<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ArchiveController extends Controller
{
    public function index(Request $request)
    {
        $user  = Auth::user();
        $query = Document::with(['submitter','campus','routes.actedByUser','workflowTemplate'])
            ->where('campus_id', $user->campus_id)
            ->whereIn('status', ['Approved', 'Archived']);

        if ($request->filled('search'))          $query->where(fn($q) => $q->where('title','like','%'.$request->search.'%')->orWhere('doc_number','like','%'.$request->search.'%'));
        if ($request->filled('category'))        $query->where('category', $request->category);
        if ($request->filled('originating_office')) $query->where('originating_office','like','%'.$request->originating_office.'%');
        if ($request->filled('date_from'))       $query->whereDate('created_at','>=',$request->date_from);
        if ($request->filled('date_to'))         $query->whereDate('created_at','<=',$request->date_to);
        if ($request->filled('routing_history')) {
            $rh = $request->routing_history;
            $query->whereHas('routes', fn($q) => $q->where('step_name','like',"%$rh%")->orWhere('comments','like',"%$rh%"));
        }

        $documents = $query->latest()->paginate(20)->withQueryString();
        return view('archive.index', compact('documents'));
    }
}
