@extends('layouts.app')
@section('title','Dashboard')
@section('breadcrumb')<li class="breadcrumb-item active">Dashboard</li>@endsection

@section('content')
@php $user = Auth::user(); @endphp

<div class="page-hdr d-flex align-items-center justify-content-between">
    <div>
        <h4 class="mb-1 fw-700">Dashboard</h4>
        <p class="text-muted mb-0 small">Welcome back, <strong>{{ $user->name }}</strong> — {{ $user->getRoleLabel() }}</p>
    </div>
    <a href="{{ route('documents.create') }}" class="btn btn-primary btn-sm">
        <i class="bi bi-plus-lg me-1"></i>Submit Document
    </a>
</div>

{{-- Stats --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md-2">
        <div class="card stat-card h-100" style="background:linear-gradient(135deg,#1a56db,#1e3a5f);color:#fff;">
            <div class="card-body text-center py-3">
                <div class="fs-2 fw-700">{{ $stats['total'] }}</div>
                <div class="small opacity-75">Total</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-2">
        <div class="card stat-card h-100 border-0">
            <div class="card-body text-center py-3">
                <div class="fs-2 fw-700 text-warning">{{ $stats['pending_my_action'] }}</div>
                <div class="small text-muted">My Action</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-2">
        <div class="card stat-card h-100 border-0">
            <div class="card-body text-center py-3">
                <div class="fs-2 fw-700 text-info">{{ $stats['in_review'] }}</div>
                <div class="small text-muted">In Review</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-2">
        <div class="card stat-card h-100 border-0">
            <div class="card-body text-center py-3">
                <div class="fs-2 fw-700 text-success">{{ $stats['approved'] }}</div>
                <div class="small text-muted">Approved</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-2">
        <div class="card stat-card h-100 border-0">
            <div class="card-body text-center py-3">
                <div class="fs-2 fw-700 text-danger">{{ $stats['returned'] }}</div>
                <div class="small text-muted">Returned</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-2">
        <div class="card stat-card h-100 border-0">
            <div class="card-body text-center py-3">
                <div class="fs-2 fw-700 text-danger">{{ $stats['overdue'] }}</div>
                <div class="small text-muted">Overdue</div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    {{-- Recent Documents --}}
    <div class="{{ $forApproval || $bottleneck ? 'col-lg-8' : 'col-12' }}">
        <div class="card border-0">
            <div class="card-header bg-white border-bottom d-flex align-items-center justify-content-between py-3">
                <h6 class="mb-0 fw-600">Recent Documents</h6>
                <a href="{{ route('documents.index') }}" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 small">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3">Doc Number</th>
                                <th>Title</th>
                                <th>Category</th>
                                <th>Status</th>
                                <th>Current Step</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentDocuments as $doc)
                            <tr class="{{ $doc->isOverdue() ? 'table-danger' : '' }}">
                                <td class="ps-3">
                                    <a href="{{ route('documents.show',$doc) }}" class="text-primary fw-600">{{ $doc->doc_number }}</a>
                                </td>
                                <td>{{ Str::limit($doc->title,35) }}</td>
                                <td><span class="badge bg-light text-dark"><i class="{{ $doc->categoryIcon() }} me-1"></i>{{ $doc->category }}</span></td>
                                <td><span class="badge bg-{{ $doc->statusColor() }}-subtle text-{{ $doc->statusColor() }} badge-s">{{ $doc->status }}</span></td>
                                <td class="text-muted">{{ $doc->routes->first()?->step_name ?? '—' }}</td>
                                <td class="text-muted">{{ $doc->created_at->format('M d, Y') }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="6" class="text-center text-muted py-4"><i class="bi bi-inbox fs-3 d-block mb-1"></i>No documents yet</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- For Approval (Campus Director) --}}
    @if($forApproval && $forApproval->count())
    <div class="col-lg-4">
        <div class="card border-0 border-start border-4 border-warning">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="mb-0 fw-600"><i class="bi bi-pen me-2 text-warning"></i>For My Approval</h6>
            </div>
            <div class="card-body p-0">
                @foreach($forApproval as $route)
                <div class="p-3 border-bottom">
                    <a href="{{ route('documents.show',$route->document) }}" class="text-primary fw-600 small d-block">
                        {{ $route->document->doc_number }}
                    </a>
                    <div class="small">{{ Str::limit($route->document->title,40) }}</div>
                    <div class="text-muted" style="font-size:.7rem;">
                        {{ $route->document->submitter->name ?? '' }} · {{ $route->document->created_at->diffForHumans() }}
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    {{-- Bottleneck (Document Controller) --}}
    @if($bottleneck && $bottleneck->count())
    <div class="col-lg-4">
        <div class="card border-0">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="mb-0 fw-600"><i class="bi bi-exclamation-triangle me-2 text-danger"></i>Routing Bottlenecks</h6>
            </div>
            <div class="card-body p-0">
                @foreach($bottleneck as $b)
                <div class="d-flex align-items-center justify-content-between p-3 border-bottom">
                    <div class="small fw-500">{{ $b->assignedUser->name ?? 'Unassigned' }}</div>
                    <span class="badge bg-danger-subtle text-danger">{{ $b->pending_count }} pending</span>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif
</div>
@endsection
