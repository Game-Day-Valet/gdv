<?php

namespace App\Services;

use App\Models\Rental;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AirtableService
{
    protected $baseId;
    protected $token;
    protected $table;
    protected $logger;

    public function __construct()
    {
        $this->baseId = config('services.airtable.base_id');
        $this->token = config('services.airtable.token');
        $this->table = config('services.airtable.table');
    }

    /**
     * Map rental data to Airtable fields and sync.
     *
     * @param Rental $rental
     * @return void
     */
    public function updateOrInsertRental(Rental $rental)
    {
        try {
            $rental->loadMissing('tournament.sport');

            $fields = [
                'Customer' => [$rental->full_name ?? 'N/A'],
                'Order Date' => $rental->created_at ? $rental->created_at->format('Y-m-d H:i:s') : now()->format('Y-m-d H:i:s'),
                'Complex' => optional($rental->tournament)->location ?? 'N/A',
                'Tournament Name' => optional($rental->tournament)->name ?? 'N/A',
                'Order Total' => (float) ($rental->total_amount ?? 0),
                'Order Status' => $rental->status ?? 'pending',
                'Delivery Method' => $rental->booking_source ?? 'website',
                'Tournament Tracker' => (string) ($rental->tournament_id ?? ''),
                'Tax Amount' => (float) ($rental->tax_amount ?? 0),
                'Items + Quantities' => $this->formatItemsAndBundles($rental),
                'Payment Status' => $rental->payment_status ?? 'pending',
                'Field Number' => $rental->field_number ?? 'N/A',
                'Game Date' => optional($rental->tournament)->game_date ? \Carbon\Carbon::parse($rental->tournament->game_date)->format('Y-m-d') : null,
                'Customer Email' => $rental->email ?? 'N/A',
                'Customer Phone' => $rental->phone_number ?? 'N/A',
                'Sports' => optional($rental->tournament->sport)->name ?? 'N/A',
            ];

            if (optional($rental->tournament)->game_time) {
                $baseDate = $rental->tournament->game_date ? \Carbon\Carbon::parse($rental->tournament->game_date)->format('Y-m-d') : now()->format('Y-m-d');
                $timeString = \Carbon\Carbon::parse($rental->tournament->game_time)->format('H:i:00');
                $fields['Game Time'] = "{$baseDate}T{$timeString}.000Z";
            }

            $this->log("Syncing rental #{$rental->id} to Airtable", $fields);

            $existingRecordId = $rental->airtable_id;

            // 1. If we don't have an ID, try to search for one (fallback for existing records)
            if (!$existingRecordId) {
                $formattedDate = $rental->created_at ? $rental->created_at->format('Y-m-d H:i') : null;

                if ($formattedDate) {
                    $filter = "AND({Tournament Tracker} = '{$rental->tournament_id}', DATETIME_FORMAT({Order Date}, 'YYYY-MM-DD HH:mm') = '{$formattedDate}')";

                    $searchResponse = Http::withToken($this->token)
                        ->get("https://api.airtable.com/v0/{$this->baseId}/" . urlencode($this->table), [
                            'filterByFormula' => $filter
                        ]);

                    if ($searchResponse->successful() && !empty($searchResponse->json()['records'])) {
                        $existingRecordId = $searchResponse->json()['records'][0]['id'];
                        $this->log("Found existing record ID via search: {$existingRecordId} for rental #{$rental->id}");

                        // Save the ID to the rental for future use
                        $rental->update(['airtable_id' => $existingRecordId]);
                    }
                }
            }

            if ($existingRecordId) {
                // PATCH (Update existing)
                $response = Http::withToken($this->token)
                    ->patch("https://api.airtable.com/v0/{$this->baseId}/" . urlencode($this->table) . "/{$existingRecordId}", [
                        'fields' => $fields,
                        'typecast' => true
                    ]);

                // If PATCH fails because record was deleted, we might want to try POST, 
                // but let's keep it simple for now as per user request.
            } else {
                // POST (Create new)
                $response = Http::withToken($this->token)
                    ->post("https://api.airtable.com/v0/{$this->baseId}/" . urlencode($this->table), [
                        'fields' => $fields,
                        'typecast' => true
                    ]);

                if ($response->successful()) {
                    $newId = $response->json()['id'];
                    $this->log("Created new Airtable record with ID: {$newId} for rental #{$rental->id}");
                    $rental->update(['airtable_id' => $newId]);
                }
            }


            if ($response->successful()) {
                $this->log("Successfully synced rental #{$rental->id} to Airtable", $response->json());
            } else {
                $this->log("Failed to sync rental #{$rental->id} to Airtable", $response->json(), 'error');
            }
        } catch (\Throwable $e) {
            $this->log("Critical error syncing rental #{$rental->id} to Airtable: " . $e->getMessage(), [], 'error');
        }
    }

    /**
     * Format items and bundles for Airtable.
     *
     * @param Rental $rental
     * @return string
     */
    protected function formatItemsAndBundles(Rental $rental)
    {
        $items = [];
        if (is_array($rental->items)) {
            foreach ($rental->items as $item) {
                $itemModel = \App\Models\Item::find($item['item_id']);
                $name = $itemModel ? $itemModel->name : "Item #{$item['item_id']}";
                $items[] = "{$name} (x{$item['quantity']})";
            }
        }

        if (is_array($rental->bundles)) {
            foreach ($rental->bundles as $bundle) {
                $bundleId = is_array($bundle) ? ($bundle['bundle_id'] ?? null) : $bundle;
                $quantity = is_array($bundle) ? ($bundle['quantity'] ?? 1) : 1;
                $bundleModel = \App\Models\Bundle::find($bundleId);
                $name = $bundleModel ? $bundleModel->name : "Bundle #{$bundleId}";
                $items[] = "{$name} (x{$quantity})";
            }
        }

        return implode(', ', $items);
    }

    /**
     * Sync Game Date and Game Time for all rentals of a specific tournament.
     *
     * @param \App\Models\Tournament $tournament
     * @return void
     */
    public function syncTournamentGamesToAirtable(\App\Models\Tournament $tournament)
    {
        try {
            // Get all rentals for this tournament that ALREADY have an airtable_id
            $rentals = Rental::where('tournament_id', $tournament->id)
                ->whereNotNull('airtable_id')
                ->get();

            if ($rentals->isEmpty()) {
                $this->log("No rentals found with Airtable IDs for tournament #{$tournament->id} to perform batch Game Date/Time update.");
                return;
            }

            $fields = [
                'Game Date' => $tournament->game_date ? \Carbon\Carbon::parse($tournament->game_date)->format('Y-m-d') : null,
            ];

            // If Game Time is provided, add it formatted.
            // Airtable Time fields require a full ISO 8601 string if the field is configured as Date with Time enabled.
            if ($tournament->game_time) {
                $baseDate = $tournament->game_date ? \Carbon\Carbon::parse($tournament->game_date)->format('Y-m-d') : now()->format('Y-m-d');
                $timeString = \Carbon\Carbon::parse($tournament->game_time)->format('H:i:00');
                $fields['Game Time'] = "{$baseDate}T{$timeString}.000Z";
            } else {
                $fields['Game Time'] = null;
            }

            // Remove nulls so we don't accidentally clear fields that shouldn't be cleared,
            // EXCEPT if both game_date and game_time were intentionally set to null.
            if ($fields['Game Date'] === null) {
                unset($fields['Game Date']);
            }
            if ($fields['Game Time'] === null) {
                unset($fields['Game Time']);
            }

            $this->log("Syncing Game Date/Time for tournament #{$tournament->id} to Airtable", $fields);

            $successCount = 0;
            $failCount = 0;

            /** @var \App\Models\Rental $rental */
            foreach ($rentals as $rental) {
                $airtableId = $rental->airtable_id;

                $response = Http::withToken($this->token)
                    ->patch("https://api.airtable.com/v0/{$this->baseId}/" . urlencode($this->table) . "/{$airtableId}", [
                        'fields' => $fields,
                        'typecast' => true
                    ]);

                if ($response->successful()) {
                    $successCount++;
                } else {
                    $failCount++;
                    $this->log("Failed to sync Game Date/Time for rental #{$rental->id}", $response->json(), 'error');
                }
            }

            $this->log("Finished syncing Game Date/Time for tournament #{$tournament->id}. Success: {$successCount}, Failed: {$failCount}");

        } catch (\Throwable $e) {
            $this->log("Critical error syncing Game Date/Time for tournament #{$tournament->id}: " . $e->getMessage(), [], 'error');
        }
    }

    /**
     * Separate logging for Airtable.
     *
     * @param string $message
     * @param array $data
     * @param string $level
     * @return void
     */
    public function log($message, $data = [], $level = 'info')
    {
        $logMessage = "[" . now() . "] {$level}: {$message} " . json_encode($data);
        Log::build([
                    'driver' => 'single',
                    'path' => storage_path('logs/airtable.log'),
                ])->{$level}($logMessage);
    }
}
