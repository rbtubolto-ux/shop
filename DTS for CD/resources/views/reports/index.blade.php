@extends('layouts.app')
@section('title', 'Reports')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Reports</li>
@endsection

@section('content')
<div class="page-header d-flex align-items-center justify-content-between">
    <div>
        <h4 class="mb-1 fw-700">Reports</h4>
        <p class="text-muted mb-0 small">Document statistics and summaries</p>
    </div>
    <button onclick="window.print()" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-printer me-1"></i>Print Report
    </button>
</div>

{{-- Summary Cards --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md-2">
        <div class="card border-0 text-center">
            <div class="card-body py-3">
                <div class="fs-3 fw-700 text-primary">{{ $total }}</div>
                <div class="small text-muted">Total</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-2">
        <div class="card border-0 text-center">
            <div class="card-body py-3">
                <div class="fs-3 fw-700 text-info">{{ $incoming }}</div>
                <div class="small text-muted">Incoming</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-2">
        <div class="card border-0 text-center">
            <div class="card-body py-3">
                <div class="fs-3 fw-700 text-success">{{ $outgoing }}</div>
                <div class="small text-muted">Outgoing</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-2">
        <div class="card border-0 text-center">
            <div class="card-body py-3">
                <div class="fs-3 fw-700 text-warning">{{ $pending }}</div>
                <div class="small text-muted">Pending</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-2">
        <div class="card border-0 text-center">
            <div class="card-body py-3">
                <div class="fs-3 fw-700 text-success">{{ $approved }}</div>
                <div class="small text-muted">Approved</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-2">
        <div class="card border-0 text-center">
            <div class="card-body py-3">
                <div class="fs-3 fw-700 text-danger">{{ $overdue }}</div>
                <div class="small text-muted">Overdue</div>
            </div>
        </div>
    </div>
</div>

{{-- Filters --}}
<div class="card border-0 mb-4">
    <div class="card-body py-3">
        <form method="GET" action="{{ route('reports.index') }}">
            <div class="row g-2 align-items-end">
                <div class="col-md-2">
                    <select name="direction" class="form-select form-select-sm">
                        <option value="">All Directions</option>
                        <option value="Incoming" {{ request('direction') == 'Incoming' ? 'selected' : '' }}>Incoming</option>
                        <option value="Outgoing" {{ request('direction') == 'Outgoing' ? 'selected' : '' }}>Outgoing</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="category" class="form-select form-select-sm">
                        <option value="">All Categories</option>
                        @foreach(\App\Models\Document::CATEGORIES as $cat)
                        <option value="{{ $cat }}" {{ request('category') == $cat ? 'selected' : '' }}>{{ $cat }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-select form-select-sm">
                        <option value="">All Statuses</option>
                        @foreach(\App\Models\Document::STATUSES as $s)
                        <option value="{{ $s }}" {{ request('status') == $s ? 'selected' : '' }}>{{ $s }}</option>
                        @endforeach
                    </select>
                </div>
                @if(Auth::user()->canManageDocuments())
                <div class="col-md-2">
                    <select name="campus_id" class="form-select form-select-sm">
                        <option value="">All Campuses</option>
                        @foreach($campuses as $campus)
                        <option value="{{ $campus->id }}" {{ request('campus_id') == $campus->id ? 'selected' : '' }}>{{ $campus->name }}</option>
                        @endforeach
                    </select>
                </div>
                @endif
                <div class="col-md-2">
                    <input type="date" name="date_from" value="{{ request('date_from') }}" class="form-control form-control-sm" placeholder="From">
                </div>
                <div class="col-md-2">
                    <input type="date" name="date_to" value="{{ request('date_to') }}" class="form-control form-control-sm" placeholder="To">
                </div>
            </div>
            <div class="d-flex gap-2 mt-2">
                <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-funnel me-1"></i>Filter</button>
                <a href="{{ route('reports.index') }}" class="btn btn-light btn-sm">Clear</a>
            </div>
        </form>
    </div>
</div>

<div class="row g-4 mb-4">
    {{-- By Category --}}
    <div class="col-md-6">
        <div class="card border-0 h-100">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="mb-0 fw-600">By Category</h6>
            </div>
            <div class="card-body">
                @php $colors = ['primary','warning','info','success','danger','secondary']; @endphp
                @foreach(\App\Models\Document::CATEGORIES as $i => $cat)
                @php $count = $byCategory[$cat] ?? 0; @endphp
                <div class="mb-3">
                    <div class="d-flex justify-content-between small mb-1">
                        <span class="fw-500">{{ $cat }}</span>
                        <span class="text-muted">{{ $count }}</span>
                    </div>
                    <div class="progress" style="height:6px;">
                        <div class="progress-bar bg-{{ $colors[$i % count($colors)] }}"
                             style="width:{{ $total > 0 ? ($count / $total * 100) : 0 }}%"></div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- By Status --}}
    <div class="col-md-6">
        <div class="card border-0 h-100">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="mb-0 fw-600">By Status</h6>
            </div>
            <div class="card-body">
                @foreach(\App\Models\Document::STATUSES as $status)
                @php $count = $byStatus[$status] ?? 0; $color = \App\Models\Document::STATUS_COLORS[$status] ?? 'secondary'; @endphp
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="badge bg-{{ $color }}-subtle text-{{ $color }} badge-status">{{ $status }}</span>
                    <div class="d-flex align-items-center gap-2">
                        <div class="progress flex-grow-1" style="width:120px;height:6px;">
                            <div class="progress-bar bg-{{ $color }}"
                                 style="width:{{ $total > 0 ? ($count / $total * 100) : 0 }}%"></div>
                        </div>
                        <span class="small text-muted" style="min-width:24px;">{{ $count }}</span>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

{{-- Monthly breakdown --}}
<div class="card border-0 mb-4">
    <div class="card-header bg-white border-bottom py-3">
        <h6 class="mb-0 fw-600">Monthly Breakdown ({{ date('Y') }})</h6>
    </div>
    <div class="card-body">
        @php $months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec']; @endphp
        <div class="row g-2">
            @foreach($months as $i => $month)
            @php $count = $byMonth[$i+1] ?? 0; $max = $byMonth->max() ?: 1; @endphp
            <div class="col text-center">
                <div class="small text-muted mb-1">{{ $month }}</div>
                <div class="bg-primary rounded mx-auto"
                     style="width:28px;height:{{ max(4, ($count/$max)*80) }}px;opacity:{{ $count > 0 ? 0.7 + ($count/$max)*0.3 : 0.15 }};"></div>
                <div class="small fw-600 mt-1">{{ $count }}</div>
            </div>
            @endforeach
        </div>
    </div>
</div>

{{-- Document List --}}
<div class="card border-0">
    <div class="card-header bg-white border-bottom py-3 d-flex align-items-center justify-content-between">
        <h6 class="mb-0 fw-600">Document List</h6>
        <span class="small text-muted">{{ $documents->total() }} records</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 small">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">Doc Number</th>
                        <th>Title</th>
                        <th>Category</th>
                        <th>Direction</th>
                        <th>Status</th>
                        <th>Signatory</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($documents as $doc)
                    <tr>
                        <td class="ps-3">
                            <a href="{{ route('documents.show', $doc) }}" class="text-primary fw-600">{{ $doc->doc_number }}</a>
                        </td>
                        <td>{{ Str::limit($doc->title, 40) }}</td>
                        <td>{{ $doc->category }}</td>
                        <td>
                            <span class="badge bg-{{ $doc->direction == 'Incoming' ? 'info' : 'success' }}-subtle text-{{ $doc->direction == 'Incoming' ? 'info' : 'success' }}">
                                {{ $doc->direction }}
                            </span>
                        </td>
                        <td>
                            <span class="badge bg-{{ $doc->statusColor() }}-subtle text-{{ $doc->statusColor() }}">{{ $doc->status }}</span>
                        </td>
                        <td class="text-muted">{{ $doc->signatory ?? '—' }}</td>
                        <td class="text-muted">{{ $doc->created_at->format('M d, Y') }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="text-center text-muted py-4">No documents found.</td></tr>
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
