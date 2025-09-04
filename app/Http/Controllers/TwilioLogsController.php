<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TwilioLogsController extends Controller
{
    public function index(Request $request)
    {
        $sid = config('services.twilio.sid');
        $token = config('services.twilio.token');
        $from = config('services.twilio.from');

        $error = null;
        $messages = [];
        $summary = [
            'total' => 0,
            'sent' => 0,
            'delivered' => 0,
            'failed' => 0,
            'undelivered' => 0,
            'queued' => 0,
        ];

        $filters = [
            'to' => trim((string) $request->query('to', '')),
            'status' => trim((string) $request->query('status', '')),
            'start' => $request->query('start'),
            'end' => $request->query('end'),
            'limit' => (int) max(1, min(1000, (int) $request->query('limit', 100))),
        ];

        if ($sid && $token) {
            try {
                $client = new \Twilio\Rest\Client($sid, $token);

                // Build Twilio list filters (limit to recent window for performance)
                $twilioFilters = [];
                $limit = (int) $filters['limit'];
                if (!empty($filters['to'])) { $twilioFilters['to'] = $filters['to']; }
                if (!empty($filters['start'])) { $twilioFilters['dateSentAfter'] = new \DateTime($filters['start'] . ' 00:00:00'); }
                if (!empty($filters['end'])) { $twilioFilters['dateSentBefore'] = new \DateTime($filters['end'] . ' 23:59:59'); }

                $records = $client->messages->read($twilioFilters, $limit);

                foreach ($records as $m) {
                    // Optional status filter (apply in PHP to be safe)
                    if (!empty($filters['status']) && (string) $m->status !== $filters['status']) { continue; }

                    $messages[] = [
                        'sid' => $m->sid,
                        'to' => $m->to,
                        'from' => $m->from,
                        'status' => (string) $m->status,
                        'body' => (string) $m->body,
                        'error_code' => $m->errorCode,
                        'error_message' => $m->errorMessage,
                        'num_segments' => $m->numSegments,
                        'price' => $m->price,
                        'date_created' => optional($m->dateCreated)->format('Y-m-d H:i:s'),
                        'date_sent' => optional($m->dateSent)->format('Y-m-d H:i:s'),
                        'date_updated' => optional($m->dateUpdated)->format('Y-m-d H:i:s'),
                    ];

                    $summary['total']++;
                    $status = (string) $m->status;
                    if (isset($summary[$status])) { $summary[$status]++; }
                    elseif ($status === 'sent') { $summary['sent']++; }
                    elseif ($status === 'delivered') { $summary['delivered']++; }
                    elseif ($status === 'failed') { $summary['failed']++; }
                    elseif ($status === 'undelivered') { $summary['undelivered']++; }
                    elseif ($status === 'queued') { $summary['queued']++; }
                }
            } catch (\Throwable $e) {
                $error = 'Unable to fetch Twilio logs. ' . $e->getMessage();
                Log::error('Twilio Logs fetch failed', ['error' => $e->getMessage()]);
            }
        } else {
            $error = 'Twilio credentials are not configured.';
        }

        // Sort newest first by date_sent or date_created
        usort($messages, function($a, $b){
            return strcmp($b['date_sent'] ?? $b['date_created'], $a['date_sent'] ?? $a['date_created']);
        });

        return view('twilio.logs', [
            'messages' => $messages,
            'summary' => $summary,
            'filters' => $filters,
            'error' => $error,
        ]);
    }
}


