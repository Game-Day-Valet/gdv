<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('sports') && !Schema::hasColumn('sports', 'sort_order')) {
            Schema::table('sports', function (Blueprint $table) {
                $table->unsignedInteger('sort_order')->nullable()->after('status');
            });
        }
        if (Schema::hasTable('rentals') && !Schema::hasColumn('rentals', 'sort_order')) {
            Schema::table('rentals', function (Blueprint $table) {
                $table->unsignedInteger('sort_order')->nullable()->after('status');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('sports') && Schema::hasColumn('sports', 'sort_order')) {
            Schema::table('sports', function (Blueprint $table) {
                $table->dropColumn('sort_order');
            });
        }
        if (Schema::hasTable('rentals') && Schema::hasColumn('rentals', 'sort_order')) {
            Schema::table('rentals', function (Blueprint $table) {
                $table->dropColumn('sort_order');
            });
        }
    }
};


