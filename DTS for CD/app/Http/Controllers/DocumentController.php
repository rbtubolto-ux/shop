<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Models\DocumentLog;
use App\Models\DocumentRoute;
use App\Models\Notification;
use App\Models\User;
use App\Models\WorkflowTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class DocumentController extends Controller
{
    public function index(Request $request)
    {
        $user      = Auth::user();
        $direction = $request->get('direction', 'Incoming');
        $query     = Document::with(['submitter','workflowTemplate','routes' => fn($q) => $q->where('action','Pending')->orderBy('step_order')]);

        // Scope by role
        $query->where('campus_id', $user->campus_id);
        if (!$user->canManageDocuments()) $query->where(fn($q) => $q->where('is_confidential', false)->orWhere('submitted_by', $user->id));
        if ($user->isDocumentSubmitter()) {
            $query->where('submitted_by', $user->id);
        } elseif ($user->isRoutingUnit()) {
            $query->whereHas('routes', fn($q) => $q->where('assigned_to', $user->id));
        }

        $query->where('direction', $direction);

        // Quick filters
        if ($request->filled('quick')) {
            match($request->quick) {
                'pending'   => $query->whereIn('status', ['Submitted','In Review','Forwarded','Endorsed','For Approval']),
                'overdue'   => $query->whereNotNull('due_date')->whereDate('due_date','<',now())->whereNotIn('status',['Approved','Archived']),
                'unacted'   => $query->where('status','Submitted'),
                'deadlines' => $query->whereNotNull('due_date')->whereDate('due_date','<=',now()->addDays(3))->whereNotIn('status',['Approved','Archived']),
                'my_action' => $query->whereHas('routes', fn($q) => $q->where('assigned_to',$user->id)->where('action','Pending')),
                default     => null,
            };
        }

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(fn($q) => $q->where('title','like',"%$s%")->orWhere('doc_number','like',"%$s%")->orWhere('originating_office','like',"%$s%"));
        }
        if ($request->filled('category')) $query->where('category', $request->category);
        if ($request->filled('status'))   $query->where('status', $request->status);
        if ($request->filled('date_from')) $query->whereDate('created_at','>=',$request->date_from);
        if ($request->filled('date_to'))   $query->whereDate('created_at','<=',$request->date_to);

        $documents = $query->latest()->paginate(15)->withQueryString();
        $counts    = $this->getSidebarCounts($user);

        return view('documents.index', compact('documents','direction','counts'));
    }

    public function create()
    {
        $user      = Auth::user();
        $users     = User::where('campus_id', $user->campus_id)->where('is_active', true)->get();
        $workflows = WorkflowTemplate::where('campus_id', $user->campus_id)->where('is_active', true)->with('steps.assignedUser')->get();
        $allDocs   = Document::where('campus_id', $user->campus_id)->latest()->get();
        $defaultWorkflows = $workflows->mapWithKeys(fn ($w) => [$w->document_type => $w->id])->all();
        return view('documents.create', compact('users', 'workflows', 'allDocs', 'defaultWorkflows'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'title'               => 'required|string|max:255',
            'category'            => 'required|in:'.implode(',', Document::CATEGORIES),
            'direction'           => 'required|in:Incoming,Outgoing',
            'description'         => 'nullable|string',
            'originating_office'  => 'nullable|string|max:255',
            'signatory'           => 'nullable|string|max:255',
            'date_submitted'      => 'nullable|date',
            'due_date'            => 'nullable|date',
            'workflow_template_id'=> 'nullable|exists:workflow_templates,id',
            'cabinet'             => 'nullable|string|max:100',
            'drawer'              => 'nullable|string|max:100',
            'container'           => 'nullable|string|max:100',
            'retention_code'      => 'nullable|string|max:100',
            'remarks'             => 'nullable|string',
            'routing_instructions' => 'nullable|string|max:1000',
            'related_documents'   => 'nullable|array',
            'related_documents.*' => 'exists:documents,id',
            'file'                => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240',
        ]);

        $campusCode = $user->campus->code ?? 'GEN';
        $docNumber  = Document::generateDocNumber($campusCode, $validated['category']);
        $barcode    = Document::generateBarcode($docNumber);
        $isSubmit   = $request->has('submit_now');

        $filePath = $fileName = null;
        if ($request->hasFile('file')) {
            $file     = $request->file('file');
            $fileName = $file->getClientOriginalName();
            $filePath = $file->store('documents', 'local');
        }

        $document = Document::create([
            'campus_id'           => $user->campus_id,
            'doc_number'          => $docNumber,
            'barcode'             => $barcode,
            'title'               => $validated['title'],
            'category'            => $validated['category'],
            'direction'           => $validated['direction'],
            'description'         => $validated['description'] ?? null,
            'status'              => $isSubmit ? 'Submitted' : 'Draft',
            'originating_office'  => $validated['originating_office'] ?? null,
            'signatory'           => $validated['signatory'] ?? null,
            'date_submitted'      => $isSubmit ? now() : ($validated['date_submitted'] ?? null),
            'due_date'            => $validated['due_date'] ?? null,
            'workflow_template_id'=> $validated['workflow_template_id'] ?? null,
            'current_step'        => 0,
            'cabinet'             => $validated['cabinet'] ?? null,
            'drawer'              => $validated['drawer'] ?? null,
            'container'           => $validated['container'] ?? null,
            'retention_code'      => $validated['retention_code'] ?? null,
            'is_confidential'     => $request->boolean('is_confidential'),
            'submitted_by'        => $user->id,
            'remarks'             => $validated['remarks'] ?? null,
            'routing_instructions' => $validated['routing_instructions'] ?? null,
            'file_path'           => $filePath,
            'file_name'           => $fileName,
        ]);

        if (!empty($validated['related_documents'])) {
            $document->relatedDocuments()->sync($validated['related_documents']);
        }

        // Create routing steps if workflow selected and submitted
        if ($isSubmit && $validated['workflow_template_id']) {
            $this->initializeRouting($document);
        }

        DocumentLog::create([
            'document_id'  => $document->id,
            'performed_by' => $user->id,
            'action'       => $isSubmit ? 'Document Submitted' : 'Document Created (Draft)',
            'new_status'   => $document->status,
            'remarks'      => 'Document added to the system.',
        ]);

        return redirect()->route('documents.show', $document)
            ->with('success', "Document {$docNumber} ".($isSubmit ? 'submitted' : 'saved as draft')." successfully.");
    }

    public function show(Document $document)
    {
        $this->authorizeView($document);
        $document->load(['campus','submitter','workflowTemplate','routes.assignedUser','routes.actedByUser','logs.performer','relatedDocuments']);
        $currentRoute = $document->currentRoute();
        $user = Auth::user();
        $canAct = $currentRoute && $currentRoute->assigned_to === $user->id;
        return view('documents.show', compact('document','currentRoute','canAct'));
    }

    public function edit(Document $document)
    {
        $this->authorizeManage($document);
        $user      = Auth::user();
        $users     = User::where('campus_id', $document->campus_id)->where('is_active', true)->get();
        $workflows = WorkflowTemplate::where('campus_id', $document->campus_id)->where('is_active', true)->with('steps.assignedUser')->get();
        $allDocs   = Document::where('campus_id', $document->campus_id)->where('id','!=',$document->id)->latest()->get();
        return view('documents.edit', compact('document','users','workflows','allDocs'));
    }

    public function update(Request $request, Document $document)
    {
        $this->authorizeManage($document);
        $user = Auth::user();

        $validated = $request->validate([
            'title'               => 'required|string|max:255',
            'category'            => 'required|in:'.implode(',', Document::CATEGORIES),
            'direction'           => 'required|in:Incoming,Outgoing',
            'description'         => 'nullable|string',
            'originating_office'  => 'nullable|string|max:255',
            'signatory'           => 'nullable|string|max:255',
            'date_submitted'      => 'nullable|date',
            'due_date'            => 'nullable|date',
            'workflow_template_id'=> 'nullable|exists:workflow_templates,id',
            'cabinet'             => 'nullable|string|max:100',
            'drawer'              => 'nullable|string|max:100',
            'container'           => 'nullable|string|max:100',
            'retention_code'      => 'nullable|string|max:100',
            'remarks'             => 'nullable|string',
            'routing_instructions' => 'nullable|string|max:1000',
            'related_documents'   => 'nullable|array',
            'related_documents.*' => 'exists:documents,id',
            'file'                => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240',
        ]);

        if ($request->hasFile('file')) {
            if ($document->file_path) Storage::disk('local')->delete($document->file_path);
            $file                   = $request->file('file');
            $validated['file_name'] = $file->getClientOriginalName();
            $validated['file_path'] = $file->store('documents', 'local');
        }

        $validated['is_confidential'] = $request->boolean('is_confidential');
        $document->update($validated);
        $document->relatedDocuments()->sync($validated['related_documents'] ?? []);

        DocumentLog::create([
            'document_id'  => $document->id,
            'performed_by' => $user->id,
            'action'       => 'Document Updated',
            'remarks'      => 'Document details were updated.',
        ]);

        return redirect()->route('documents.show', $document)->with('success', 'Document updated successfully.');
    }

    public function submit(Document $document)
    {
        $this->authorizeManage($document);
        $user = Auth::user();

        if (!in_array($document->status, ['Draft', 'Returned'])) {
            return back()->with('error', 'Document cannot be submitted in its current status.');
        }

        $document->update(['status' => 'Submitted', 'date_submitted' => now(), 'current_step' => 0]);

        // Re-initialize routing
        $document->routes()->delete();
        if ($document->workflow_template_id) {
            $this->initializeRouting($document);
        }

        DocumentLog::create([
            'document_id'  => $document->id,
            'performed_by' => $user->id,
            'action'       => 'Document Submitted',
            'new_status'   => 'Submitted',
        ]);

        return back()->with('success', 'Document submitted successfully.');
    }

    public function routeAction(Request $request, Document $document)
    {
        $this->authorizeView($document);
        $user = Auth::user();

        $validated = $request->validate([
            'action'    => 'required|in:Forwarded,Endorsed,Returned,Approved,Noted',
            'comments'  => 'nullable|string|max:1000',
            'signature' => 'nullable|file|mimes:png,jpg,jpeg|max:2048',
        ]);

        $currentRoute = $document->currentRoute();
        if (!$currentRoute || $currentRoute->assigned_to !== $user->id) {
            return back()->with('error', 'You are not authorized to act on this document at this step.');
        }

        // Update current route — stamp user's signature automatically
        $currentRoute->update([
            'action'         => $validated['action'],
            'comments'       => $validated['comments'],
            'acted_by'       => $user->id,
            'acted_at'       => now(),
            'signature_path' => $user->signature_path, // auto-stamp from profile
        ]);

        $oldStatus = $document->status;
        $updateData = [];

        if ($validated['action'] === 'Returned') {
            $updateData['status'] = 'Returned';
            $this->notify($document->submitted_by, $document, "Your document {$document->doc_number} was returned by {$user->name}.", 'danger');

        } elseif ($validated['action'] === 'Approved') {
            $updateData['status']      = 'Approved';
            $updateData['approved_by'] = $user->id;
            $updateData['approved_at'] = now();

            // Save signature if uploaded
            if ($request->hasFile('signature')) {
                // Delete old signature if exists
                if ($document->signature_path) {
                    Storage::disk('public')->delete($document->signature_path);
                }
                $updateData['signature_path'] = $request->file('signature')->store('signatures', 'public');
            }

            $this->notify($document->submitted_by, $document, "Your document {$document->doc_number} has been approved by {$user->name}.", 'success');

        } else {
            // Forward / Endorse / Note — activate next step
            $nextRoute = DocumentRoute::where('document_id', $document->id)
                ->where('step_order', '>', $currentRoute->step_order)
                ->where('action', 'Pending')
                ->orderBy('step_order')
                ->first();

            if ($nextRoute) {
                $nextRoute->update(['received_at' => now()]);
                $newStatus = $nextRoute->step_order === $document->routes()->max('step_order') ? 'For Approval' : 'Forwarded';
                $updateData['status']       = $newStatus;
                $updateData['current_step'] = $nextRoute->step_order;
                if ($nextRoute->assigned_to) {
                    $this->notify($nextRoute->assigned_to, $document, "Document {$document->doc_number} has been routed to you for action.", 'info');
                }
            } else {
                $updateData['status'] = 'Approved';
                $updateData['approved_by'] = $user->id;
                $updateData['approved_at'] = now();
                $this->notify($document->submitted_by, $document, "Your document {$document->doc_number} has been fully processed.", 'success');
            }
        }

        $document->update($updateData);

        DocumentLog::create([
            'document_id'  => $document->id,
            'performed_by' => $user->id,
            'action'       => $validated['action'],
            'old_status'   => $oldStatus,
            'new_status'   => $document->fresh()->status,
            'routing_step' => $currentRoute->step_name,
            'remarks'      => $validated['comments'],
        ]);

        return back()->with('success', "Action '{$validated['action']}' recorded successfully.");
    }

    public function routingSlip(Document $document)
    {
        $this->authorizeView($document);
        $document->load(['campus', 'submitter', 'routes.assignedUser']);
        $current  = $document->currentRoute();
        $subjects = \App\Models\WorkflowStep::SUBJECTS;

        return view('documents.routing-slip', compact('document', 'current', 'subjects'));
    }

    public function archive(Document $document)
    {
        $this->authorizeView($document);
        $user = Auth::user();

        if (!$user->canManageDocuments()) abort(403);
        if (empty($document->retention_code)) return back()->with('error', 'Set a retention code before archiving (FAM 13.1 section 3.5).');

        $document->update(['status' => 'Archived', 'archived_at' => now()]);

        DocumentLog::create([
            'document_id'  => $document->id,
            'performed_by' => $user->id,
            'action'       => 'Document Archived',
            'new_status'   => 'Archived',
        ]);

        return back()->with('success', 'Document archived successfully.');
    }

    public function printBarcode(Document $document)
    {
        $this->authorizeView($document);
        return view('documents.barcode', compact('document'));
    }

    public function destroy(Document $document)
    {
        $this->authorizeManage($document);
        if ($document->file_path) Storage::disk('local')->delete($document->file_path);
        $document->delete();
        return redirect()->route('documents.index')->with('success', 'Document deleted.');
    }

    // ── Helpers ──────────────────────────────────────────────

    private function initializeRouting(Document $document): void
    {
        $template = WorkflowTemplate::with('steps')->find($document->workflow_template_id);
        if (!$template) return;

        foreach ($template->steps as $step) {
            DocumentRoute::create([
                'document_id'      => $document->id,
                'workflow_step_id' => $step->id,
                'step_order'       => $step->step_order,
                'step_name'        => $step->step_name,
                'subject'        => $step->subject ?? \App\Models\WorkflowStep::defaultSubjectFor($step->role_required),
                'assigned_to'      => $step->assigned_to,
                'action'           => 'Pending',
                'received_at'      => $step->step_order === 1 ? now() : null,
            ]);
        }

        $document->update(['status' => 'In Review', 'current_step' => 1]);

        // Notify first step assignee
        $firstStep = $template->steps->first();
        if ($firstStep && $firstStep->assigned_to) {
            $this->notify($firstStep->assigned_to, $document, "Document {$document->doc_number} has been routed to you for review.", 'info');
        }
    }

    private function notify(int $userId, Document $document, string $message, string $type = 'info'): void
    {
        Notification::create([
            'user_id'     => $userId,
            'document_id' => $document->id,
            'message'     => $message,
            'type'        => $type,
        ]);
    }

    private function authorizeView(Document $document): void
    {
        $user = Auth::user();
        if ($user->campus_id !== $document->campus_id) abort(403);
        if ($document->is_confidential && !$user->canManageDocuments() && (int) $document->submitted_by !== $user->id) abort(403);
        if ($user->canManageDocuments()) return;
        if ((int) $document->submitted_by === $user->id) return;
        if ($document->routes()->where('assigned_to', $user->id)->exists()) return;
        abort(403);
    }

    private function authorizeManage(Document $document): void
    {
        $user = Auth::user();
        if ($user->campus_id !== $document->campus_id) abort(403);
        if ($document->is_confidential && !$user->canManageDocuments() && (int) $document->submitted_by !== $user->id) abort(403);
        if ($user->canManageDocuments()) return;
        if ($user->isDocumentSubmitter() && (int) $document->submitted_by === $user->id) return;
        abort(403);
    }

    public function file(Document $document)
    {
        $this->authorizeView($document);
        abort_unless($document->file_path && Storage::disk('local')->exists($document->file_path), 404);
        return Storage::disk('local')->response($document->file_path, $document->file_name);
    }


    public function getSidebarCounts(User $user): array
    {
        $base = Document::where('campus_id', $user->campus_id);
        if ($user->isDocumentSubmitter()) $base->where('submitted_by', $user->id);
        elseif ($user->isRoutingUnit()) $base->whereHas('routes', fn($q) => $q->where('assigned_to', $user->id));

        return [
            'pending'    => (clone $base)->whereIn('status',['Submitted','In Review','Forwarded','Endorsed','For Approval'])->count(),
            'overdue'    => (clone $base)->whereNotNull('due_date')->whereDate('due_date','<',now())->whereNotIn('status',['Approved','Archived'])->count(),
            'unacted'    => (clone $base)->where('status','Submitted')->count(),
            'deadlines'  => (clone $base)->whereNotNull('due_date')->whereDate('due_date','<=',now()->addDays(3))->whereNotIn('status',['Approved','Archived'])->count(),
            'my_action'  => DocumentRoute::where('assigned_to',$user->id)->where('action','Pending')->count(),
        ];
    }
}
