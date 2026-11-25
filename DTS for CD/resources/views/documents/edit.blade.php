@extends('layouts.app')

@section('title', 'Edit Document')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('documents.index') }}">Documents</a></li>
    <li class="breadcrumb-item"><a href="{{ route('documents.show', $document) }}">{{ $document->doc_number }}</a></li>
    <li class="breadcrumb-item active">Edit</li>
@endsection

@section('content')
<div class="page-header">
    <h4 class="mb-1 fw-700">Edit Document</h4>
    <p class="text-muted mb-0 small">{{ $document->doc_number }} — {{ $document->title }}</p>
</div>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card border-0">
            <div class="card-body p-4">
                <form method="POST" action="{{ route('documents.update', $document) }}" enctype="multipart/form-data">
                    @csrf @method('PUT')

                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-500">Document Title <span class="text-danger">*</span></label>
                            <input type="text" name="title" value="{{ old('title', $document->title) }}"
                                   class="form-control @error('title') is-invalid @enderror" required>
                            @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-500">Category <span class="text-danger">*</span></label>
                            <select name="category" class="form-select" required>
                                @foreach(\App\Models\Document::CATEGORIES as $cat)
                                <option value="{{ $cat }}" {{ old('category', $document->category) == $cat ? 'selected' : '' }}>
                                    {{ $cat }}
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-500">Originating Office</label>
                            <input type="text" name="originating_office"
                                   value="{{ old('originating_office', $document->originating_office) }}"
                                   class="form-control">
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-500">Description</label>
                            <textarea name="description" rows="3" class="form-control">{{ old('description', $document->description) }}</textarea>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-500">Date Received</label>
                            <input type="date" name="date_received"
                                   value="{{ old('date_received', $document->date_received?->format('Y-m-d')) }}"
                                   class="form-control">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-500">Due Date</label>
                            <input type="date" name="due_date"
                                   value="{{ old('due_date', $document->due_date?->format('Y-m-d')) }}"
                                   class="form-control">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-500">Assign To</label>
                            <select name="assigned_to" class="form-select">
                                <option value="">Unassigned</option>
                                @foreach($users as $u)
                                <option value="{{ $u->id }}"
                                    {{ old('assigned_to', $document->assigned_to) == $u->id ? 'selected' : '' }}>
                                    {{ $u->name }} ({{ ucfirst(str_replace('_',' ',$u->role)) }})
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-500">Replace File</label>
                            @if($document->file_name)
                            <div class="small text-muted mb-1">
                                Current: <a href="{{ route('documents.file', $document) }}" target="_blank">{{ $document->file_name }}</a>
                            </div>
                            @endif
                            <input type="file" name="file" class="form-control"
                                   accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                            <div class="form-text">Leave blank to keep existing file</div>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-500">Remarks</label>
                            <textarea name="remarks" rows="2" class="form-control">{{ old('remarks', $document->remarks) }}</textarea>
                        </div>
                    </div>

                    <div class="d-flex gap-2 mt-4 pt-3 border-top">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save me-1"></i>Save Changes
                        </button>
                        <a href="{{ route('documents.show', $document) }}" class="btn btn-light">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
