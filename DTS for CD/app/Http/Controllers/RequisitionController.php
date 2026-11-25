<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\DocumentLog;
use App\Models\RecordsRequisition;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

// FAM 13.1 section 3.10 and forms 5.1.1 / 5.1.2: a records requisition is issued by the
// Records Management Officer, acknowledged by the requester, and returned to the office.
class RequisitionController extends Controller
{
    public function index()
    {
        $user  = Auth::user();
        $query = RecordsRequisition::with(['document', 'requester', 'issuer'])
            ->whereHas('document', fn($q) => $q->where('campus_id', $user->campus_id));

        if (!$user->canManageDocuments()) {
            $query->where('requested_by', $user->id);
        }

        $requisitions = $query->latest()->paginate(20);

        return view('requisitions.index', compact('requisitions'));
    }

    public function store(Request $request, Document $document)
    {
        $user = Auth::user();
        if (!$this->canView($document, $user)) abort(403);

        $validated = $request->validate([
            'purpose' => 'required|string|max:1000',
        ]);

        RecordsRequisition::create([
            'document_id'  => $document->id,
            'requested_by' => $user->id,
            'purpose'      => $validated['purpose'],
            'status'       => 'Requested',
        ]);

        DocumentLog::create([
            'document_id'  => $document->id,
            'performed_by' => $user->id,
            'action'       => 'Records Requisitioned',
            'remarks'      => $validated['purpose'],
        ]);

        return back()->with('success', 'Requisition submitted to the Records Management Officer.');
    }

    public function issue(RecordsRequisition $requisition)
    {
        $user = Auth::user();
        $this->authorizeManager($requisition, $user);

        if ($requisition->status !== 'Requested') {
            return back()->with('error', 'This requisition has already been issued.');
        }

        $requisition->update([
            'status'    => 'Issued',
            'issued_by' => $user->id,
            'issued_at' => now(),
        ]);

        $this->log($requisition, $user, 'Record Issued', 'Issued to ' . $requisition->requester->name);

        return back()->with('success', 'Record issued.');
    }

    public function acknowledge(RecordsRequisition $requisition)
    {
        $user = Auth::user();
        if ((int) $requisition->requested_by !== $user->id) abort(403);

        if ($requisition->status !== 'Issued') {
            return back()->with('error', 'This record has not been issued yet.');
        }

        $requisition->update([
            'status'          => 'Acknowledged',
            'acknowledged_at' => now(),
        ]);

        $this->log($requisition, $user, 'Record Acknowledged', 'Receipt acknowledged by requester.');

        return back()->with('success', 'Receipt acknowledged.');
    }

    public function returned(RecordsRequisition $requisition)
    {
        $user = Auth::user();
        $this->authorizeManager($requisition, $user);

        if (!in_array($requisition->status, ['Issued', 'Acknowledged'], true)) {
            return back()->with('error', 'Only issued records can be returned.');
        }

        $requisition->update([
            'status'      => 'Returned',
            'returned_at' => now(),
        ]);

        $this->log($requisition, $user, 'Record Returned', 'Returned to the records office.');

        return back()->with('success', 'Record marked as returned.');
    }

    private function authorizeManager(RecordsRequisition $requisition, $user): void
    {
        if (!$user->canManageDocuments()) abort(403);
        if ((int) $requisition->document->campus_id !== (int) $user->campus_id) abort(403);
    }

    private function canView(Document $document, $user): bool
    {
        if ((int) $document->campus_id !== (int) $user->campus_id) return false;
        if ($document->is_confidential && !$user->canManageDocuments() && (int) $document->submitted_by !== $user->id) return false;
        if ($user->canManageDocuments()) return true;
        if ((int) $document->submitted_by === $user->id) return true;
        return $document->routes()->where('assigned_to', $user->id)->exists();
    }

    private function log(RecordsRequisition $requisition, $user, string $action, string $remarks): void
    {
        DocumentLog::create([
            'document_id'  => $requisition->document_id,
            'performed_by' => $user->id,
            'action'       => $action,
            'remarks'      => $remarks,
        ]);
    }
}
