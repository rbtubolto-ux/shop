@extends('layouts.app')

@section('title', 'Notifications')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Notifications</li>
@endsection

@section('content')
<div class="page-header d-flex align-items-center justify-content-between">
    <div>
        <h4 class="mb-1 fw-700">Notifications</h4>
        <p class="text-muted mb-0 small">Your document activity notifications</p>
    </div>
    <form method="POST" action="{{ route('notifications.markAllRead') }}">
        @csrf
        <button type="submit" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-check-all me-1"></i>Mark All Read
        </button>
    </form>
</div>

<div class="card border-0">
    <div class="card-body p-0">
        @forelse($notifications as $notif)
        <div class="d-flex align-items-start gap-3 p-3 border-bottom {{ !$notif->is_read ? 'bg-primary-subtle' : '' }}">
            <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                 style="width:40px;height:40px;background:{{ !$notif->is_read ? '#dbeafe' : '#f1f5f9' }};">
                <i class="bi bi-bell {{ !$notif->is_read ? 'text-primary' : 'text-muted' }}"></i>
            </div>
            <div class="flex-grow-1">
                <div class="small {{ !$notif->is_read ? 'fw-500' : '' }}">{{ $notif->message }}</div>
                @if($notif->document)
                <a href="{{ route('documents.show', $notif->document) }}" class="small text-primary">
                    View document &rarr;
                </a>
                @endif
                <div class="text-muted mt-1" style="font-size:0.7rem;">
                    {{ $notif->created_at->diffForHumans() }}
                </div>
            </div>
            @if(!$notif->is_read)
            <span class="badge bg-primary rounded-pill" style="font-size:0.6rem;">New</span>
            @endif
        </div>
        @empty
        <div class="text-center text-muted py-5">
            <i class="bi bi-bell-slash fs-2 d-block mb-2"></i>
            No notifications yet.
        </div>
        @endforelse
    </div>
    @if($notifications->hasPages())
    <div class="card-footer bg-white border-top py-3">
        {{ $notifications->links() }}
    </div>
    @endif
</div>
@endsection
