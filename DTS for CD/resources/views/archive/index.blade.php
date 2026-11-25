@extends('layouts.app')
@section('title','Archive')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Archive</li>
@endsection

@section('content')
<div class="page-hdr">
    <h4 class="mb-1 fw-700">Document Archive</h4>
    <p class="text-muted mb-0 small">Searchable repository of finalized documents</p>
</div>

<div class="card border-0 mb-4">
    <div class="card-body py-3">
        <form method="GET" action="{{ route('archive.index') }}">
            <div class="row g-2">
                <div class="col-md-3">
                    <div class="input-group">
                        <span class="input-group-text bg-light"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Title or doc number...">
                    </div>
                </div>
                <div class="col-md-2">
                    <select name="category" class="form-select">
                        <option value="">All Types</option>
                        @foreach(\App\Models\Document::CATEGORIES as $cat)
                        <option value="{{ $cat }}" {{ request('category')==$cat?'selected':'' }}>{{ $cat }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <input type="text" name="originating_office" value="{{ request('originating_office') }}" class="form-control" placeholder="Originating office...">
                </div>
                <div class="col-md-2">
                    <input type="text" name="routing_history" value="{{ request('routing_history') }}" class="form-control" placeholder="Routing history keyword...">
                </div>
                <div class="col-md-1">
                    <input type="date" name="date_from" value="{{ request('date_from') }}" class="form-control">
                </div>
                <div class="col-md-1">
                    <input type="date" name="date_to" value="{{ request('date_to') }}" class="form-control">
                </div>
                <div class="col-md-1">
                    <button type="submit" class="btn btn-primary w-100"><i class="bi bi-search"></i></button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="card border-0">
    <div class="card-header bg-white border-bottom py-3 d-flex align-items-center justify-content-between">
        <h6 class="mb-0 fw-600">Archived Documents</h6>
        <span class="small text-muted">{{ $documents->total() }} records</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 small">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">Doc Number</th>
                        <th>Title</th>
                        <th>Type</th>
                        <th>Originating Office</th>
                        <th>Submitted By</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th class="text-end pe-3">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($documents as $doc)
                    <tr>
                        <td class="ps-3">
                            <a href="{{ route('documents.show',$doc) }}" class="text-primary fw-600">{{ $doc->doc_number }}</a>
                        </td>
                        <td>{{ Str::limit($doc->title,40) }}</td>
                        <td><span class="badge bg-light text-dark">{{ $doc->category }}</span></td>
                        <td class="text-muted">{{ $doc->originating_office ?? '—' }}</td>
                        <td class="text-muted">{{ $doc->submitter->name ?? '—' }}</td>
                        <td><span class="badge bg-{{ $doc->statusColor() }}-subtle text-{{ $doc->statusColor() }} badge-s">{{ $doc->status }}</span></td>
                        <td class="text-muted">{{ $doc->created_at->format('M d, Y') }}</td>
                        <td class="text-end pe-3">
                            <a href="{{ route('documents.show',$doc) }}" class="btn btn-sm btn-light"><i class="bi bi-eye"></i></a>
                            @if($doc->file_path)
                            <a href="{{ route('documents.file', $doc) }}" target="_blank" class="btn btn-sm btn-light"><i class="bi bi-download"></i></a>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="text-center text-muted py-5"><i class="bi bi-archive fs-2 d-block mb-2"></i>No archived documents found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($documents->hasPages())
    <div class="card-footer bg-white py-3">{{ $documents->links() }}</div>
    @endif
</div>
@endsection
