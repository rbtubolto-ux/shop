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
        <p class="text-muted mb-0 small">Inventory, appraisal and semi-annual classification (FAM 13.1 sections 3.11 to 3.15).</p>
    </div>
    <button onclick="window.print()" class="btn btn-outline-secondary btn-sm"><i class="bi bi-printer me-1"></i>Print</button>
</div>

@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif

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

<div class="card border-0 shadow-sm">
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
<div class="mt-3">{{ $documents->links() }}</div>
@endsection
