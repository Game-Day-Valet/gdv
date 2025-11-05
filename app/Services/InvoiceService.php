<?php

namespace App\Services;

use App\Models\Rental;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;

class InvoiceService
{
    /**
     * Generate PDF invoice for a rental booking
     */
    public function generateInvoice(Rental $rental): string
    {
        try {
            // Load relationships
            $rental->load(['user', 'tournament', 'tournament.sport', 'tournament.items', 'tournament.bundles']);

            // --- START: UPDATED PRICE LOGIC ---

            // Build name and price maps for items and bundles
            $itemNames = [];
            $itemPrices = []; // Yeh prices ab tournament se ayengi
            $bundleNames = [];
            $bundlePrices = []; // Yeh prices ab tournament se ayengi

            // Get tournament-specific prices FIRST
            $tournamentItemPrices = [];
            foreach (($rental->tournament->items ?? []) as $it) {
                // Use tournament-specific price (from pivot) if it exists, otherwise fall back to item's base price
                $tournamentItemPrices[$it->id] = (float) ($it->pivot?->price ?? $it->price ?? 0);
            }
            $tournamentBundlePrices = [];
            foreach (($rental->tournament->bundles ?? []) as $bd) {
                // Use tournament-specific price (from pivot) if it exists, otherwise fall back to bundle's base price
                $tournamentBundlePrices[$bd->id] = (float) ($bd->pivot?->price ?? $bd->price ?? 0);
            }

            // Get item names and prices
            if (!empty($rental->items) && is_array($rental->items)) {
                $itemIds = collect($rental->items)->pluck('item_id')->filter()->unique()->values()->all();
                if (!empty($itemIds)) {
                    $items = \App\Models\Item::whereIn('id', $itemIds)->get();
                    $itemNames = $items->pluck('name', 'id')->toArray();
                    
                    // Map the correct prices
                    foreach($itemIds as $id) {
                        // Use tournament price if available, otherwise fall back to item's default price
                        $itemPrices[$id] = $tournamentItemPrices[$id] ?? $items->find($id)->price ?? 0;
                    }
                }
            }

            // Get bundle names and prices
            if (!empty($rental->bundles) && is_array($rental->bundles)) {
                $bundleIds = [];
                foreach ($rental->bundles as $bundle) {
                    if (is_array($bundle) && isset($bundle['bundle_id'])) {
                        $bundleIds[] = $bundle['bundle_id'];
                    } elseif (is_numeric($bundle)) {
                        $bundleIds[] = $bundle;
                    }
                }
                $bundleIds = array_values(array_unique($bundleIds));
                
                if (!empty($bundleIds)) {
                    $bundles = \App\Models\Bundle::whereIn('id', $bundleIds)->get();
                    $bundleNames = $bundles->pluck('name', 'id')->toArray();

                    // Map the correct prices
                    foreach($bundleIds as $id) {
                        // Use tournament price if available, otherwise fall back to bundle's default price
                        $bundlePrices[$id] = $tournamentBundlePrices[$id] ?? $bundles->find($id)->price ?? 0;
                    }
                }
            }
            // --- END: UPDATED PRICE LOGIC ---


            // Prepare data for PDF
            $data = [
                'rental' => $rental,
                'user' => $rental->user,
                'tournament' => $rental->tournament,
                'sport' => $rental->tournament->sport ?? null,
                'itemNames' => $itemNames,
                'itemPrices' => $itemPrices,
                'bundleNames' => $bundleNames,
                'bundlePrices' => $bundlePrices,
            ];

            // Generate PDF
            $pdf = Pdf::loadView('invoices.rental-invoice', $data);
            $pdf->setPaper('A4', 'portrait');
            $pdf->getDomPDF()->set_option('isRemoteEnabled', true);
            $pdf->getDomPDF()->set_option('isHtml5ParserEnabled', true);
            
            // Generate filename
            $filename = 'invoice-gdv-' . str_pad($rental->id, 6, '0', STR_PAD_LEFT) . '.pdf';
            
            // Save to storage
            $storagePath = storage_path('app/public/invoices');
            if (!file_exists($storagePath)) {
                mkdir($storagePath, 0755, true);
            }
            
            $filePath = $storagePath . '/' . $filename;
            $pdf->save($filePath);

            return $filePath;

        } catch (\Exception $e) {
            Log::error('Failed to generate invoice PDF', [
                'rental_id' => $rental->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    /**
     * Get the public URL for an invoice file
     */
    public function getInvoiceUrl(string $filePath): string
    {
        $filename = basename($filePath);
        return asset('storage/invoices/' . $filename);
    }

    /**
     * Clean up old invoice files (optional cleanup method)
     */
    public function cleanupOldInvoices(int $daysOld = 30): int
    {
        $storagePath = storage_path('app/public/invoices');
        $cutoffTime = now()->subDays($daysOld)->timestamp;
        $deletedCount = 0;

        if (is_dir($storagePath)) {
            $files = glob($storagePath . '/*.pdf');
            foreach ($files as $file) {
                if (filemtime($file) < $cutoffTime) {
                    if (unlink($file)) {
                        $deletedCount++;
                    }
                }
            }
        }

        Log::info('Cleaned up old invoice files', [
            'deleted_count' => $deletedCount,
            'days_old' => $daysOld
        ]);

        return $deletedCount;
    }
}