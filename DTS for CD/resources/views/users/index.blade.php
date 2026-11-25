@extends('layouts.app')
@section('title','Users')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Users</li>
@endsection

@section('content')
<div class="page-hdr d-flex align-items-center justify-content-between">
    <div>
        <h4 class="mb-1 fw-700">Users</h4>
        <p class="text-muted mb-0 small">All registered users in {{ Auth::user()->campus->name ?? 'your campus' }}</p>
    </div>
    @if($canManage)
    <a href="{{ route('users.create') }}" class="btn btn-primary btn-sm">
        <i class="bi bi-person-plus me-1"></i>Add User
    </a>
    @endif
</div>

{{-- Role summary cards --}}
@php
    $roles = [
        'document_controller' => ['label'=>'Document Controller','color'=>'danger',  'icon'=>'bi-shield-check'],
        'campus_director'     => ['label'=>'Campus Director',     'color'=>'primary', 'icon'=>'bi-building'],
        'routing_unit'        => ['label'=>'Routing Unit',        'color'=>'warning', 'icon'=>'bi-diagram-3'],
        'document_submitter'  => ['label'=>'Document Submitter',  'color'=>'secondary','icon'=>'bi-person'],
    ];
@endphp
<div class="row g-3 mb-4">
    @foreach($roles as $role => $info)
    @php $count = $users->where('role', $role)->count(); @endphp
    <div class="col-6 col-md-3">
        <div class="card border-0 h-100">
            <div class="card-body text-center py-3">
                <i class="bi {{ $info['icon'] }} fs-3 text-{{ $info['color'] }} mb-1 d-block"></i>
                <div class="fs-4 fw-700">{{ $users->total() > 0 ? $users->where('role',$role)->count() : 0 }}</div>
                <div class="small text-muted">{{ $info['label'] }}</div>
            </div>
        </div>
    </div>
    @endforeach
</div>

{{-- Users Table --}}
<div class="card border-0">
    <div class="card-header bg-white border-bottom py-3 d-flex align-items-center justify-content-between">
        <h6 class="mb-0 fw-600">All Users ({{ $users->total() }})</h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 small">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Department</th>
                        <th>Position</th>
                        <th>Signature</th>
                        <th>Status</th>
                        @if($canManage)
                        <th class="text-end pe-3">Actions</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $u)
                    <tr>
                        <td class="ps-3">
                            <div class="d-flex align-items-center gap-2">
                                <div class="rounded-circle bg-{{ $u->getRoleColor() }} text-white d-flex align-items-center justify-content-center flex-shrink-0"
                                     style="width:32px;height:32px;font-size:.75rem;font-weight:700;">
                                    {{ strtoupper(substr($u->name,0,1)) }}
                                </div>
                                <div>
                                    <div class="fw-500">{{ $u->name }}</div>
                                    @if($u->id === Auth::id())
                                    <span class="badge bg-light text-muted" style="font-size:.6rem;">You</span>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="text-muted">{{ $u->email }}</td>
                        <td>
                            <span class="badge bg-{{ $u->getRoleColor() }}-subtle text-{{ $u->getRoleColor() }} badge-s">
                                {{ $u->getRoleLabel() }}
                            </span>
                        </td>
                        <td class="text-muted">{{ $u->department ?? '—' }}</td>
                        <td class="text-muted">{{ $u->position ?? '—' }}</td>
                        <td>
                            @if($u->signature_path)
                            <img src="{{ asset('storage/'.$u->signature_path) }}"
                                 alt="sig" style="max-height:28px;max-width:80px;object-fit:contain;">
                            @else
                            <span class="text-muted small">—</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-{{ $u->is_active?'success':'secondary' }}-subtle text-{{ $u->is_active?'success':'secondary' }} badge-s">
                                {{ $u->is_active?'Active':'Inactive' }}
                            </span>
                        </td>
                        @if($canManage)
                        <td class="text-end pe-3">
                            <div class="d-flex gap-1 justify-content-end">
                                <a href="{{ route('users.edit',$u) }}" class="btn btn-sm btn-light" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                @if($u->id !== Auth::id())
                                <form method="POST" action="{{ route('users.destroy',$u) }}"
                                      onsubmit="return confirm('Deactivate this user?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-light text-danger" title="Deactivate">
                                        <i class="bi bi-person-x"></i>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                        @endif
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-5">
                            <i class="bi bi-people fs-2 d-block mb-2"></i>No users found.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($users->hasPages())
    <div class="card-footer bg-white py-3">{{ $users->links() }}</div>
    @endif
</div>
@endsection
