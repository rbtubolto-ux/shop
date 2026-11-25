<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Models\DocumentRoute;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index()
    {
        $notifications = Auth::user()->notifications()->with('document')->latest()->paginate(20);
        Auth::user()->notifications()->where('is_read', false)->update(['is_read' => true]);
        return view('notifications.index', compact('notifications'));
    }

    public function markAllRead()
    {
        Auth::user()->notifications()->where('is_read', false)->update(['is_read' => true]);
        return back()->with('success', 'All notifications marked as read.');
    }

    public function getUnreadCount()
    {
        $user = Auth::user();
        $base = Document::where('campus_id', $user->campus_id);

        if ($user->isDocumentSubmitter()) $base->where('submitted_by', $user->id);
        elseif ($user->isRoutingUnit())   $base->whereHas('routes', fn($q) => $q->where('assigned_to', $user->id));

        return response()->json([
            'count'  => $user->notifications()->where('is_read', false)->count(),
            'counts' => [
                'my_action' => DocumentRoute::where('assigned_to', $user->id)->where('action', 'Pending')->count(),
                'pending'   => (clone $base)->whereIn('status',['Submitted','In Review','Forwarded','Endorsed','For Approval'])->count(),
                'overdue'   => (clone $base)->whereNotNull('due_date')->whereDate('due_date','<',now())->whereNotIn('status',['Approved','Archived'])->count(),
                'unacted'   => (clone $base)->where('status','Submitted')->count(),
                'deadlines' => (clone $base)->whereNotNull('due_date')->whereDate('due_date','<=',now()->addDays(3))->whereNotIn('status',['Approved','Archived'])->count(),
            ],
        ]);
    }
}
