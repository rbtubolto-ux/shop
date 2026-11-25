<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\DocumentLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

// FAM 13.1 sections 3.11 to 3.15: inventory, appraisal and semi-annual classification
// (forms and reports 5.1.3, 5.2.1 to 5.2.3).
class RecordsController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        abort_unless($user->canManageDocuments(), 403);

        $base = Document::with('submitter')
            ->where('campus_id', $user->campus_id)
            ->whereIn('status', ['Approved', 'Archived']);

        $documents     = (clone $base)->latest()->paginate(25);
        $byRetention   = (clone $base)->selectRaw('retention_code, COUNT(*) as count')->groupBy('retention_code')->pluck('count', 'retention_code');
        $byDisposition = (clone $base)->selectRaw('disposition, COUNT(*) as count')->groupBy('disposition')->pluck('count', 'disposition');
        $unclassified  = (clone $base)->whereNull('disposition')->count();

        return view('records.index', compact('documents', 'byRetention', 'byDisposition', 'unclassified'));
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
}
