@extends('layouts.app')
@section('title', 'Records Requisitions')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Requisitions</li>
@endsection

@section('content')
<div class="page-header">
    <h4 class="mb-1 fw-700">Records Requisitions</h4>
    <p class="text-muted mb-0 small">Requisition, issue, acknowledgement and return of records (FAM 13.1 section 3.10, forms 5.1.1 and 5.1.2).</p>
</div>

@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead class="table-light">
                <tr>
                    <th>Doc Number</th>
                    <th>Title</th>
                    <th>Requested by</th>
                    <th>Purpose</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($requisitions as $req)
                <tr>
                    <td class="small">{{ $req->document->doc_number ?? '—' }}</td>
                    <td class="small">{{ $req->document->title ?? '—' }}</td>
                    <td class="small">{{ $req->requester->name ?? '—' }}</td>
                    <td class="small text-muted">{{ $req->purpose }}</td>
                    <td><span class="badge bg-secondary">{{ $req->status }}</span></td>
                    <td class="text-nowrap">
                        @if($req->status === 'Requested' && Auth::user()->canManageDocuments())
                            <form method="POST" action="{{ route('requisitions.issue', $req) }}" class="d-inline">
                                @csrf @method('PATCH')
                                <button class="btn btn-sm btn-primary">Issue</button>
                            </form>
                        @endif
                        @if($req->status === 'Issued' && (int) $req->requested_by === Auth::id())
                            <form method="POST" action="{{ route('requisitions.acknowledge', $req) }}" class="d-inline">
                                @csrf @method('PATCH')
                                <button class="btn btn-sm btn-outline-primary">Acknowledge receipt</button>
                            </form>
                        @endif
                        @if(in_array($req->status, ['Issued','Acknowledged']) && Auth::user()->canManageDocuments())
                            <form method="POST" action="{{ route('requisitions.return', $req) }}" class="d-inline">
                                @csrf @method('PATCH')
                                <button class="btn btn-sm btn-outline-success">Mark returned</button>
                            </form>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center text-muted py-4">No requisitions yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-3">{{ $requisitions->links() }}</div>
@endsection
