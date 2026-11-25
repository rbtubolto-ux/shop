<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Models\DocumentRoute;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // Base query scoped by role
        $base = $this->scopedQuery($user);

        $stats = [
            'total'            => (clone $base)->count(),
            'submitted'        => (clone $base)->where('status', 'Submitted')->count(),
            'in_review'        => (clone $base)->whereIn('status', ['In Review','Forwarded','Endorsed','For Approval'])->count(),
            'approved'         => (clone $base)->where('status', 'Approved')->count(),
            'returned'         => (clone $base)->where('status', 'Returned')->count(),
            'overdue'          => (clone $base)->whereNotNull('due_date')->whereDate('due_date','<',now())->whereNotIn('status',['Approved','Archived'])->count(),
            'pending_my_action'=> $this->pendingMyAction($user),
        ];

        $recentDocuments = (clone $base)
            ->with(['campus','submitter','workflowTemplate','routes' => fn($q) => $q->where('action','Pending')->orderBy('step_order')])
            ->latest()->take(10)->get();

        // Routing bottleneck (for controller/director)
        $bottleneck = null;
        if ($user->canManageDocuments()) {
            $bottleneck = DocumentRoute::with('assignedUser')
                ->where('action', 'Pending')
                ->whereHas('document', fn($q) => $q->where('campus_id', $user->campus_id))
                ->selectRaw('assigned_to, COUNT(*) as pending_count')
                ->groupBy('assigned_to')
                ->orderByDesc('pending_count')
                ->take(5)
                ->get();
        }

        // For approval (campus director)
        $forApproval = null;
        if ($user->isCampusDirector()) {
            $forApproval = DocumentRoute::with(['document.submitter','document.campus'])
                ->where('assigned_to', $user->id)
                ->where('action', 'Pending')
                ->latest()
                ->take(5)
                ->get();
        }

        $unreadNotifications = $user->notifications()->where('is_read', false)->count();

        return view('dashboard', compact(
            'stats','recentDocuments','bottleneck','forApproval','unreadNotifications'
        ));
    }

    private function scopedQuery(User $user)
    {
        $query = Document::query()->where('campus_id', $user->campus_id);

        if ($user->isDocumentSubmitter()) {
            $query->where('submitted_by', $user->id);
        } elseif ($user->isRoutingUnit()) {
            $query->whereHas('routes', fn($q) => $q->where('assigned_to', $user->id));
        }

        return $query;
    }

    private function pendingMyAction(User $user): int
    {
        return DocumentRoute::where('assigned_to', $user->id)
            ->where('action', 'Pending')
            ->whereHas('document', fn($q) => $q->where('campus_id', $user->campus_id))
            ->count();
    }
}
