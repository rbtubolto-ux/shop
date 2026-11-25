@extends('layouts.app')
@section('title','Edit User')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('users.index') }}">Users</a></li>
    <li class="breadcrumb-item active">Edit</li>
@endsection

@section('content')
<div class="page-hdr"><h4 class="mb-1 fw-700">Edit User</h4><p class="text-muted mb-0 small">{{ $user->email }}</p></div>
<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="card border-0">
            <div class="card-body p-4">
                <form method="POST" action="{{ route('users.update',$user) }}">
                    @csrf @method('PUT')
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-500">Full Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" value="{{ old('name',$user->name) }}" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-500">Email <span class="text-danger">*</span></label>
                            <input type="email" name="email" value="{{ old('email',$user->email) }}" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-500">Role <span class="text-danger">*</span></label>
                            <select name="role" class="form-select" required>
                                <option value="document_controller" {{ old('role',$user->role)=='document_controller'?'selected':'' }}>Document Controller</option>
                                <option value="campus_director"     {{ old('role',$user->role)=='campus_director'?'selected':'' }}>Campus Director</option>
                                <option value="routing_unit"        {{ old('role',$user->role)=='routing_unit'?'selected':'' }}>Routing Unit</option>
                                <option value="document_submitter"  {{ old('role',$user->role)=='document_submitter'?'selected':'' }}>Document Submitter</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-500">Department</label>
                            <input type="text" name="department" value="{{ old('department',$user->department) }}" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-500">Position</label>
                            <input type="text" name="position" value="{{ old('position',$user->position) }}" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-500">New Password</label>
                            <input type="password" name="password" class="form-control" minlength="8" placeholder="Leave blank to keep current">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-500">Confirm Password</label>
                            <input type="password" name="password_confirmation" class="form-control">
                        </div>
                        <div class="col-12">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_active" value="1" {{ old('is_active',$user->is_active)?'checked':'' }}>
                                <label class="form-check-label fw-500">Account Active</label>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex gap-2 mt-4 pt-3 border-top">
                        <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i>Save Changes</button>
                        <a href="{{ route('users.index') }}" class="btn btn-light">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
