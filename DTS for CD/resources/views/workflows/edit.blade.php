@extends('layouts.app')
@section('title','Edit Workflow')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('workflows.index') }}">Workflows</a></li>
    <li class="breadcrumb-item active">Edit</li>
@endsection

@section('content')
<div class="page-hdr">
    <h4 class="mb-1 fw-700">Edit Workflow</h4>
    <p class="text-muted mb-0 small">{{ $workflow->name }}</p>
</div>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <form method="POST" action="{{ route('workflows.update',$workflow) }}" x-data="workflowEditForm()">
            @csrf @method('PUT')
            <div class="card border-0 mb-3">
                <div class="card-header bg-white border-bottom py-3"><h6 class="mb-0 fw-600">Workflow Details</h6></div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label fw-500">Workflow Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" value="{{ old('name',$workflow->name) }}" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-500">Document Type <span class="text-danger">*</span></label>
                            <select name="document_type" class="form-select" required>
                                @foreach(\App\Models\Document::CATEGORIES as $cat)
                                <option value="{{ $cat }}" {{ old('document_type',$workflow->document_type)==$cat?'selected':'' }}>{{ $cat }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label fw-500">Description</label>
                            <textarea name="description" rows="2" class="form-control">{{ old('description',$workflow->description) }}</textarea>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-500">Status</label>
                            <div class="form-check form-switch mt-2">
                                <input class="form-check-input" type="checkbox" name="is_active" value="1" {{ $workflow->is_active?'checked':'' }}>
                                <label class="form-check-label">Active</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 mb-3">
                <div class="card-header bg-white border-bottom py-3 d-flex align-items-center justify-content-between">
                    <h6 class="mb-0 fw-600">Routing Steps</h6>
                    <button type="button" class="btn btn-sm btn-outline-primary" @click="addStep()">
                        <i class="bi bi-plus-lg me-1"></i>Add Step
                    </button>
                </div>
                <div class="card-body">
                    <template x-for="(step, i) in steps" :key="i">
                        <div class="border rounded p-3 mb-3">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <span class="badge bg-primary" x-text="'Step '+(i+1)"></span>
                                <button type="button" class="btn btn-sm btn-outline-danger" @click="removeStep(i)" x-show="steps.length > 1">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                            <div class="row g-2">
                                <div class="col-md-5">
                                    <input type="text" :name="'steps['+i+'][step_name]'" x-model="step.step_name"
                                           class="form-control form-control-sm" placeholder="Step name" required>
                                </div>
                                <div class="col-md-4">
                                    <select :name="'steps['+i+'][assigned_to]'" x-model="step.assigned_to" class="form-select form-select-sm">
                                        <option value="">Select user...</option>
                                        @foreach($users as $u)
                                        <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->getRoleLabel() }})</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <input type="number" :name="'steps['+i+'][pending_threshold_hours]'" x-model="step.pending_threshold_hours"
                                           class="form-control form-control-sm" min="1" required>
                                </div>
                                <input type="hidden" :name="'steps['+i+'][role_required]'" x-model="step.role_required">
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i>Save Changes</button>
                <a href="{{ route('workflows.index') }}" class="btn btn-light">Cancel</a>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function workflowEditForm() {
    return {
        steps: @json($workflow->steps->map(fn($s) => ['step_name' => $s->step_name, 'assigned_to' => $s->assigned_to ?? '', 'role_required' => $s->role_required ?? '', 'pending_threshold_hours' => $s->pending_threshold_hours])),
        addStep() { this.steps.push({ step_name: '', assigned_to: '', role_required: '', pending_threshold_hours: 48 }); },
        removeStep(i) { if(this.steps.length > 1) this.steps.splice(i, 1); }
    }
}
</script>
@endpush
@endsection
