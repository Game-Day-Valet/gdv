<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rentals', function (Blueprint $table) {
            $table->unsignedTinyInteger('booking_days')->nullable()->after('rental_date');
        });
    }

    public function down(): void
    {
        Schema::table('rentals', function (Blueprint $table) {
            $table->dropColumn('booking_days');
        });
    }
};


