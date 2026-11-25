<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Models\DocumentRoute;
use App\Models\Campus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $user  = Auth::user();
        $query = Document::with(['submitter','campus']);

        if (!$user->isDocumentController() && !$user->isCampusDirector()) abort(403);

        $query->where('campus_id', $user->campus_id);

        if ($request->filled('category'))  $query->where('category', $request->category);
        if ($request->filled('status'))    $query->where('status', $request->status);
        if ($request->filled('direction')) $query->where('direction', $request->direction);
        if ($request->filled('date_from')) $query->whereDate('created_at','>=',$request->date_from);
        if ($request->filled('date_to'))   $query->whereDate('created_at','<=',$request->date_to);

        $total    = (clone $query)->count();
        $incoming = (clone $query)->where('direction','Incoming')->count();
        $outgoing = (clone $query)->where('direction','Outgoing')->count();
        $approved = (clone $query)->where('status','Approved')->count();
        $pending  = (clone $query)->whereNotIn('status',['Approved','Archived'])->count();
        $returned = (clone $query)->where('status','Returned')->count();
        $overdue  = (clone $query)->whereNotNull('due_date')->whereDate('due_date','<',now())->whereNotIn('status',['Approved','Archived'])->count();

        $byCategory = (clone $query)->selectRaw('category, COUNT(*) as count')->groupBy('category')->pluck('count','category');
        $byStatus   = (clone $query)->selectRaw('status, COUNT(*) as count')->groupBy('status')->pluck('count','status');
        $byMonth    = (clone $query)->selectRaw('MONTH(created_at) as month, COUNT(*) as count')->whereYear('created_at',date('Y'))->groupBy('month')->pluck('count','month');

        // Bottleneck: pending by routing unit
        $bottleneck = DocumentRoute::with('assignedUser')
            ->where('action','Pending')
            ->whereHas('document', fn($q) => $q->where('campus_id',$user->campus_id))
            ->selectRaw('assigned_to, COUNT(*) as pending_count')
            ->groupBy('assigned_to')
            ->orderByDesc('pending_count')
            ->get();

        // Avg processing time by category (days from submitted to approved)
        $processingTimes = Document::where('campus_id',$user->campus_id)
            ->where('status','Approved')
            ->whereNotNull('date_submitted')
            ->selectRaw('category, AVG(DATEDIFF(updated_at, date_submitted)) as avg_days')
            ->groupBy('category')
            ->pluck('avg_days','category');

        $documents = $query->latest()->paginate(20)->withQueryString();
        $campuses  = Campus::all();

        return view('reports.index', compact(
            'documents','total','incoming','outgoing','approved','pending','returned','overdue',
            'byCategory','byStatus','byMonth','bottleneck','processingTimes','campuses'
        ));
    }
}
