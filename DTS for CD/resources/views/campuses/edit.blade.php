@extends('layouts.app')

@section('title', 'Edit Campus')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('campuses.index') }}">Campuses</a></li>
    <li class="breadcrumb-item active">Edit Campus</li>
@endsection

@section('content')
<div class="page-header">
    <h4 class="mb-1 fw-700">Edit Campus</h4>
    <p class="text-muted mb-0 small">{{ $campus->name }}</p>
</div>

<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="card border-0">
            <div class="card-body p-4">
                <form method="POST" action="{{ route('campuses.update', $campus) }}">
                    @csrf @method('PUT')
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-500">Campus Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" value="{{ old('name', $campus->name) }}"
                                   class="form-control @error('name') is-invalid @enderror" required>
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-500">Campus Code <span class="text-danger">*</span></label>
                            <input type="text" name="code" value="{{ old('code', $campus->code) }}"
                                   class="form-control @error('code') is-invalid @enderror"
                                   style="text-transform:uppercase;" required>
                            @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-500">Contact</label>
                            <input type="text" name="contact" value="{{ old('contact', $campus->contact) }}"
                                   class="form-control">
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-500">Address</label>
                            <textarea name="address" rows="2" class="form-control">{{ old('address', $campus->address) }}</textarea>
                        </div>

                        <div class="col-12">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_active"
                                       id="isActive" value="1" {{ old('is_active', $campus->is_active) ? 'checked' : '' }}>
                                <label class="form-check-label fw-500" for="isActive">Campus Active</label>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex gap-2 mt-4 pt-3 border-top">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save me-1"></i>Save Changes
                        </button>
                        <a href="{{ route('campuses.index') }}" class="btn btn-light">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
