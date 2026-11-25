@extends('layouts.app')

@section('title', 'Campuses')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Campuses</li>
@endsection

@section('content')
<div class="page-header d-flex align-items-center justify-content-between">
    <div>
        <h4 class="mb-1 fw-700">Campuses</h4>
        <p class="text-muted mb-0 small">Manage all campuses in the system</p>
    </div>
    <a href="{{ route('campuses.create') }}" class="btn btn-primary">
        <i class="bi bi-building-add me-1"></i>Add Campus
    </a>
</div>

<div class="row g-3">
    @forelse($campuses as $campus)
    <div class="col-md-4">
        <div class="card border-0 h-100">
            <div class="card-body">
                <div class="d-flex align-items-start justify-content-between mb-3">
                    <div class="rounded-2 bg-primary-subtle d-flex align-items-center justify-content-center"
                         style="width:48px;height:48px;">
                        <i class="bi bi-building text-primary fs-4"></i>
                    </div>
                    <span class="badge bg-{{ $campus->is_active ? 'success' : 'secondary' }}-subtle
                                      text-{{ $campus->is_active ? 'success' : 'secondary' }} badge-status">
                        {{ $campus->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </div>
                <h6 class="fw-700 mb-1">{{ $campus->name }}</h6>
                <div class="small text-muted mb-2">Code: <strong>{{ $campus->code }}</strong></div>
                @if($campus->address)
                <div class="small text-muted mb-2"><i class="bi bi-geo-alt me-1"></i>{{ $campus->address }}</div>
                @endif
                <div class="d-flex gap-3 mt-3 pt-3 border-top">
                    <div class="text-center">
                        <div class="fw-700 text-primary">{{ $campus->documents_count }}</div>
                        <div class="small text-muted">Documents</div>
                    </div>
                    <div class="text-center">
                        <div class="fw-700 text-success">{{ $campus->users_count }}</div>
                        <div class="small text-muted">Users</div>
                    </div>
                </div>
            </div>
            <div class="card-footer bg-white border-top d-flex gap-2">
                <a href="{{ route('campuses.edit', $campus) }}" class="btn btn-sm btn-outline-primary flex-fill">
                    <i class="bi bi-pencil me-1"></i>Edit
                </a>
            </div>
        </div>
    </div>
    @empty
    <div class="col-12">
        <div class="text-center text-muted py-5">
            <i class="bi bi-building fs-2 d-block mb-2"></i>No campuses found.
        </div>
    </div>
    @endforelse
</div>

@if($campuses->hasPages())
<div class="mt-4">{{ $campuses->links() }}</div>
@endif
@endsection
