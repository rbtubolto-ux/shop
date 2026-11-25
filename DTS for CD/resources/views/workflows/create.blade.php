@extends('layouts.app')
@section('title','New Workflow')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('workflows.index') }}">Workflows</a></li>
    <li class="breadcrumb-item active">New Workflow</li>
@endsection

@section('content')
<div class="page-hdr">
    <h4 class="mb-1 fw-700">Create Routing Workflow</h4>
    <p class="text-muted mb-0 small">Define the routing chain for a document type</p>
</div>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <form method="POST" action="{{ route('workflows.store') }}" x-data="workflowForm()">
            @csrf
            <div class="card border-0 mb-3">
                <div class="card-header bg-white border-bottom py-3"><h6 class="mb-0 fw-600">Workflow Details</h6></div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label fw-500">Workflow Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" value="{{ old('name') }}" class="form-control @error('name') is-invalid @enderror" placeholder="e.g. Budget Request Workflow" required>
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-500">Document Type <span class="text-danger">*</span></label>
                            <select name="document_type" class="form-select @error('document_type') is-invalid @enderror" required>
                                <option value="">Select...</option>
                                @foreach(\App\Models\Document::CATEGORIES as $cat)
                                <option value="{{ $cat }}" {{ old('document_type')==$cat?'selected':'' }}>{{ $cat }}</option>
                                @endforeach
                            </select>
                            @error('document_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-500">Description</label>
                            <textarea name="description" rows="2" class="form-control" placeholder="Describe this workflow...">{{ old('description') }}</textarea>
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
                        <div class="border rounded p-3 mb-3 position-relative">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <span class="badge bg-primary" x-text="'Step '+(i+1)"></span>
                                <button type="button" class="btn btn-sm btn-outline-danger" @click="removeStep(i)" x-show="steps.length > 1">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                            <div class="row g-2">
                                <div class="col-md-5">
                                    <label class="form-label small fw-500">Step Name <span class="text-danger">*</span></label>
                                    <input type="text" :name="'steps['+i+'][step_name]'" x-model="step.step_name"
                                           class="form-control form-control-sm" placeholder="e.g. Budget Officer Review" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small fw-500">Assign To</label>
                                    <select :name="'steps['+i+'][assigned_to]'" x-model="step.assigned_to" class="form-select form-select-sm">
                                        <option value="">Select user...</option>
                                        @foreach($users as $u)
                                        <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->getRoleLabel() }})</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small fw-500">Alert After (hrs)</label>
                                    <input type="number" :name="'steps['+i+'][pending_threshold_hours]'" x-model="step.pending_threshold_hours"
                                           class="form-control form-control-sm" min="1" value="48" required>
                                </div>
                                <div class="col-12">
                                    <label class="form-label small fw-500">Routing slip subject</label>
                                    <select :name="'steps['+i+'][subject]'" x-model="step.subject" class="form-select form-select-sm">
                                        <option value="">Default for this role</option>
                                        @foreach(\App\Models\WorkflowStep::SUBJECTS as $sj)
                                        <option value="{{ $sj }}">{{ $sj }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <input type="hidden" :name="'steps['+i+'][role_required]'" x-model="step.role_required">
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i>Create Workflow</button>
                <a href="{{ route('workflows.index') }}" class="btn btn-light">Cancel</a>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function workflowForm() {
    return {
        steps: [{ step_name: '', assigned_to: '', role_required: '', subject: '', pending_threshold_hours: 48 }],
        addStep() { this.steps.push({ step_name: '', assigned_to: '', role_required: '', subject: '', pending_threshold_hours: 48 }); },
        removeStep(i) { if(this.steps.length > 1) this.steps.splice(i, 1); }
    }
}
</script>
@endpush
@endsection
