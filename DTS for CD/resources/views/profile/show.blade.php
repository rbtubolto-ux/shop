@extends('layouts.app')
@section('title','My Profile')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">My Profile</li>
@endsection

@section('content')
<div class="page-hdr">
    <h4 class="mb-1 fw-700">My Profile</h4>
    <p class="text-muted mb-0 small">Manage your account details and digital signature</p>
</div>

<div class="row g-4">
    {{-- Profile Info --}}
    <div class="col-lg-7">
        <div class="card border-0">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="mb-0 fw-600">Account Information</h6>
            </div>
            <div class="card-body p-4">
                <form method="POST" action="{{ route('profile.update') }}">
                    @csrf @method('PUT')
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-500">Full Name</label>
                            <input type="text" name="name" value="{{ old('name',$user->name) }}"
                                   class="form-control @error('name') is-invalid @enderror" required>
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-500">Email</label>
                            <input type="email" name="email" value="{{ old('email',$user->email) }}"
                                   class="form-control @error('email') is-invalid @enderror" required>
                            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-500">Position</label>
                            <input type="text" name="position" value="{{ old('position',$user->position) }}"
                                   class="form-control" placeholder="Your position/title">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-500">Role</label>
                            <input type="text" value="{{ $user->getRoleLabel() }}" class="form-control" disabled>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-500">Department</label>
                            <input type="text" value="{{ $user->department ?? '—' }}" class="form-control" disabled>
                        </div>

                        <div class="col-12 mt-2">
                            <div class="border-top pt-3">
                                <label class="form-label fw-500">Change Password</label>
                                <div class="row g-2">
                                    <div class="col-md-6">
                                        <input type="password" name="password" class="form-control"
                                               placeholder="New password (min 8 chars)" minlength="8">
                                    </div>
                                    <div class="col-md-6">
                                        <input type="password" name="password_confirmation" class="form-control"
                                               placeholder="Confirm new password">
                                    </div>
                                </div>
                                <div class="form-text">Leave blank to keep current password</div>
                            </div>
                        </div>
                    </div>
                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save me-1"></i>Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Digital Signature --}}
    <div class="col-lg-5">
        <div class="card border-0">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="mb-0 fw-600"><i class="bi bi-pen me-2"></i>Digital Signature</h6>
            </div>
            <div class="card-body p-4">
                <p class="small text-muted mb-3">
                    Your signature will be automatically attached to every document action you take
                    (Forward, Endorse, Approve, etc.) and will appear in the routing timeline.
                </p>

                {{-- Current Signature --}}
                @if($user->signature_path)
                <div class="mb-4 p-3 border rounded text-center" style="background:#f8fffe;">
                    <div class="small text-muted mb-2">Current Signature</div>
                    <img src="{{ asset('storage/'.$user->signature_path) }}"
                         alt="Your Signature"
                         style="max-height:100px;max-width:100%;object-fit:contain;">
                    <div class="mt-3">
                        <form method="POST" action="{{ route('profile.signature.remove') }}"
                              onsubmit="return confirm('Remove your signature?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger">
                                <i class="bi bi-trash me-1"></i>Remove Signature
                            </button>
                        </form>
                    </div>
                </div>
                @else
                <div class="mb-4 p-4 border rounded text-center bg-light">
                    <i class="bi bi-pen fs-2 text-muted d-block mb-2"></i>
                    <div class="small text-muted">No signature uploaded yet</div>
                </div>
                @endif

                {{-- Upload New Signature --}}
                <form method="POST" action="{{ route('profile.signature') }}" enctype="multipart/form-data">
                    @csrf
                    <label class="form-label fw-500">
                        {{ $user->signature_path ? 'Replace Signature' : 'Upload Signature' }}
                    </label>
                    <input type="file" name="signature"
                           class="form-control @error('signature') is-invalid @enderror"
                           accept=".png,.jpg,.jpeg" required>
                    <div class="form-text">PNG or JPG, max 2MB. Use a white/transparent background for best results.</div>
                    @error('signature')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    <button type="submit" class="btn btn-primary mt-3 w-100">
                        <i class="bi bi-upload me-1"></i>Upload Signature
                    </button>
                </form>

                {{-- Tips --}}
                <div class="mt-4 p-3 bg-light rounded small text-muted">
                    <div class="fw-500 mb-1"><i class="bi bi-lightbulb me-1"></i>Tips for a good signature:</div>
                    <ul class="mb-0 ps-3">
                        <li>Sign on white paper, take a photo or scan</li>
                        <li>Crop tightly around the signature</li>
                        <li>PNG with transparent background looks best</li>
                        <li>Keep it under 2MB</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
