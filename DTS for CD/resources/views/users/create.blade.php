@extends('layouts.app')
@section('title','Add User')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('users.index') }}">Users</a></li>
    <li class="breadcrumb-item active">Add User</li>
@endsection

@section('content')
<div class="page-hdr"><h4 class="mb-1 fw-700">Add New User</h4></div>
<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="card border-0">
            <div class="card-body p-4">
                <form method="POST" action="{{ route('users.store') }}">
                    @csrf
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-500">Full Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" value="{{ old('name') }}" class="form-control @error('name') is-invalid @enderror" required>
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-500">Email <span class="text-danger">*</span></label>
                            <input type="email" name="email" value="{{ old('email') }}" class="form-control @error('email') is-invalid @enderror" required>
                            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-500">Role <span class="text-danger">*</span></label>
                            <select name="role" class="form-select" required>
                                <option value="">Select role...</option>
                                <option value="document_controller" {{ old('role')=='document_controller'?'selected':'' }}>Document Controller</option>
                                <option value="campus_director"     {{ old('role')=='campus_director'?'selected':'' }}>Campus Director</option>
                                <option value="routing_unit"        {{ old('role')=='routing_unit'?'selected':'' }}>Routing Unit</option>
                                <option value="document_submitter"  {{ old('role')=='document_submitter'?'selected':'' }}>Document Submitter</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-500">Department</label>
                            <input type="text" name="department" value="{{ old('department') }}" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-500">Position</label>
                            <input type="text" name="position" value="{{ old('position') }}" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-500">Password <span class="text-danger">*</span></label>
                            <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" required minlength="8">
                            @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-500">Confirm Password <span class="text-danger">*</span></label>
                            <input type="password" name="password_confirmation" class="form-control" required>
                        </div>
                    </div>
                    <div class="d-flex gap-2 mt-4 pt-3 border-top">
                        <button type="submit" class="btn btn-primary"><i class="bi bi-person-plus me-1"></i>Create User</button>
                        <a href="{{ route('users.index') }}" class="btn btn-light">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
