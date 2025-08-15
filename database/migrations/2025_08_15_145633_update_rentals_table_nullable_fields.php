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
        Schema::table('rentals', function (Blueprint $table) {
            $table->string('team_name')->nullable()->change();
            $table->string('coach_name')->nullable()->change();
            $table->string('field_number')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rentals', function (Blueprint $table) {
            $table->string('team_name')->nullable(false)->change();
            $table->string('coach_name')->nullable(false)->change();
            $table->string('field_number')->nullable(false)->change();
        });
    }
};
