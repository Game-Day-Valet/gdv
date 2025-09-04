<?php

namespace App\Http\Controllers;

use App\Models\EmailLog;
use Illuminate\Http\Request;

class EmailLogsController extends Controller
{
    public function index(Request $request)
    {
        $query = EmailLog::query();

        $to = trim((string) $request->query('to', ''));
        $status = trim((string) $request->query('status', ''));
        $start = $request->query('start');
        $end = $request->query('end');
        $limit = (int) max(1, min(1000, (int) $request->query('limit', 100)));

        if ($to !== '') { $query->where('to_email', 'like', "%{$to}%"); }
        if ($status !== '') { $query->where('status', $status); }
        if (!empty($start)) { $query->whereDate('created_at', '>=', $start); }
        if (!empty($end)) { $query->whereDate('created_at', '<=', $end); }

        $logs = $query->orderByDesc('created_at')->limit($limit)->get();

        $summary = [
            'total' => EmailLog::count(),
            'sent' => EmailLog::where('status', 'sent')->count(),
            'failed' => EmailLog::where('status', 'failed')->count(),
            'queued' => EmailLog::where('status', 'queued')->count(),
        ];

        return view('emails.logs', [
            'logs' => $logs,
            'filters' => compact('to', 'status', 'start', 'end', 'limit'),
            'summary' => $summary,
        ]);
    }
}


