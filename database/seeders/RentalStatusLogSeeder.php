<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Rental;
use App\Models\RentalStatusLog;

class RentalStatusLogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all existing rentals
        $rentals = Rental::all();

        foreach ($rentals as $rental) {
            // Create initial status log for each rental
            RentalStatusLog::create([
                'rental_id' => $rental->id,
                'status' => $rental->status ?? 'pending',
                'notes' => 'Initial status',
                'updated_by' => 1, // Assuming admin user ID is 1
                'created_at' => $rental->created_at,
                'updated_at' => $rental->created_at,
            ]);
        }
    }
}
