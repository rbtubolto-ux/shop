@extends('layouts.app')

@section('title', $direction . ' Documents')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">{{ $direction }} Documents</li>
@endsection

@section('content')
<div class="page-header d-flex align-items-center justify-content-between">
    <div>
        <h4 class="mb-1 fw-700">Documents</h4>
        <p class="text-muted mb-0 small">Track and manage all campus documents</p>
    </div>
    <a href="{{ route('documents.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i> Add Document
    </a>
</div>

{{-- Incoming / Outgoing Tabs --}}
<ul class="nav nav-tabs mb-3">
    <li class="nav-item">
        <a class="nav-link {{ $direction == 'Incoming' ? 'active' : '' }}"
           href="{{ route('documents.index', array_merge(request()->except('direction','page'), ['direction' => 'Incoming'])) }}">
            <i class="bi bi-box-arrow-in-down me-1"></i> Incoming
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ $direction == 'Outgoing' ? 'active' : '' }}"
           href="{{ route('documents.index', array_merge(request()->except('direction','page'), ['direction' => 'Outgoing'])) }}">
            <i class="bi bi-box-arrow-up me-1"></i> Outgoing
        </a>
    </li>
</ul>

{{-- Quick filter alert --}}
@if(request('quick'))
<div class="alert alert-info py-2 small d-flex align-items-center justify-content-between">
    <span><i class="bi bi-funnel me-1"></i> Showing: <strong>{{ ucfirst(request('quick')) }}</strong> documents</span>
    <a href="{{ route('documents.index', ['direction' => $direction]) }}" class="btn btn-sm btn-light">Clear filter</a>
</div>
@endif

{{-- Filters --}}
<div class="card border-0 mb-3">
    <div class="card-body py-3">
        <form method="GET" action="{{ route('documents.index') }}">
            <input type="hidden" name="direction" value="{{ $direction }}">
            <div class="row g-2 align-items-end">
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text bg-light"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" name="search" value="{{ request('search') }}"
                               class="form-control" placeholder="Search title, doc no., barcode...">
                    </div>
                </div>
                <div class="col-md-2">
                    <select name="category" class="form-select">
                        <option value="">All Categories</option>
                        @foreach(\App\Models\Document::CATEGORIES as $cat)
                        <option value="{{ $cat }}" {{ request('category') == $cat ? 'selected' : '' }}>{{ $cat }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-select">
                        <option value="">All Statuses</option>
                        @foreach(\App\Models\Document::STATUSES as $s)
                        <option value="{{ $s }}" {{ request('status') == $s ? 'selected' : '' }}>{{ $s }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <input type="date" name="date_from" value="{{ request('date_from') }}" class="form-control">
                </div>
                <div class="col-md-2">
                    <input type="date" name="date_to" value="{{ request('date_to') }}" class="form-control">
                </div>
            </div>
            <div class="d-flex gap-2 mt-2">
                <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-funnel me-1"></i>Filter</button>
                <a href="{{ route('documents.index', ['direction' => $direction]) }}" class="btn btn-light btn-sm"><i class="bi bi-x-circle me-1"></i>Clear</a>
            </div>
        </form>
    </div>
</div>

{{-- Table --}}
<div class="card border-0">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="small fw-600 ps-3">Doc Number</th>
                        <th class="small fw-600">Title</th>
                        <th class="small fw-600">Category</th>
                        <th class="small fw-600">Status</th>
                        <th class="small fw-600">Current Step</th>
                        <th class="small fw-600">Due Date</th>
                        <th class="small fw-600 text-end pe-3">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($documents as $doc)
                    <tr class="{{ $doc->isOverdue() ? 'table-danger' : '' }}">
                        <td class="ps-3">
                            <a href="{{ route('documents.show', $doc) }}" class="text-primary fw-600 small">
                                {{ $doc->doc_number }}
                            </a>
                            @if($doc->barcode)
                            <div class="text-muted" style="font-size:0.65rem;">{{ $doc->barcode }}</div>
                            @endif
                            @if(Auth::user()->canManageDocuments())
                            <div class="text-muted" style="font-size:0.65rem;">{{ $doc->campus->name ?? '' }}</div>
                            @endif
                        </td>
                        <td>
                            <div class="fw-500 small">{{ Str::limit($doc->title, 38) }}</div>
                            @if($doc->originating_office)
                            <div class="text-muted" style="font-size:0.7rem;">{{ $doc->originating_office }}</div>
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-light text-dark small">
                                <i class="{{ $doc->categoryIcon() }} me-1"></i>{{ $doc->category }}
                            </span>
                        </td>
                        <td>
                            <span class="badge bg-{{ $doc->statusColor() }}-subtle text-{{ $doc->statusColor() }} badge-status">
                                {{ $doc->status }}
                            </span>
                        </td>
                        <td class="small text-muted">{{ $doc->routes->first()?->step_name ?? '—' }}</td>
                        <td class="small">
                            @if($doc->due_date)
                                <span class="{{ $doc->isOverdue() ? 'text-danger fw-600' : 'text-muted' }}">
                                    @if($doc->isOverdue())<i class="bi bi-exclamation-circle me-1"></i>@endif
                                    {{ $doc->due_date->format('M d, Y') }}
                                </span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td class="text-end pe-3">
                            <div class="d-flex gap-1 justify-content-end">
                                <a href="{{ route('documents.show', $doc) }}" class="btn btn-sm btn-light" title="View">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('documents.barcode', $doc) }}" class="btn btn-sm btn-light" title="Print Barcode" target="_blank">
                                    <i class="bi bi-upc-scan"></i>
                                </a>
                                <a href="{{ route('documents.edit', $doc) }}" class="btn btn-sm btn-light" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form method="POST" action="{{ route('documents.destroy', $doc) }}"
                                      onsubmit="return confirm('Delete this document?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-light text-danger" title="Delete">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-5">
                            <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                            No {{ strtolower($direction) }} documents found.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($documents->hasPages())
    <div class="card-footer bg-white border-top d-flex align-items-center justify-content-between py-3">
        <div class="small text-muted">Showing {{ $documents->firstItem() }}–{{ $documents->lastItem() }} of {{ $documents->total() }}</div>
        {{ $documents->links() }}
    </div>
    @endif
</div>
@endsection
