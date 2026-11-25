@extends('layouts.app')

@section('title', 'Add Campus')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('campuses.index') }}">Campuses</a></li>
    <li class="breadcrumb-item active">Add Campus</li>
@endsection

@section('content')
<div class="page-header">
    <h4 class="mb-1 fw-700">Add New Campus</h4>
    <p class="text-muted mb-0 small">Register a new campus in the system</p>
</div>

<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="card border-0">
            <div class="card-body p-4">
                <form method="POST" action="{{ route('campuses.store') }}">
                    @csrf
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-500">Campus Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" value="{{ old('name') }}"
                                   class="form-control @error('name') is-invalid @enderror"
                                   placeholder="e.g. Main Campus" required>
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-500">Campus Code <span class="text-danger">*</span></label>
                            <input type="text" name="code" value="{{ old('code') }}"
                                   class="form-control @error('code') is-invalid @enderror"
                                   placeholder="e.g. MAIN" style="text-transform:uppercase;" required>
                            <div class="form-text">Used in document numbering (e.g. SO-MAIN-2024-0001)</div>
                            @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-500">Contact</label>
                            <input type="text" name="contact" value="{{ old('contact') }}"
                                   class="form-control" placeholder="Phone or email">
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-500">Address</label>
                            <textarea name="address" rows="2" class="form-control"
                                      placeholder="Campus address">{{ old('address') }}</textarea>
                        </div>
                    </div>

                    <div class="d-flex gap-2 mt-4 pt-3 border-top">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-building-add me-1"></i>Create Campus
                        </button>
                        <a href="{{ route('campuses.index') }}" class="btn btn-light">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
