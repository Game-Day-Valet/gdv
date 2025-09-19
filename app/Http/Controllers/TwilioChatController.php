<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

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

    protected function extractMediaUrls($media): array
    {
        if (!$media) return [];
        
        $urls = [];
        try {
            foreach ($media as $mediaItem) {
                if (isset($mediaItem->uri)) {
                    $urls[] = (string) $mediaItem->uri;
                }
            }
        } catch (\Throwable $e) {
            Log::warning('Failed to extract media URLs', ['error' => $e->getMessage()]);
        }
        
        return $urls;
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
                    'media_urls' => $this->extractMediaUrls($m->media),
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

    // POST /admin/twilio/chat/send { phone, body, media_urls }
    public function send(Request $request)
    {
        $data = $request->validate([
            'phone' => 'required|string',
            'body' => 'nullable|string|max:1000',
            'media_urls' => 'nullable|array',
            'media_urls.*' => 'url|max:2048',
        ]);
        
        $to = $this->formatPhone($data['phone']);
        $body = $data['body'] ?? '';
        $mediaUrls = $data['media_urls'] ?? [];
        $from = config('services.twilio.from');

        // Validate that either body or media_urls is provided
        if (empty($body) && empty($mediaUrls)) {
            return response()->json(['success' => false, 'message' => 'Either message body or media is required'], 422);
        }

        // Guard against Twilio 11200: MediaUrl must be publicly accessible over HTTPS
        $invalidMedia = [];
        $sanitizedMedia = [];
        foreach ($mediaUrls as $url) {
            $isHttps = stripos($url, 'https://') === 0;
            $isLocal = preg_match('#^(https?://)?(localhost|127\.0\.0\.1|0\.0\.0\.0|\[::1\])#i', $url);
            if (!$isHttps || $isLocal) {
                $invalidMedia[] = $url;
            } else {
                $sanitizedMedia[] = $url;
            }
        }
        if (!empty($invalidMedia)) {
            Log::warning('TwilioChat blocked non-public media', ['invalid_media' => $invalidMedia]);
            return response()->json([
                'success' => false,
                'message' => 'Images must be publicly accessible over HTTPS. On local, use an https tunnel (e.g., ngrok) or upload to a public host (S3/Cloudinary).',
                'invalid_media' => $invalidMedia,
                'code' => 11200,
            ], 422);
        }

        try {
            $client = $this->client();
            $messageData = [
                'from' => $from,
            ];
            
            if (!empty($body)) {
                $messageData['body'] = $body;
            }
            
            if (!empty($sanitizedMedia)) {
                $messageData['mediaUrl'] = $sanitizedMedia;
            }
            
            $message = $client->messages->create($to, $messageData);
            Log::info('TwilioChat sent', [
                'to' => $to,
                'body_len' => strlen((string) $body),
                'media_count' => count($sanitizedMedia),
                'sid' => (string) ($message->sid ?? ''),
                'status' => (string) ($message->status ?? ''),
            ]);
            return response()->json(['success' => true, 'sid' => (string) ($message->sid ?? '')]);
        } catch (\Twilio\Exceptions\RestException $e) {
            $code = method_exists($e, 'getCode') ? $e->getCode() : 0;
            Log::error('TwilioChat send failed', [
                'to' => $to,
                'error' => $e->getMessage(),
                'code' => $code,
            ]);
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'code' => $code,
            ], 502);
        } catch (\Throwable $e) {
            Log::error('TwilioChat send failed (unexpected)', ['to' => $to, 'error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // POST /admin/twilio/chat/upload - Handle image upload
    public function upload(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:5120', // 5MB max
        ]);

        try {
            $file = $request->file('image');
            $filename = 'twilio_chat_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            // Save to the public disk (storage/app/public/twilio_chat)
            $path = $file->storeAs('public/twilio_chat', $filename);

            // Build a controller-served URL to avoid webserver symlink issues
            $url = url('/twilio/chat/media/' . $filename);

            $storageAbsolute = storage_path('app/public/twilio_chat/' . $filename);
            $publicDir = public_path('storage/twilio_chat');
            $publicAbsolute = $publicDir . DIRECTORY_SEPARATOR . $filename;
            if (!is_dir($publicDir)) @mkdir($publicDir, 0755, true);
            // If the target file doesn't exist at public path, copy it
            if (!file_exists($publicAbsolute) && file_exists($storageAbsolute)) {
                $copied = @copy($storageAbsolute, $publicAbsolute);
                Log::info('TwilioChat upload copy fallback', [
                    'src_exists' => file_exists($storageAbsolute),
                    'dst_exists_before' => file_exists($publicAbsolute),
                    'copied' => (bool) $copied,
                    'src' => $storageAbsolute,
                    'dst' => $publicAbsolute,
                ]);
            }

            // Deep diagnostics
            $link = public_path('storage');
            Log::info('TwilioChat upload diagnostics', [
                'original_name' => $file->getClientOriginalName(),
                'mime' => $file->getMimeType(),
                'size_bytes' => $file->getSize(),
                'stored_path' => $path,
                'storage_abs_exists' => file_exists($storageAbsolute),
                'public_dir_exists' => is_dir($publicDir),
                'public_file_exists' => file_exists($publicAbsolute),
                'public_storage_link_exists' => file_exists($link),
                'public_storage_is_link' => is_link($link),
                'url' => $url,
            ]);
            
            return response()->json([
                'success' => true, 
                'url' => $url,
                'filename' => $filename
            ]);
        } catch (\Throwable $e) {
            Log::error('TwilioChat upload failed', [
                'error' => $e->getMessage(),
                'trace' => app()->hasDebugModeEnabled() ? $e->getTraceAsString() : null,
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Upload failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    // Public: serve uploaded media reliably (Twilio needs direct HTTPS access)
    public function media(string $filename)
    {
        // Security: allow only plain filenames
        $safe = basename($filename);
        $path = 'twilio_chat/' . $safe;
        if (!Storage::disk('public')->exists($path)) {
            Log::warning('TwilioChat media not found', ['filename' => $safe, 'path' => $path]);
            abort(404);
        }

        $mime = Storage::disk('public')->mimeType($path) ?: 'application/octet-stream';
        $contents = Storage::disk('public')->get($path);
        return response($contents, 200)
            ->header('Content-Type', $mime)
            ->header('Cache-Control', 'public, max-age=604800'); // 7 days
    }
}


