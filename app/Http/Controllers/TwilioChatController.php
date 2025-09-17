<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TwilioChatController extends Controller
{
    protected function client()
    {
        $sid = config('services.twilio.sid');
        $token = config('services.twilio.token');
        if (!$sid || !$token) {
            throw new \Exception('Twilio credentials are not configured.');
        }
        return new \Twilio\Rest\Client($sid, $token);
    }

    protected function formatPhone(?string $num): string
    {
        if (!$num) return '';
        return preg_replace('/\s+/', '', $num);
    }

    protected function lookupRentalContactByPhone(?string $phone): array
    {
        // Prefer contact tied to rentals table rather than user profile
        $result = ['name' => 'Unknown', 'phone' => (string) $phone];
        if (!$phone) return $result;
        try {
            $p1 = $phone;
            $p2 = ltrim($phone, '+');
            $p3 = '+' . ltrim($phone, '+');
            $rental = \App\Models\Rental::with('user')
                ->whereIn('phone_number', [$p1, $p2, $p3])
                ->orderBy('created_at', 'desc')
                ->first();
            if ($rental) {
                $name = $rental->user->name
                    ?? optional($rental->user)->name
                    ?? 'Unknown';
                $result['name'] = trim($name) !== '' ? $name : 'Unknown';
                $result['phone'] = $rental->phone_number ?: $result['phone'];
            }
        } catch (\Throwable $e) { /* ignore */ }
        return $result;
    }

    // GET /admin/twilio/chat
    public function index(Request $request)
    {
        $fromNumber = config('services.twilio.from');
        $limit = (int) max(2000, min(2000, (int) $request->query('limit', 2000))); // fetch recent

        $conversations = [];
        $selectedPhone = $this->formatPhone($request->query('phone'));
        $error = null;

        try {
            $client = $this->client();
            // Read recent messages (both to and from). Twilio API reads sent messages; to include inbound, also list with 'from' filter is not necessary: messages->read without filter returns both directions for the account
            $records = $client->messages->read([], $limit);
            $grouped = [];

            foreach ($records as $m) {
                $peer = null;
                if ($m->direction === 'inbound' || ($fromNumber && (string) $m->to === $fromNumber)) {
                    // inbound from customer -> our from is account number
                    $peer = $this->formatPhone((string) $m->from);
                } else {
                    // outbound to customer
                    $peer = $this->formatPhone((string) $m->to);
                }
                if (!$peer) continue;

                if (!isset($grouped[$peer])) {
                    $grouped[$peer] = [
                        'phone' => $peer,
                        'messages' => [],
                        'last_time' => null,
                        'last_body' => null,
                    ];
                }

                $time = optional($m->dateSent ?? $m->dateCreated)->format('Y-m-d H:i:s');
                $grouped[$peer]['messages'][] = [
                    'sid' => (string) $m->sid,
                    'from' => (string) $m->from,
                    'to' => (string) $m->to,
                    'direction' => (string) $m->direction,
                    'body' => (string) $m->body,
                    'status' => (string) $m->status,
                    'time' => $time,
                ];
                if (!$grouped[$peer]['last_time'] || $time > $grouped[$peer]['last_time']) {
                    $grouped[$peer]['last_time'] = $time;
                    $grouped[$peer]['last_body'] = (string) $m->body;
                }
            }

            // Build conversations list with user names
            foreach ($grouped as $peer => $g) {
                $contact = $this->lookupRentalContactByPhone($peer);
                $label = trim(($contact['name'] ?? 'Unknown') . ' (' . ($contact['phone'] ?? $peer) . ')');
                $conversations[] = [
                    'phone' => $peer,
                    'label' => $label,
                    'last_time' => $g['last_time'],
                    'last_body' => $g['last_body'],
                ];
            }

            // Sort by last_time desc
            usort($conversations, function ($a, $b) {
                return strcmp($b['last_time'] ?? '', $a['last_time'] ?? '');
            });

        } catch (\Throwable $e) {
            $error = 'Failed to fetch Twilio messages: ' . $e->getMessage();
            Log::error('TwilioChat index error', ['error' => $e->getMessage()]);
        }

        return view('twilio.chat', compact('conversations', 'selectedPhone', 'error', 'fromNumber'));
    }

    // GET /admin/twilio/chat/messages?phone=+1...
    public function messages(Request $request)
    {
        $phone = $this->formatPhone($request->query('phone'));
        if (!$phone) return response()->json(['success' => false, 'message' => 'Phone is required'], 422);

        try {
            $client = $this->client();
            $limit = (int) max(20, min(500, (int) $request->query('limit', 200)));
            $records = $client->messages->read([], $limit);
            $fromNumber = config('services.twilio.from');

            $list = [];
            $seen = [];
            foreach ($records as $m) {
                $isWithPeer = $this->formatPhone($m->to) === $phone || $this->formatPhone($m->from) === $phone;
                if (!$isWithPeer) continue;
                $sid = (string) $m->sid;
                if (isset($seen[$sid])) { continue; }
                $seen[$sid] = true;
                $list[] = [
                    'sid' => $sid,
                    'from' => (string) $m->from,
                    'to' => (string) $m->to,
                    'direction' => (string) $m->direction,
                    'body' => (string) $m->body,
                    'status' => (string) $m->status,
                    'time' => optional($m->dateSent ?? $m->dateCreated)->format('Y-m-d H:i:s'),
                    'mine' => $this->formatPhone((string) $m->from) === $this->formatPhone($fromNumber),
                ];
            }
            // sort chronologically asc
            usort($list, function($a, $b){ return strcmp($a['time'] ?? '', $b['time'] ?? ''); });

            return response()->json(['success' => true, 'messages' => $list]);
        } catch (\Throwable $e) {
            Log::error('TwilioChat messages error', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Failed: ' . $e->getMessage()], 500);
        }
    }

    // POST /admin/twilio/chat/send { phone, body }
    public function send(Request $request)
    {
        $data = $request->validate([
            'phone' => 'required|string',
            'body' => 'required|string|max:1000',
        ]);
        $to = $this->formatPhone($data['phone']);
        $body = $data['body'];
        $from = config('services.twilio.from');

        try {
            $client = $this->client();
            $client->messages->create($to, [
                'from' => $from,
                'body' => $body,
            ]);
            return response()->json(['success' => true]);
        } catch (\Throwable $e) {
            Log::error('TwilioChat send failed', ['to' => $to, 'error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}


