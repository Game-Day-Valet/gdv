<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MetaPixelService
{
    private string $pixelId;
    private string $accessToken;
    private string $apiVersion = 'v19.0';

    public function __construct()
    {
        $this->pixelId = config('services.meta.pixel_id', '');
        $this->accessToken = config('services.meta.access_token', '');
    }

    public function trackPurchase(array $data): void
    {
        if (empty($this->pixelId) || empty($this->accessToken)) {
            return;
        }

        $payload = [
            'data' => [[
                'event_name'       => 'Purchase',
                'event_time'       => time(),
                'action_source'    => 'website',
                'event_source_url' => $data['url'] ?? '',
                'user_data'        => $this->buildUserData($data),
                'custom_data'      => [
                    'value'        => round((float) ($data['value'] ?? 0), 2),
                    'currency'     => 'USD',
                    'content_ids'  => [$data['order_id'] ?? ''],
                    'content_type' => 'product',
                    'content_name' => $data['content_name'] ?? 'Tournament Rental',
                ],
            ]],
        ];

        try {
            $response = Http::post(
                "https://graph.facebook.com/{$this->apiVersion}/{$this->pixelId}/events?access_token={$this->accessToken}",
                $payload
            );

            if (!$response->successful()) {
                Log::warning('Meta CAPI Purchase failed', ['body' => $response->body()]);
            }
        } catch (\Throwable $e) {
            Log::error('Meta CAPI exception', ['error' => $e->getMessage()]);
        }
    }

    private function buildUserData(array $data): array
    {
        $userData = ['client_ip_address' => $data['ip'] ?? ''];

        if (!empty($data['email'])) {
            $userData['em'] = [hash('sha256', strtolower(trim($data['email'])))];
        }

        if (!empty($data['phone'])) {
            $phone = preg_replace('/\D/', '', $data['phone']);
            $userData['ph'] = [hash('sha256', $phone)];
        }

        return $userData;
    }
}
