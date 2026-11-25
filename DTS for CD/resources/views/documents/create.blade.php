@extends('layouts.app')
@section('title','Submit Document')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Submit Document</li>
@endsection

@section('content')
<div class="page-hdr">
    <h4 class="mb-1 fw-700">Submit Document</h4>
    <p class="text-muted mb-0 small">Fill in the details to register and route a document</p>
</div>

<div x-data="docForm()" class="row justify-content-center">
    <div class="col-lg-9">
        <form method="POST" action="{{ route('documents.store') }}" enctype="multipart/form-data">
            @csrf

            {{-- Section 1: Basic Info --}}
            <div class="card border-0 mb-3">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="mb-0 fw-600"><span class="badge bg-primary me-2">1</span>Basic Information</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-500">Direction <span class="text-danger">*</span></label>
                            <div class="d-flex gap-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="direction" value="Incoming" id="dIn" {{ old('direction','Incoming')=='Incoming'?'checked':'' }}>
                                    <label class="form-check-label" for="dIn"><i class="bi bi-box-arrow-in-down me-1 text-info"></i>Incoming</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="direction" value="Outgoing" id="dOut" {{ old('direction')=='Outgoing'?'checked':'' }}>
                                    <label class="form-check-label" for="dOut"><i class="bi bi-box-arrow-up me-1 text-success"></i>Outgoing</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-500">Document Title <span class="text-danger">*</span></label>
                            <input type="text" name="title" value="{{ old('title') }}" class="form-control @error('title') is-invalid @enderror" placeholder="Enter document title" required>
                            @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-500">Category <span class="text-danger">*</span></label>
                            <select name="category" class="form-select @error('category') is-invalid @enderror" required>
                                <option value="">Select category...</option>
                                @foreach(\App\Models\Document::CATEGORIES as $cat)
                                <option value="{{ $cat }}" {{ old('category')==$cat?'selected':'' }}>{{ $cat }}</option>
                                @endforeach
                            </select>
                            @error('category')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-500">Originating Office</label>
                            <input type="text" name="originating_office" value="{{ old('originating_office') }}" class="form-control" placeholder="e.g. College of Engineering">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-500">Description</label>
                            <textarea name="description" rows="2" class="form-control" placeholder="Brief description of the document...">{{ old('description') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Section 2: Details --}}
            <div class="card border-0 mb-3">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="mb-0 fw-600"><span class="badge bg-primary me-2">2</span>Document Details</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-500">Signatory</label>
                            <input type="text" name="signatory" value="{{ old('signatory') }}" class="form-control" placeholder="Name of signatory">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-500">Date Submitted</label>
                            <input type="date" name="date_submitted" value="{{ old('date_submitted',date('Y-m-d')) }}" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-500">Due Date / Deadline</label>
                            <input type="date" name="due_date" value="{{ old('due_date') }}" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-500">Attach File</label>
                            <input type="file" name="file" class="form-control" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                            <div class="form-text">PDF, DOC, DOCX, JPG, PNG — max 10MB</div>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-500">Remarks</label>
                            <textarea name="remarks" rows="2" class="form-control" placeholder="Additional notes...">{{ old('remarks') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Section 3: Workflow --}}
            <div class="card border-0 mb-3">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="mb-0 fw-600"><span class="badge bg-primary me-2">3</span>Routing Workflow</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-500">Select Workflow Template</label>
                        <select name="workflow_template_id" class="form-select" x-model="selectedWorkflow" @change="updateSteps()">
                            <option value="">No workflow (manual routing)</option>
                            @foreach($workflows as $wf)
                            <option value="{{ $wf->id }}" data-steps="{{ json_encode($wf->steps->map(fn($s)=>['order'=>$s->step_order,'name'=>$s->step_name,'user'=>$s->assignedUser?->name??'Unassigned','threshold'=>$s->pending_threshold_hours])) }}">
                                {{ $wf->name }} ({{ $wf->document_type }})
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div x-show="steps.length > 0" class="mt-3">
                        <div class="small fw-500 text-muted mb-2">Routing Steps Preview:</div>
                        <div class="d-flex flex-wrap gap-2">
                            <template x-for="(step,i) in steps" :key="i">
                                <div class="d-flex align-items-center gap-1">
                                    <span class="badge bg-primary-subtle text-primary" x-text="'Step '+(i+1)+': '+step.name+' → '+step.user"></span>
                                    <template x-if="i < steps.length-1"><i class="bi bi-arrow-right text-muted small"></i></template>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Section 4: File Container --}}
            <div class="card border-0 mb-3">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="mb-0 fw-600"><span class="badge bg-primary me-2">4</span>File Container (Physical Location)</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-500">Cabinet</label>
                            <input type="text" name="cabinet" value="{{ old('cabinet') }}" class="form-control" placeholder="Cabinet label">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-500">Drawer</label>
                            <input type="text" name="drawer" value="{{ old('drawer') }}" class="form-control" placeholder="Drawer label">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-500">Container / Folder</label>
                            <input type="text" name="container" value="{{ old('container') }}" class="form-control" placeholder="Folder/box label">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-500">Retention code</label>
                                <input type="text" name="retention_code" value="{{ old('retention_code') }}" class="form-control" placeholder="e.g. per NAP General Records Schedule">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-500">Confidential</label>
                                <div class="form-check mt-2">
                                    <input class="form-check-input" type="checkbox" name="is_confidential" value="1" id="isConfidential" {{ old('is_confidential') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="isConfidential">Keep separately (visible to managers and submitter only)</label>
                                </div>

                        </div>
                    </div>
                </div>
            </div>

            {{-- Section 5: Related Documents --}}
            @if($allDocs->count())
            <div class="card border-0 mb-3">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="mb-0 fw-600"><span class="badge bg-primary me-2">5</span>Related Documents</h6>
                </div>
                <div class="card-body">
                    <select name="related_documents[]" class="form-select" multiple style="height:100px;">
                        @foreach($allDocs as $d)
                        <option value="{{ $d->id }}" {{ in_array($d->id, old('related_documents',[])) ? 'selected':'' }}>
                            {{ $d->doc_number }} — {{ Str::limit($d->title,50) }}
                        </option>
                        @endforeach
                    </select>
                    <div class="form-text">Hold Ctrl to select multiple</div>
                </div>
            </div>
            @endif

            {{-- Submit Buttons --}}
            <div class="d-flex gap-2">
                <button type="submit" name="save_draft" class="btn btn-outline-secondary">
                    <i class="bi bi-save me-1"></i>Save as Draft
                </button>
                <button type="submit" name="submit_now" class="btn btn-primary">
                    <i class="bi bi-send me-1"></i>Submit & Start Routing
                </button>
                <a href="{{ route('documents.index') }}" class="btn btn-light">Cancel</a>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function docForm() {
    return {
        selectedWorkflow: '{{ old("workflow_template_id","") }}',
        steps: [],
        updateSteps() {
            const sel = document.querySelector('select[name="workflow_template_id"]');
            const opt = sel.options[sel.selectedIndex];
            const data = opt.getAttribute('data-steps');
            this.steps = data ? JSON.parse(data) : [];
        }
    }
}
</script>
@endpush
@endsection
