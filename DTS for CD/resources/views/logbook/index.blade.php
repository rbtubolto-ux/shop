@extends('layouts.app')
@section('title', 'Logbook')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Logbook</li>
@endsection

@section('content')
<div class="page-header">
    <h4 class="mb-1 fw-700">Logbook</h4>
    <p class="text-muted mb-0 small">Official acts, transactions and releases (FAM 13.1 section 3.7). Confidential records are shown to managers only.</p>
</div>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead class="table-light">
                <tr>
                    <th>Date &amp; time</th>
                    <th>Doc Number</th>
                    <th>Title</th>
                    <th>Action</th>
                    <th>Performed by</th>
                    <th>Remarks</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                <tr>
                    <td class="small text-nowrap">{{ $log->created_at->format('M d, Y h:i A') }}</td>
                    <td class="small">{{ $log->document->doc_number ?? '—' }}</td>
                    <td class="small">{{ $log->document->title ?? '—' }}</td>
                    <td class="small">{{ $log->action }}</td>
                    <td class="small">{{ $log->performer->name ?? '—' }}</td>
                    <td class="small text-muted">{{ $log->remarks }}</td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center text-muted py-4">No entries yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-3">{{ $logs->links() }}</div>
@endsection
