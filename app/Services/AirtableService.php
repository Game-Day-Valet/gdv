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
    protected $customersTable;
    protected $logger;

    public function __construct()
    {
        $this->baseId = config('services.airtable.base_id');
        $this->token = config('services.airtable.token');
        $this->table = config('services.airtable.table');
        $this->customersTable = config('services.airtable.customers_table');
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

            // 1. Get or Create Customer in Airtable
            $customerRecordId = $this->findOrCreateAirtableCustomer($rental);

            // 2. Find Tournament ID in Airtable Tournament Tracker table
            $tournamentAirtableId = $this->findAirtableTournamentId(optional($rental->tournament)->name);

            $fields = [
                'Customer Name' => $rental->full_name ?? 'N/A',
                'Order Date' => $rental->created_at ? $rental->created_at->format('Y-m-d H:i:s') : now()->format('Y-m-d H:i:s'),
                'Complex' => optional($rental->tournament)->location ?? 'N/A',
                'Tournament' => optional($rental->tournament)->name ?? 'N/A',
                'Order Total' => (float) ($rental->total_amount ?? 0),
                'Subtotal' => (float) (($rental->total_amount ?? 0) - ($rental->tax_amount ?? 0)),
                'Order Status' => $rental->status ?? 'pending',
                'Delivery Method' => $rental->booking_source ?? 'website',
                'Tax' => (float) ($rental->tax_amount ?? 0),
                'Items Ordered' => $this->formatItemsAndBundles($rental),
                'Field Number' => $rental->field_number ?? 'N/A',
                'First Game Time' => optional($rental->tournament)->game_date ? \Carbon\Carbon::parse($rental->tournament->game_date)->format('Y-m-d') : null,
                'Email' => $rental->email ?? 'N/A',
                'Phone Number' => $rental->phone_number ?? 'N/A',

                // Fields from screenshots
                'Team Name' => $rental->team_name ?? 'N/A',
                'Age Group' => $rental->age_group ?? 'N/A',
                'Stripe Payment ID' => $rental->stripe_payment_id ?? 'N/A',
                'Coach Name' => $rental->coach_name ?? 'N/A',
            ];

            // If you still have a hidden "Customer" link field, you can add it here:
            if ($customerRecordId) {
                $fields['Customer'] = [$customerRecordId];
            }

            if (optional($rental->tournament)->game_time) {
                $baseDate = $rental->tournament->game_date ? \Carbon\Carbon::parse($rental->tournament->game_date)->format('Y-m-d') : now()->format('Y-m-d');
                $timeString = \Carbon\Carbon::parse($rental->tournament->game_time)->format('H:i:00');
                $fields['Game Time'] = "{$baseDate}T{$timeString}.000Z";
            }

            $this->log("Syncing rental #{$rental->id} to Airtable Web Orders", $fields);

            $existingRecordId = $rental->airtable_id;

            // Search for existing record if airtable_id is missing
            if (!$existingRecordId) {
                $formattedDate = $rental->created_at ? $rental->created_at->format('Y-m-d H:i') : null;
                if ($formattedDate) {
                    $filter = "AND({Customer Email} = '{$rental->email}', DATETIME_FORMAT({Order Date}, 'YYYY-MM-DD HH:mm') = '{$formattedDate}')";
                    $searchResponse = Http::withToken($this->token)
                        ->get("https://api.airtable.com/v0/{$this->baseId}/" . urlencode($this->table), [
                            'filterByFormula' => $filter
                        ]);

                    if ($searchResponse->successful() && !empty($searchResponse->json()['records'])) {
                        $existingRecordId = $searchResponse->json()['records'][0]['id'];
                        $rental->update(['airtable_id' => $existingRecordId]);
                    }
                }
            }

            if ($existingRecordId) {
                $response = Http::withToken($this->token)
                    ->patch("https://api.airtable.com/v0/{$this->baseId}/" . urlencode($this->table) . "/{$existingRecordId}", [
                        'fields' => $fields,
                        'typecast' => true
                    ]);
            } else {
                $response = Http::withToken($this->token)
                    ->post("https://api.airtable.com/v0/{$this->baseId}/" . urlencode($this->table), [
                        'fields' => $fields,
                        'typecast' => true
                    ]);

                if ($response->successful()) {
                    $newId = $response->json()['id'];
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
     * Map bundle to tier name (Basic/Pro/VIP).
     */
    protected function getBundleSelectedTier(Rental $rental)
    {
        if (empty($rental->bundles) || !is_array($rental->bundles)) {
            return 'N/A';
        }

        foreach ($rental->bundles as $bundle) {
            $bundleId = is_array($bundle) ? ($bundle['bundle_id'] ?? null) : $bundle;
            if (!$bundleId)
                continue;

            // Mapping based on provided bundle list
            // 10: Sideline Setup -> Basic
            // 12: Family Sideline Setup -> Pro
            // 11: Ultimate Sideline Setup -> VIP
            // Including "with Tent Sides" versions
            switch ((int) $bundleId) {
                case 10:
                    return 'Basic';
                case 12:
                case 17:
                    return 'Pro';
                case 11:
                case 15:
                    return 'VIP';
                case 13:
                case 18:
                    return 'Team'; // Team package
                case 14:
                    return 'Chairs';
            }
        }

        return 'N/A';
    }

    /**
     * Find or create a customer record in Airtable.
     */
    protected function findOrCreateAirtableCustomer(Rental $rental)
    {
        if (!$this->customersTable)
            return null;

        $email = $rental->email;
        if (!$email)
            return null;

        try {
            // Search by email
            $filter = "{Email} = '{$email}'";
            $response = Http::withToken($this->token)
                ->get("https://api.airtable.com/v0/{$this->baseId}/" . urlencode($this->customersTable), [
                    'filterByFormula' => $filter
                ]);

            if ($response->successful() && !empty($response->json()['records'])) {
                return $response->json()['records'][0]['id'];
            }

            // Create new customer if not found
            $createResponse = Http::withToken($this->token)
                ->post("https://api.airtable.com/v0/{$this->baseId}/" . urlencode($this->customersTable), [
                    'fields' => [
                        'Name' => $rental->full_name ?? 'N/A',
                        'Email' => $email,
                        'Phone' => $rental->phone_number ?? 'N/A',
                    ],
                    'typecast' => true
                ]);

            if ($createResponse->successful()) {
                return $createResponse->json()['id'];
            }
        } catch (\Throwable $e) {
            $this->log("Error in findOrCreateAirtableCustomer: " . $e->getMessage(), [], 'error');
        }

        return null;
    }

    /**
     * Find Tournament ID in Tournament Tracker table.
     */
    protected function findAirtableTournamentId($name)
    {
        if (!$name)
            return null;

        try {
            $filter = "{Name} = '" . addslashes($name) . "'";
            $response = Http::withToken($this->token)
                ->get("https://api.airtable.com/v0/{$this->baseId}/" . urlencode('Tournament Tracker'), [
                    'filterByFormula' => $filter
                ]);

            if ($response->successful() && !empty($response->json()['records'])) {
                return $response->json()['records'][0]['id'];
            }
        } catch (\Throwable $e) {
            $this->log("Error in findAirtableTournamentId: " . $e->getMessage(), [], 'error');
        }

        return null;
    }

    /**
     * Format items and bundles for Airtable.
     */
    protected function formatItemsAndBundles(Rental $rental)
    {
        $items = [];
        $tier = $this->getBundleSelectedTier($rental);
        if ($tier !== 'N/A') {
            $items[] = "Tier: {$tier}";
        }

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
     */
    public function syncTournamentGamesToAirtable(\App\Models\Tournament $tournament)
    {
        try {
            $rentals = Rental::where('tournament_id', $tournament->id)
                ->whereNotNull('airtable_id')
                ->get();

            if ($rentals->isEmpty())
                return;

            $fields = [
                'Game Date' => $tournament->game_date ? \Carbon\Carbon::parse($tournament->game_date)->format('Y-m-d') : null,
            ];

            if ($tournament->game_time) {
                $baseDate = $tournament->game_date ? \Carbon\Carbon::parse($tournament->game_date)->format('Y-m-d') : now()->format('Y-m-d');
                $timeString = \Carbon\Carbon::parse($tournament->game_time)->format('H:i:00');
                $fields['Game Time'] = "{$baseDate}T{$timeString}.000Z";
            }

            foreach ($rentals as $rental) {
                Http::withToken($this->token)
                    ->patch("https://api.airtable.com/v0/{$this->baseId}/" . urlencode($this->table) . "/{$rental->airtable_id}", [
                        'fields' => $fields,
                        'typecast' => true
                    ]);
            }
        } catch (\Throwable $e) {
            $this->log("Critical error syncing Game Date/Time: " . $e->getMessage(), [], 'error');
        }
    }

    public function log($message, $data = [], $level = 'info')
    {
        $logMessage = "[" . now() . "] {$level}: {$message} " . json_encode($data);
        Log::build([
                    'driver' => 'single',
                    'path' => storage_path('logs/airtable.log'),
                ])->{$level}($logMessage);
    }
}
