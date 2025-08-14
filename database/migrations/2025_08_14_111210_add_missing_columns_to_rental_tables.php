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
        // Add new fields to rentals table
        Schema::table('rentals', function (Blueprint $table) {
            $table->dateTime('estimated_delivery_time')->nullable()->after('status');
            $table->unsignedBigInteger('assigned_manager_id')->nullable()->after('estimated_delivery_time');
            $table->foreign('assigned_manager_id')->references('id')->on('users')->onDelete('set null');
        });

        // Add new fields to rental_status_logs table
        Schema::table('rental_status_logs', function (Blueprint $table) {
            $table->json('image_paths')->nullable()->after('notes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove fields from rentals table
        Schema::table('rentals', function (Blueprint $table) {
            $table->dropForeign(['assigned_manager_id']);
            $table->dropColumn(['estimated_delivery_time', 'assigned_manager_id']);
        });

        // Remove fields from rental_status_logs table
        Schema::table('rental_status_logs', function (Blueprint $table) {
            $table->dropColumn(['image_paths']);
        });
    }
};
