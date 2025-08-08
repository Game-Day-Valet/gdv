<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('rental_status_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rental_id')->constrained('rentals')->onDelete('cascade');
            $table->string('image_path')->nullable(); // Store the path to the uploaded image
            $table->string('status'); // pending, delivered, picked_up, returned
            $table->text('notes')->nullable(); // Optional notes about the status change
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null'); // Who made the change
            $table->timestamps();

            // Index for better performance
            $table->index(['rental_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rental_status_logs');
    }
};
