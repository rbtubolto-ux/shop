<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\WorkflowTemplate;
use App\Models\WorkflowStep;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class WorkflowController extends Controller
{
    private function authorizeController(): void
    {
        if (!Auth::user()->isDocumentController()) abort(403, 'Only Document Controllers can manage workflows.');
    }

    public function index()
    {
        $this->authorizeController();
        $workflows = WorkflowTemplate::with(['steps.assignedUser'])
            ->where('campus_id', Auth::user()->campus_id)
            ->latest()->paginate(15);
        return view('workflows.index', compact('workflows'));
    }

    public function create()
    {
        $this->authorizeController();
        $users = User::where('campus_id', Auth::user()->campus_id)->where('is_active', true)->get();
        return view('workflows.create', compact('users'));
    }

    public function store(Request $request)
    {
        $this->authorizeController();
        $user = Auth::user();

        $validated = $request->validate([
            'name'          => 'required|string|max:255',
            'document_type' => 'required|in:'.implode(',', \App\Models\Document::CATEGORIES),
            'description'   => 'nullable|string',
            'steps'         => 'required|array|min:1',
            'steps.*.step_name'               => 'required|string|max:255',
            'steps.*.assigned_to'             => 'nullable|exists:users,id',
            'steps.*.role_required'           => 'nullable|string',
            'steps.*.subject'             => ['nullable', \Illuminate\Validation\Rule::in(\App\Models\WorkflowStep::SUBJECTS)],
            'steps.*.pending_threshold_hours' => 'required|integer|min:1',
        ]);

        DB::transaction(function () use ($validated, $user) {
            $template = WorkflowTemplate::create([
                'campus_id'     => $user->campus_id,
                'name'          => $validated['name'],
                'document_type' => $validated['document_type'],
                'description'   => $validated['description'] ?? null,
                'is_active'     => true,
            ]);

            foreach ($validated['steps'] as $i => $step) {
                WorkflowStep::create([
                    'workflow_template_id'    => $template->id,
                    'step_order'              => $i + 1,
                    'step_name'               => $step['step_name'],
                    'assigned_to'             => $step['assigned_to'] ?? null,
                    'role_required'           => $step['role_required'] ?? null,
                    'subject'                 => $step['subject'] ?? null,
                    'pending_threshold_hours' => $step['pending_threshold_hours'],
                ]);
            }
        });

        return redirect()->route('workflows.index')->with('success', 'Workflow created successfully.');
    }

    public function edit(WorkflowTemplate $workflow)
    {
        $this->authorizeController();
        $workflow->load('steps.assignedUser');
        $users = User::where('campus_id', Auth::user()->campus_id)->where('is_active', true)->get();
        return view('workflows.edit', compact('workflow', 'users'));
    }

    public function update(Request $request, WorkflowTemplate $workflow)
    {
        $this->authorizeController();

        $validated = $request->validate([
            'name'          => 'required|string|max:255',
            'document_type' => 'required|in:'.implode(',', \App\Models\Document::CATEGORIES),
            'description'   => 'nullable|string',
            'is_active'     => 'boolean',
            'steps'         => 'required|array|min:1',
            'steps.*.step_name'               => 'required|string|max:255',
            'steps.*.assigned_to'             => 'nullable|exists:users,id',
            'steps.*.role_required'           => 'nullable|string',
            'steps.*.subject'             => ['nullable', \Illuminate\Validation\Rule::in(\App\Models\WorkflowStep::SUBJECTS)],
            'steps.*.pending_threshold_hours' => 'required|integer|min:1',
        ]);

        DB::transaction(function () use ($validated, $workflow) {
            $workflow->update([
                'name'          => $validated['name'],
                'document_type' => $validated['document_type'],
                'description'   => $validated['description'] ?? null,
                'is_active'     => $request->boolean('is_active', true),
            ]);

            $workflow->steps()->delete();

            foreach ($validated['steps'] as $i => $step) {
                WorkflowStep::create([
                    'workflow_template_id'    => $workflow->id,
                    'step_order'              => $i + 1,
                    'step_name'               => $step['step_name'],
                    'assigned_to'             => $step['assigned_to'] ?? null,
                    'role_required'           => $step['role_required'] ?? null,
                    'subject'                 => $step['subject'] ?? null,
                    'pending_threshold_hours' => $step['pending_threshold_hours'],
                ]);
            }
        });

        return redirect()->route('workflows.index')->with('success', 'Workflow updated successfully.');
    }

    public function destroy(WorkflowTemplate $workflow)
    {
        $this->authorizeController();
        $workflow->update(['is_active' => false]);
        return back()->with('success', 'Workflow deactivated.');
    }
}
