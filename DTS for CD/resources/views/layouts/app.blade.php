<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title','DTS') — Campus Document Tracking System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root{--sw:265px;--primary:#1a56db;}
        *{font-family:'Inter',sans-serif;}
        body{background:#f1f5f9;min-height:100vh;}
        #sidebar{width:var(--sw);min-height:100vh;background:linear-gradient(180deg,#1e3a5f,#1a2e4a);position:fixed;top:0;left:0;z-index:1000;overflow-y:auto;transition:transform .3s;}
        .sb-brand{padding:1.1rem 1rem .75rem;border-bottom:1px solid rgba(255,255,255,.1);}
        .sb-brand h5{color:#fff;font-weight:700;font-size:.9rem;margin:0;}
        .sb-brand small{color:rgba(255,255,255,.45);font-size:.68rem;}
        .sb-user{background:rgba(255,255,255,.08);border-radius:8px;padding:.5rem .75rem;margin:.6rem .75rem 0;}
        .sb-user .name{color:#fff;font-size:.78rem;font-weight:600;}
        .sb-user .role{color:rgba(255,255,255,.45);font-size:.68rem;}
        .nav-section{color:rgba(255,255,255,.3);font-size:.62rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em;padding:.7rem 1rem .2rem;}
        .nav-link{color:rgba(255,255,255,.65);padding:.45rem .9rem;border-radius:7px;margin:1px .6rem;font-size:.8rem;font-weight:500;transition:all .2s;display:flex;align-items:center;gap:.5rem;}
        .nav-link:hover,.nav-link.active{background:rgba(255,255,255,.12);color:#fff;}
        .nav-link i{font-size:.9rem;width:17px;}
        .qf{color:rgba(255,255,255,.55);padding:.32rem .9rem .32rem 2.2rem;font-size:.76rem;display:flex;align-items:center;justify-content:space-between;border-radius:6px;margin:1px .6rem;text-decoration:none;transition:all .2s;}
        .qf:hover{background:rgba(255,255,255,.08);color:#fff;}
        #main{margin-left:var(--sw);min-height:100vh;}
        #topbar{background:#fff;border-bottom:1px solid #e2e8f0;padding:.6rem 1.5rem;position:sticky;top:0;z-index:999;}
        .stat-card{border:none;border-radius:12px;transition:transform .2s,box-shadow .2s;}
        .stat-card:hover{transform:translateY(-2px);box-shadow:0 8px 25px rgba(0,0,0,.1);}
        .badge-s{font-size:.7rem;font-weight:500;padding:.28em .65em;border-radius:20px;}
        .page-hdr{background:#fff;border-radius:12px;padding:1.1rem 1.4rem;margin-bottom:1.25rem;border:1px solid #e2e8f0;}
        .timeline{position:relative;padding-left:1.75rem;}
        .timeline::before{content:'';position:absolute;left:.45rem;top:0;bottom:0;width:2px;background:#e2e8f0;}
        .tl-item{position:relative;margin-bottom:1.1rem;}
        .tl-item::before{content:'';position:absolute;left:-1.4rem;top:.3rem;width:10px;height:10px;border-radius:50%;background:var(--primary);border:2px solid #fff;box-shadow:0 0 0 2px var(--primary);}
        .nb{position:absolute;top:-4px;right:-4px;font-size:.58rem;min-width:15px;height:15px;display:flex;align-items:center;justify-content:center;border-radius:50%;}
        @media(max-width:768px){#sidebar{transform:translateX(-100%);}#sidebar.show{transform:translateX(0);}#main{margin-left:0;}}
    </style>
    @stack('styles')
</head>
<body>
<nav id="sidebar">
    <div class="sb-brand">
        <div class="d-flex align-items-center gap-2">
            <div style="background:rgba(255,255,255,.15);border-radius:8px;padding:5px 7px;">
                <i class="bi bi-file-earmark-text-fill text-white fs-5"></i>
            </div>
            <div><h5>Campus DTS</h5><small>Document Tracking System</small></div>
        </div>
        @auth
        <div class="sb-user mt-2">
            <div class="name">{{ Auth::user()->name }}</div>
            <div class="role">{{ Auth::user()->campus->name ?? 'System' }} · {{ Auth::user()->getRoleLabel() }}</div>
        </div>
        @endauth
    </div>

    <div class="py-2">
        <span class="nav-section">Main</span>
        <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active':'' }}">
            <i class="bi bi-speedometer2"></i> Dashboard
        </a>

        <span class="nav-section">Documents</span>
        <a href="{{ route('documents.index',['direction'=>'Incoming']) }}" class="nav-link {{ request()->routeIs('documents.index') && request('direction','Incoming')=='Incoming' ? 'active':'' }}">
            <i class="bi bi-box-arrow-in-down"></i> Incoming
        </a>
        <a href="{{ route('documents.index',['direction'=>'Outgoing']) }}" class="nav-link {{ request()->routeIs('documents.index') && request('direction')=='Outgoing' ? 'active':'' }}">
            <i class="bi bi-box-arrow-up"></i> Outgoing
        </a>
        <a href="{{ route('documents.create') }}" class="nav-link">
            <i class="bi bi-plus-circle"></i> Submit Document
        </a>

        @auth
        <span class="nav-section">Quick Filters</span>
        <a href="{{ route('documents.index',['quick'=>'my_action']) }}" class="qf">
            <span><i class="bi bi-hand-index me-1"></i>Pending My Action</span>
            <span class="badge bg-primary" id="cnt-my_action">·</span>
        </a>
        <a href="{{ route('documents.index',['quick'=>'pending']) }}" class="qf">
            <span><i class="bi bi-clock me-1"></i>Pending</span>
            <span class="badge bg-warning text-dark" id="cnt-pending">·</span>
        </a>
        <a href="{{ route('documents.index',['quick'=>'overdue']) }}" class="qf">
            <span><i class="bi bi-exclamation-circle me-1"></i>Over Due</span>
            <span class="badge bg-danger" id="cnt-overdue">·</span>
        </a>
        <a href="{{ route('documents.index',['quick'=>'unacted']) }}" class="qf">
            <span><i class="bi bi-inbox me-1"></i>Unacted</span>
            <span class="badge bg-secondary" id="cnt-unacted">·</span>
        </a>
        <a href="{{ route('documents.index',['quick'=>'deadlines']) }}" class="qf">
            <span><i class="bi bi-calendar-x me-1"></i>Deadlines</span>
            <span class="badge bg-info text-dark" id="cnt-deadlines">·</span>
        </a>

        @if(Auth::user()->isDocumentController())
        <span class="nav-section">Management</span>
        <a href="{{ route('workflows.index') }}" class="nav-link {{ request()->routeIs('workflows.*') ? 'active':'' }}">
            <i class="bi bi-diagram-3"></i> Workflows
        </a>
        @endif
        <a href="{{ route('users.index') }}" class="nav-link {{ request()->routeIs('users.*') ? 'active':'' }}">
            <i class="bi bi-people"></i> Users
        </a>
        @if(Auth::user()->isDocumentController())
        {{-- Add user only for controller --}}
        @endif

        <span class="nav-section">Reports & Archive</span>
        @if(Auth::user()->canManageDocuments())
        <a href="{{ route('reports.index') }}" class="nav-link {{ request()->routeIs('reports.*') ? 'active':'' }}">
            <i class="bi bi-bar-chart"></i> Reports
        </a>
        @endif
        <a href="{{ route('archive.index') }}" class="nav-link {{ request()->routeIs('archive.*') ? 'active':'' }}">
            <i class="bi bi-archive"></i> Archive
        </a>
        <a href="{{ route('notifications.index') }}" class="nav-link {{ request()->routeIs('notifications.*') ? 'active':'' }}">
            <i class="bi bi-bell"></i> Notifications
            <span id="sb-notif" class="badge bg-danger ms-auto" style="display:none;"></span>
        </a>
        <a href="{{ route('profile.show') }}" class="nav-link {{ request()->routeIs('profile.*') ? 'active':'' }}">
            <i class="bi bi-person-circle"></i> My Profile & Signature
        </a>
        <form method="POST" action="{{ route('logout') }}" class="m-0">
            @csrf
            <button type="submit" class="nav-link w-100 border-0 bg-transparent text-start">
                <i class="bi bi-box-arrow-left"></i> Logout
            </button>
        </form>
        @endauth
    </div>
</nav>

<div id="main">
    <div id="topbar" class="d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center gap-3">
            <button class="btn btn-sm btn-light d-md-none" onclick="document.getElementById('sidebar').classList.toggle('show')">
                <i class="bi bi-list fs-5"></i>
            </button>
            <nav aria-label="breadcrumb" class="d-none d-md-block">
                <ol class="breadcrumb mb-0 small">@yield('breadcrumb')</ol>
            </nav>
        </div>
        <div class="d-flex align-items-center gap-3">
            <a href="{{ route('notifications.index') }}" class="btn btn-sm btn-light position-relative">
                <i class="bi bi-bell"></i>
                <span id="tb-notif" class="badge bg-danger nb" style="display:none;"></span>
            </a>
            <div class="d-flex align-items-center gap-2">
                <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center"
                     style="width:30px;height:30px;font-size:.75rem;font-weight:700;">
                    {{ strtoupper(substr(Auth::user()->name??'U',0,1)) }}
                </div>
                <span class="d-none d-md-inline small fw-500">{{ Auth::user()->name??'' }}</span>
            </div>
        </div>
    </div>

    <div class="p-4">
        @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show py-2">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif
        @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show py-2">
            <i class="bi bi-exclamation-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif
        @yield('content')
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.1/dist/cdn.min.js"></script>
<script>
function updateCounts(){
    fetch('{{ route("notifications.unreadCount") }}')
        .then(r=>r.json()).then(d=>{
            const c=d.count;
            ['tb-notif','sb-notif'].forEach(id=>{
                const el=document.getElementById(id);
                if(el){el.textContent=c>99?'99+':c;el.style.display=c>0?'':'none';}
            });
            if(d.counts){
                Object.keys(d.counts).forEach(k=>{
                    const el=document.getElementById('cnt-'+k);
                    if(el) el.textContent=d.counts[k]??0;
                });
            }
        }).catch(()=>{});
}
updateCounts();
setInterval(updateCounts,30000);
</script>
@stack('scripts')
</body>
</html>
