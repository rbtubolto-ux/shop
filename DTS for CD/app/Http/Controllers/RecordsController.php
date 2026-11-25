<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\DocumentLog;
use App\Models\RecordsExamination;
use App\Models\RecordsReview;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

// FAM 13.1 sections 3.11 to 3.15: inventory, appraisal, semi-annual classification,
// periodic file examination, and the reviews recorded for each.
class RecordsController extends Controller
{
    // Intervals in months. The manual sets the yearly and semi-annual cycles; the
    // examination interval is "periodic" in the manual, so 12 months is a chosen default.
    public const EXAMINATION_INTERVAL_MONTHS = 12;
    public const REVIEW_INTERVALS = [
        'Yearly Inventory'           => 12,
        'Semi-annual Classification' => 6,
    ];

    public function index()
    {
        $user = Auth::user();
        abort_unless($user->canManageDocuments(), 403);
        $campusId = $user->campus_id;

        $base = Document::with(['submitter', 'latestExamination'])
            ->where('campus_id', $campusId)
            ->whereIn('status', ['Approved', 'Archived']);

        $documents     = (clone $base)->latest()->paginate(25);
        $byRetention   = (clone $base)->selectRaw('retention_code, COUNT(*) as count')->groupBy('retention_code')->pluck('count', 'retention_code');
        $byDisposition = (clone $base)->selectRaw('disposition, COUNT(*) as count')->groupBy('disposition')->pluck('count', 'disposition');
        $unclassified  = (clone $base)->whereNull('disposition')->count();

        // Review reminders (3.12, 3.13)
        $reviews = [];
        foreach (self::REVIEW_INTERVALS as $type => $months) {
            $last = RecordsReview::where('campus_id', $campusId)->where('type', $type)->latest('conducted_at')->first();
            $due  = $last ? $last->conducted_at->copy()->addMonths($months) : null;
            $reviews[$type] = [
                'months'  => $months,
                'last'    => $last,
                'due'     => $due,
                'overdue' => !$last || $due->isPast(),
            ];
        }
        $reviewHistory = RecordsReview::with('conductor')->where('campus_id', $campusId)->latest('conducted_at')->limit(10)->get();

        // Periodic file examination (3.14): records never examined or examined too long ago
        $cutoff = now()->subMonths(self::EXAMINATION_INTERVAL_MONTHS);
        $examinationsDue = (clone $base)->with('latestExamination.examiner')->get()
            ->filter(fn ($doc) => !$doc->latestExamination || $doc->latestExamination->examined_at->lt($cutoff))
            ->values();
        $examinationsDueCount = $examinationsDue->count();
        $examinationsDue = $examinationsDue->take(50);

        return view('records.index', compact(
            'documents', 'byRetention', 'byDisposition', 'unclassified',
            'reviews', 'reviewHistory', 'examinationsDue', 'examinationsDueCount'
        ));
    }

    public function setDisposition(Request $request, Document $document)
    {
        $user = Auth::user();
        abort_unless($user->canManageDocuments() && (int) $user->campus_id === (int) $document->campus_id, 403);

        $validated = $request->validate([
            'disposition' => 'required|in:For Retention,Archive,Dispose',
        ]);

        if (empty($document->retention_code)) {
            return back()->with('error', 'Set a retention code on this document before classifying it.');
        }

        $document->update(['disposition' => $validated['disposition']]);

        DocumentLog::create([
            'document_id'  => $document->id,
            'performed_by' => $user->id,
            'action'       => 'Disposition Set',
            'remarks'      => $validated['disposition'],
        ]);

        return back()->with('success', 'Disposition updated.');
    }

    public function examine(Request $request, Document $document)
    {
        $user = Auth::user();
        abort_unless($user->canManageDocuments() && (int) $user->campus_id === (int) $document->campus_id, 403);

        $validated = $request->validate([
            'condition' => 'required|in:Good,Fair,Poor,Damaged',
            'notes'     => 'nullable|string|max:1000',
        ]);

        RecordsExamination::create([
            'document_id' => $document->id,
            'condition'   => $validated['condition'],
            'notes'       => $validated['notes'] ?? null,
            'examined_by' => $user->id,
            'examined_at' => now(),
        ]);

        DocumentLog::create([
            'document_id'  => $document->id,
            'performed_by' => $user->id,
            'action'       => 'File Condition Examined',
            'remarks'      => $validated['condition'] . (!empty($validated['notes']) ? ' - ' . $validated['notes'] : ''),
        ]);

        return back()->with('success', 'Examination recorded.');
    }

    public function review(Request $request)
    {
        $user = Auth::user();
        abort_unless($user->canManageDocuments(), 403);

        $validated = $request->validate([
            'type'  => 'required|in:Yearly Inventory,Semi-annual Classification',
            'notes' => 'nullable|string|max:1000',
        ]);

        $count = Document::where('campus_id', $user->campus_id)
            ->whereIn('status', ['Approved', 'Archived'])
            ->count();

        RecordsReview::create([
            'campus_id'     => $user->campus_id,
            'type'          => $validated['type'],
            'conducted_by'  => $user->id,
            'conducted_at'  => now(),
            'records_count' => $count,
            'notes'         => $validated['notes'] ?? null,
        ]);

        return back()->with('success', $validated['type'] . ' recorded for ' . $count . ' records.');
    }
}
