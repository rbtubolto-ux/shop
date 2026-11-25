<?php

namespace App\Http\Controllers;

use App\Models\DocumentLog;
use Illuminate\Support\Facades\Auth;

// FAM 13.1 section 3.7: the logbook of official acts, transactions and releases, open to the public.
// Entries for confidential records are shown only to managers.
class LogbookController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $logs = DocumentLog::with(['document', 'performer'])
            ->whereHas('document', function ($q) use ($user) {
                $q->where('campus_id', $user->campus_id);
                if (!$user->canManageDocuments()) {
                    $q->where('is_confidential', false);
                }
            })
            ->latest()
            ->paginate(30);

        return view('logbook.index', compact('logs'));
    }
}
