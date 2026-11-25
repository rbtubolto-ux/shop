@extends('layouts.app')
@section('title','Workflows')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Workflows</li>
@endsection

@section('content')
<div class="page-hdr d-flex align-items-center justify-content-between">
    <div>
        <h4 class="mb-1 fw-700">Routing Workflows</h4>
        <p class="text-muted mb-0 small">Configure document routing chains per document type</p>
    </div>
    <a href="{{ route('workflows.create') }}" class="btn btn-primary btn-sm">
        <i class="bi bi-plus-lg me-1"></i>New Workflow
    </a>
</div>

<div class="row g-3">
    @forelse($workflows as $wf)
    <div class="col-md-6">
        <div class="card border-0 h-100">
            <div class="card-body">
                <div class="d-flex align-items-start justify-content-between mb-2">
                    <div>
                        <h6 class="fw-700 mb-1">{{ $wf->name }}</h6>
                        <span class="badge bg-light text-dark small">{{ $wf->document_type }}</span>
                    </div>
                    <span class="badge bg-{{ $wf->is_active ? 'success':'secondary' }}-subtle text-{{ $wf->is_active ? 'success':'secondary' }}">
                        {{ $wf->is_active ? 'Active':'Inactive' }}
                    </span>
                </div>
                @if($wf->description)<p class="small text-muted mb-3">{{ $wf->description }}</p>@endif

                {{-- Steps --}}
                <div class="d-flex flex-wrap gap-1 mb-3">
                    @foreach($wf->steps as $step)
                    <div class="d-flex align-items-center gap-1">
                        <span class="badge bg-primary-subtle text-primary small">
                            {{ $step->step_order }}. {{ $step->step_name }}
                            @if($step->assignedUser) → {{ $step->assignedUser->name }} @endif
                        </span>
                        @if(!$loop->last)<i class="bi bi-arrow-right text-muted" style="font-size:.7rem;"></i>@endif
                    </div>
                    @endforeach
                </div>

                <div class="d-flex gap-2">
                    <a href="{{ route('workflows.edit',$wf) }}" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-pencil me-1"></i>Edit
                    </a>
                    <form method="POST" action="{{ route('workflows.destroy',$wf) }}" onsubmit="return confirm('Deactivate this workflow?')">
                        @csrf @method('DELETE')
                        <button class="btn btn-sm btn-outline-secondary"><i class="bi bi-pause-circle me-1"></i>Deactivate</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @empty
    <div class="col-12">
        <div class="text-center text-muted py-5">
            <i class="bi bi-diagram-3 fs-2 d-block mb-2"></i>No workflows yet.
            <a href="{{ route('workflows.create') }}" class="d-block mt-2 small">Create your first workflow</a>
        </div>
    </div>
    @endforelse
</div>
@endsection
