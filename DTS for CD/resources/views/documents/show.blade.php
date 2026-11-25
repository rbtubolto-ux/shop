@extends('layouts.app')
@section('title',$document->doc_number)
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('documents.index') }}">Documents</a></li>
    <li class="breadcrumb-item active">{{ $document->doc_number }}</li>
@endsection

@section('content')
<div class="page-hdr d-flex align-items-start justify-content-between">
    <div>
        <div class="d-flex align-items-center gap-2 mb-1">
            <span class="badge bg-light text-dark"><i class="{{ $document->categoryIcon() }} me-1"></i>{{ $document->category }}</span>
            <span class="badge bg-{{ $document->statusColor() }}-subtle text-{{ $document->statusColor() }} badge-s">{{ $document->status }}</span>
            <span class="badge bg-{{ $document->direction=='Incoming'?'info':'success' }}-subtle text-{{ $document->direction=='Incoming'?'info':'success' }}">{{ $document->direction }}</span>
            @if($document->isOverdue())<span class="badge bg-danger">Overdue</span>@endif
        </div>
        <h4 class="mb-1 fw-700">{{ $document->title }}</h4>
        <p class="text-muted mb-0 small">
            <strong>{{ $document->doc_number }}</strong> · {{ $document->campus->name ?? '' }} ·
            Submitted by {{ $document->submitter->name ?? '' }} on {{ $document->created_at->format('M d, Y') }}
        </p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <a href="{{ route('documents.barcode',$document) }}" class="btn btn-sm btn-outline-secondary" target="_blank">
            <i class="bi bi-upc-scan me-1"></i>Barcode
        </a>
        @if(in_array($document->status,['Draft','Returned']))
        <a href="{{ route('documents.edit',$document) }}" class="btn btn-sm btn-outline-primary">
            <i class="bi bi-pencil me-1"></i>Edit
        </a>
        @endif
        @if(Auth::user()->canManageDocuments() && in_array($document->status,['Approved']))
        <form method="POST" action="{{ route('documents.archive',$document) }}">
            @csrf @method('PATCH')
            <button class="btn btn-sm btn-outline-secondary"><i class="bi bi-archive me-1"></i>Archive</button>
        </form>
        @endif
        @if(in_array($document->status,['Draft','Returned']))
        <form method="POST" action="{{ route('documents.destroy',$document) }}" onsubmit="return confirm('Delete this document?')">
            @csrf @method('DELETE')
            <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash me-1"></i>Delete</button>
        </form>
        @endif
    </div>
</div>

<div class="row g-4">
    {{-- Left: Details + Routing Timeline + Logs --}}
    <div class="col-lg-8">
        {{-- Document Details --}}
        <div class="card border-0 mb-4">
            <div class="card-header bg-white border-bottom py-3"><h6 class="mb-0 fw-600">Document Details</h6></div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6"><div class="small text-muted mb-1">Document Number</div><div class="fw-600">{{ $document->doc_number }}</div></div>
                    <div class="col-md-6"><div class="small text-muted mb-1">Category</div><div>{{ $document->category }}</div></div>
                    <div class="col-md-6"><div class="small text-muted mb-1">Originating Office</div><div>{{ $document->originating_office ?? '—' }}</div></div>
                    <div class="col-md-6"><div class="small text-muted mb-1">Signatory</div><div>{{ $document->signatory ?? '—' }}</div></div>
                    <div class="col-md-6"><div class="small text-muted mb-1">Date Submitted</div><div>{{ $document->date_submitted?->format('F d, Y') ?? '—' }}</div></div>
                    <div class="col-md-6">
                        <div class="small text-muted mb-1">Due Date</div>
                        <div class="{{ $document->isOverdue() ? 'text-danger fw-600' : '' }}">
                            {{ $document->due_date?->format('F d, Y') ?? '—' }}
                        </div>
                    </div>
                    <div class="col-md-6"><div class="small text-muted mb-1">Workflow</div><div>{{ $document->workflowTemplate->name ?? 'No workflow' }}</div></div>
                    <div class="col-md-6">
                        <div class="small text-muted mb-1">File Container</div>
                        <div class="small">
                            @if($document->cabinet || $document->drawer || $document->container)
                                <i class="bi bi-folder me-1"></i>{{ implode(' / ', array_filter([$document->cabinet,$document->drawer,$document->container])) }}
                            @else —
                            @endif
                        </div>
                    </div>
                    @if($document->description)
                    <div class="col-12"><div class="small text-muted mb-1">Description</div><div>{{ $document->description }}</div></div>
                    @endif
                    @if($document->remarks)
                    <div class="col-12"><div class="small text-muted mb-1">Remarks</div><div class="p-2 bg-light rounded small">{{ $document->remarks }}</div></div>
                    @endif
                </div>
                @if($document->file_path)
                <div class="mt-3 pt-3 border-top">
                    <div class="small text-muted mb-2">Attached File</div>
                    <a href="{{ route('documents.file', $document) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-paperclip me-1"></i>{{ $document->file_name }}
                    </a>
                </div>
                @endif

                {{-- Signature --}}
                @if($document->signature_path)
                <div class="mt-3 pt-3 border-top">
                    <div class="small text-muted mb-2"><i class="bi bi-pen me-1"></i>Digital Signature</div>
                    <div class="p-3 border rounded text-center" style="background:#f8fffe;max-width:280px;">
                        <img src="{{ asset('storage/'.$document->signature_path) }}"
                             alt="Signature" style="max-height:80px;max-width:240px;">
                        <div class="small text-success mt-2 fw-500">
                            <i class="bi bi-check-circle me-1"></i>
                            Approved by {{ $document->approver->name ?? '' }}
                            @if($document->approved_at)
                                on {{ $document->approved_at->format('F d, Y h:i A') }}
                            @endif
                        </div>
                    </div>
                </div>
                @elseif($document->status === 'Approved')
                <div class="mt-3 pt-3 border-top">
                    <div class="small text-success fw-500">
                        <i class="bi bi-check-circle me-1"></i>
                        Approved by {{ $document->approver->name ?? '' }}
                        @if($document->approved_at)
                            on {{ $document->approved_at->format('F d, Y h:i A') }}
                        @endif
                    </div>
                </div>
                @endif
            </div>
        </div>

        {{-- Routing Timeline --}}
        @if($document->routes->count())
        <div class="card border-0 mb-4">
            <div class="card-header bg-white border-bottom py-3"><h6 class="mb-0 fw-600">Routing Timeline</h6></div>
            <div class="card-body">
                <div class="timeline">
                    @foreach($document->routes as $route)
                    <div class="tl-item">
                        <div class="d-flex align-items-start justify-content-between">
                            <div class="flex-grow-1">
                                <div class="d-flex align-items-center gap-2 mb-1">
                                    <span class="fw-600 small">Step {{ $route->step_order }}: {{ $route->step_name }}</span>
                                    <span class="badge bg-{{ $route->actionColor() }}-subtle text-{{ $route->actionColor() }} badge-s">
                                        {{ $route->action }}
                                    </span>
                                </div>
                                <div class="small text-muted">
                                    Assigned to: <strong>{{ $route->assignedUser->name ?? 'Unassigned' }}</strong>
                                    @if($route->assignedUser) ({{ $route->assignedUser->department ?? '' }}) @endif
                                </div>
                                @if($route->comments)
                                <div class="small mt-1 p-2 bg-light rounded">{{ $route->comments }}</div>
                                @endif
                                @if($route->acted_by)
                                <div class="d-flex align-items-center gap-3 mt-2">
                                    <div>
                                        <div class="small text-muted">
                                            Acted by <strong>{{ $route->actedByUser->name ?? '' }}</strong>
                                            on {{ $route->acted_at?->format('M d, Y H:i') }}
                                        </div>
                                    </div>
                                    {{-- Show signature if available --}}
                                    @if($route->signature_path)
                                    <div class="border rounded p-1 bg-white" style="max-width:120px;">
                                        <img src="{{ asset('storage/'.$route->signature_path) }}"
                                             alt="Signature"
                                             style="max-height:40px;max-width:110px;object-fit:contain;">
                                    </div>
                                    @endif
                                </div>
                                @endif
                            </div>
                            <div class="text-muted small text-nowrap ms-3">
                                @if($route->received_at) Received {{ $route->received_at->format('M d') }} @endif
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

        {{-- Activity Log --}}
        <div class="card border-0">
            <div class="card-header bg-white border-bottom py-3"><h6 class="mb-0 fw-600">Activity Log</h6></div>
            <div class="card-body">
                @if($document->logs->count())
                <div class="timeline">
                    @foreach($document->logs as $log)
                    <div class="tl-item">
                        <div class="d-flex justify-content-between">
                            <div>
                                <div class="fw-500 small">{{ $log->action }}</div>
                                @if($log->old_status && $log->new_status)
                                <div class="small text-muted">
                                    <span class="badge bg-secondary-subtle text-secondary">{{ $log->old_status }}</span>
                                    <i class="bi bi-arrow-right mx-1"></i>
                                    <span class="badge bg-primary-subtle text-primary">{{ $log->new_status }}</span>
                                </div>
                                @endif
                                @if($log->remarks)<div class="small text-muted mt-1">{{ $log->remarks }}</div>@endif
                                <div class="small text-muted">by <strong>{{ $log->performer->name ?? 'System' }}</strong></div>
                            </div>
                            <div class="small text-muted text-nowrap ms-3">{{ $log->created_at->format('M d, Y H:i') }}</div>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <p class="text-muted small mb-0">No activity recorded yet.</p>
                @endif
            </div>
        </div>
    </div>

    {{-- Right: Action Panel + Related Docs --}}
    <div class="col-lg-4">
        {{-- Submit (if Draft) --}}
        @if($document->status === 'Draft' && $document->submitted_by === Auth::id())
        <div class="card border-0 border-start border-4 border-primary mb-4">
            <div class="card-header bg-white border-bottom py-3"><h6 class="mb-0 fw-600">Submit Document</h6></div>
            <div class="card-body">
                <p class="small text-muted">This document is saved as a draft. Submit it to start the routing workflow.</p>
                <form method="POST" action="{{ route('documents.submit',$document) }}">
                    @csrf @method('PATCH')
                    <button class="btn btn-primary w-100"><i class="bi bi-send me-1"></i>Submit Document</button>
                </form>
            </div>
        </div>
        @endif

        {{-- Routing Action Panel --}}
        @if($canAct)
        <div class="card border-0 border-start border-4 border-warning mb-4">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="mb-0 fw-600"><i class="bi bi-hand-index me-2 text-warning"></i>Your Action Required</h6>
                <div class="small text-muted">Step {{ $currentRoute->step_order }}: {{ $currentRoute->step_name }}</div>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('documents.routeAction',$document) }}"
                      enctype="multipart/form-data" x-data="{ action: '' }">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label small fw-500">Action</label>
                        <div class="d-flex flex-wrap gap-2">
                            @if(Auth::user()->isCampusDirector())
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="action" value="Approved" id="act-approve" x-model="action" required>
                                    <label class="form-check-label small" for="act-approve"><span class="badge bg-success-subtle text-success">Approve</span></label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="action" value="Returned" id="act-return" x-model="action">
                                    <label class="form-check-label small" for="act-return"><span class="badge bg-danger-subtle text-danger">Return</span></label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="action" value="Noted" id="act-note" x-model="action">
                                    <label class="form-check-label small" for="act-note"><span class="badge bg-secondary-subtle text-secondary">Note</span></label>
                                </div>
                            @else
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="action" value="Forwarded" id="act-fwd" x-model="action" required>
                                    <label class="form-check-label small" for="act-fwd"><span class="badge bg-primary-subtle text-primary">Forward</span></label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="action" value="Endorsed" id="act-end" x-model="action">
                                    <label class="form-check-label small" for="act-end"><span class="badge bg-info-subtle text-info">Endorse</span></label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="action" value="Returned" id="act-ret" x-model="action">
                                    <label class="form-check-label small" for="act-ret"><span class="badge bg-danger-subtle text-danger">Return</span></label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="action" value="Noted" id="act-nt" x-model="action">
                                    <label class="form-check-label small" for="act-nt"><span class="badge bg-secondary-subtle text-secondary">Note</span></label>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Signature upload — shown only when Approve is selected --}}
                    @if(Auth::user()->isCampusDirector())
                    <div x-show="action === 'Approved'" class="mb-3 p-3 bg-success-subtle rounded">
                        <label class="form-label small fw-500 text-success">
                            <i class="bi bi-pen me-1"></i>Attach Digital Signature (optional)
                        </label>
                        <input type="file" name="signature" class="form-control form-control-sm"
                               accept=".png,.jpg,.jpeg">
                        <div class="form-text">Upload your signature image (PNG or JPG, max 2MB)</div>
                        @if($document->signature_path)
                        <div class="mt-2 small text-success">
                            <i class="bi bi-check-circle me-1"></i>Signature already attached — upload new to replace
                        </div>
                        @endif
                    </div>
                    @endif

                    <div class="mb-3">
                        <label class="form-label small fw-500">Comments / Directives</label>
                        <textarea name="comments" rows="3" class="form-control form-control-sm"
                                  placeholder="Add comments, directives, or reason for return..."></textarea>
                    </div>
                    <button type="submit" class="btn btn-warning w-100 text-dark fw-600">
                        <i class="bi bi-check-lg me-1"></i>Submit Action
                    </button>
                </form>
            </div>
        </div>
        @endif

        {{-- Status Info --}}
        <div class="card border-0 mb-4">
            <div class="card-header bg-white border-bottom py-3"><h6 class="mb-0 fw-600">Status</h6></div>
            <div class="card-body">
                @foreach(\App\Models\Document::STATUSES as $s)
                <div class="d-flex align-items-center gap-2 mb-2">
                    <span class="badge bg-{{ \App\Models\Document::STATUS_COLORS[$s]??'secondary' }}-subtle text-{{ \App\Models\Document::STATUS_COLORS[$s]??'secondary' }} badge-s" style="min-width:100px;">{{ $s }}</span>
                    @if($document->status===$s)<i class="bi bi-arrow-left-short text-primary fw-bold"></i><span class="small text-primary fw-500">Current</span>@endif
                </div>
                @endforeach
            </div>
        </div>

        {{-- Related Documents --}}
        @if($document->relatedDocuments->count())
        <div class="card border-0">
            <div class="card-header bg-white border-bottom py-3"><h6 class="mb-0 fw-600">Related Documents</h6></div>
            <div class="card-body p-0">
                @foreach($document->relatedDocuments as $rel)
                <div class="p-3 border-bottom">
                    <a href="{{ route('documents.show',$rel) }}" class="text-primary fw-600 small">{{ $rel->doc_number }}</a>
                    <div class="small text-muted">{{ Str::limit($rel->title,40) }}</div>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
