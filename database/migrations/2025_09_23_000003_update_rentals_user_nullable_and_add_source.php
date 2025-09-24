<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rentals', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable()->change();
            if (!Schema::hasColumn('rentals', 'booking_source')) {
                $table->string('booking_source', 32)->nullable()->after('user_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('rentals', function (Blueprint $table) {
            if (Schema::hasColumn('rentals', 'booking_source')) {
                $table->dropColumn('booking_source');
            }
            // Note: making user_id NOT NULL in down() may fail if nulls exist; skipping strict revert for safety
        });
    }
};


