<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Rental;
use App\Services\InvoiceService;

class TestInvoiceGeneration extends Command
{
    protected $signature = 'test:invoice {rental_id}';
    protected $description = 'Test invoice PDF generation for a rental';

    public function handle()
    {
        $rentalId = $this->argument('rental_id');
        
        try {
            $rental = Rental::with(['user', 'tournament', 'tournament.sport'])->findOrFail($rentalId);
            
            $this->info("Generating invoice for rental ID: {$rentalId}");
            
            $invoiceService = new InvoiceService();
            $invoicePath = $invoiceService->generateInvoice($rental);
            
            $this->info("Invoice generated successfully!");
            $this->info("File path: {$invoicePath}");
            $this->info("Public URL: " . $invoiceService->getInvoiceUrl($invoicePath));
            
        } catch (\Exception $e) {
            $this->error("Failed to generate invoice: " . $e->getMessage());
            return 1;
        }
        
        return 0;
    }
}
