@extends('layouts.app')
@section('title', 'Records Management')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Records Management</li>
@endsection

@section('content')
<div class="page-header d-flex align-items-center justify-content-between">
    <div>
        <h4 class="mb-1 fw-700">Records Management</h4>
        <p class="text-muted mb-0 small">Inventory, classification and file examination (FAM 13.1 sections 3.11 to 3.15).</p>
    </div>
    <button onclick="window.print()" class="btn btn-outline-secondary btn-sm"><i class="bi bi-printer me-1"></i>Print</button>
</div>

@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif

{{-- Review reminders (3.12, 3.13) --}}
<div class="row g-3 mb-4">
    @foreach($reviews as $type => $r)
    <div class="col-md-6">
        <div class="card border-0 shadow-sm h-100 {{ $r['overdue'] ? 'border-start border-4 border-danger' : '' }}">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-600">{{ $type }}</h6>
                @if($r['overdue'])
                    <span class="badge bg-danger">Due now</span>
                @else
                    <span class="badge bg-success">On schedule</span>
                @endif
            </div>
            <div class="card-body small">
                <div class="mb-1">Every {{ $r['months'] }} months</div>
                <div class="mb-1">Last completed: <strong>{{ $r['last']?->conducted_at?->format('M d, Y') ?? 'Never' }}</strong></div>
                <div class="mb-3">Next due: <strong>{{ $r['due']?->format('M d, Y') ?? 'Now' }}</strong></div>
                <form method="POST" action="{{ route('records.reviews.store') }}">
                    @csrf
                    <input type="hidden" name="type" value="{{ $type }}">
                    <input type="text" name="notes" class="form-control form-control-sm mb-2" placeholder="Notes (optional)">
                    <button class="btn btn-sm btn-outline-primary">Record completed {{ strtolower($type) }}</button>
                </form>
            </div>
        </div>
    </div>
    @endforeach
</div>

{{-- Summary (5.2.2, 5.2.3) --}}
<div class="row g-3 mb-4">
    <div class="col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white py-3"><h6 class="mb-0 fw-600">Inventory by retention code (5.2.2)</h6></div>
            <div class="card-body small">
                @forelse($byRetention as $code => $count)
                    <div class="d-flex justify-content-between"><span>{{ $code ?: 'No retention code' }}</span><strong>{{ $count }}</strong></div>
                @empty
                    <span class="text-muted">No approved or archived records yet.</span>
                @endforelse
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white py-3"><h6 class="mb-0 fw-600">Classification (5.2.3)</h6></div>
            <div class="card-body small">
                @foreach(['For Retention','Archive','Dispose'] as $d)
                    <div class="d-flex justify-content-between"><span>{{ $d }}</span><strong>{{ $byDisposition[$d] ?? 0 }}</strong></div>
                @endforeach
                <div class="d-flex justify-content-between text-warning"><span>Not yet classified</span><strong>{{ $unclassified }}</strong></div>
            </div>
        </div>
    </div>
</div>

{{-- File condition examination (3.14) --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-600">Files due for examination (3.14)</h6>
        <span class="badge bg-warning text-dark">{{ $examinationsDueCount }} due</span>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead class="table-light">
                <tr>
                    <th>Doc Number</th>
                    <th>Title</th>
                    <th>Last examined</th>
                    <th>Condition</th>
                    <th>Record examination</th>
                </tr>
            </thead>
            <tbody>
                @forelse($examinationsDue as $doc)
                <tr>
                    <td class="small">{{ $doc->doc_number }}</td>
                    <td class="small">{{ $doc->title }}</td>
                    <td class="small">
                        {{ $doc->latestExamination?->examined_at?->format('M d, Y') ?? 'Never' }}
                    </td>
                    <td class="small">{{ $doc->latestExamination?->condition ?? '—' }}</td>
                    <td>
                        <form method="POST" action="{{ route('records.examine', $doc) }}" class="d-flex gap-2">
                            @csrf
                            <select name="condition" class="form-select form-select-sm" required>
                                @foreach(['Good','Fair','Poor','Damaged'] as $c)
                                    <option value="{{ $c }}">{{ $c }}</option>
                                @endforeach
                            </select>
                            <input type="text" name="notes" class="form-control form-control-sm" placeholder="Notes">
                            <button class="btn btn-sm btn-outline-primary">Save</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" class="text-center text-muted py-4">All files were examined within the last 12 months.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($examinationsDueCount > 50)
        <div class="card-footer small text-muted">Showing the first 50 of {{ $examinationsDueCount }} files.</div>
    @endif
</div>

{{-- Disposition (3.13, 3.15) --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white py-3"><h6 class="mb-0 fw-600">Disposition</h6></div>
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead class="table-light">
                <tr>
                    <th>Doc Number</th>
                    <th>Title</th>
                    <th>Retention code</th>
                    <th>Disposition</th>
                    <th>Set disposition</th>
                </tr>
            </thead>
            <tbody>
                @forelse($documents as $doc)
                <tr>
                    <td class="small">{{ $doc->doc_number }}</td>
                    <td class="small">{{ $doc->title }}</td>
                    <td class="small">{{ $doc->retention_code ?: '—' }}</td>
                    <td class="small">{{ $doc->disposition ?: 'Not classified' }}</td>
                    <td>
                        <form method="POST" action="{{ route('records.disposition', $doc) }}" class="d-flex gap-2">
                            @csrf @method('PATCH')
                            <select name="disposition" class="form-select form-select-sm">
                                @foreach(['For Retention','Archive','Dispose'] as $d)
                                    <option value="{{ $d }}" {{ $doc->disposition === $d ? 'selected' : '' }}>{{ $d }}</option>
                                @endforeach
                            </select>
                            <button class="btn btn-sm btn-outline-primary">Save</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" class="text-center text-muted py-4">No approved or archived records yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mb-4">{{ $documents->links() }}</div>

{{-- Review history --}}
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3"><h6 class="mb-0 fw-600">Review history</h6></div>
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead class="table-light">
                <tr>
                    <th>Date</th>
                    <th>Review</th>
                    <th>Records covered</th>
                    <th>Conducted by</th>
                    <th>Notes</th>
                </tr>
            </thead>
            <tbody>
                @forelse($reviewHistory as $rev)
                <tr>
                    <td class="small">{{ $rev->conducted_at->format('M d, Y') }}</td>
                    <td class="small">{{ $rev->type }}</td>
                    <td class="small">{{ $rev->records_count }}</td>
                    <td class="small">{{ $rev->conductor->name ?? '—' }}</td>
                    <td class="small text-muted">{{ $rev->notes }}</td>
                </tr>
                @empty
                <tr><td colspan="5" class="text-center text-muted py-4">No reviews recorded yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
