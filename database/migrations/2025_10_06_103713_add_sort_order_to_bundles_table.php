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
        if (Schema::hasTable('bundles')) {
            Schema::table('bundles', function (Blueprint $table) {
                if (!Schema::hasColumn('bundles', 'sort_order')) {
                    $table->unsignedInteger('sort_order')->nullable()->after('status');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('bundles')) {
            Schema::table('bundles', function (Blueprint $table) {
                if (Schema::hasColumn('bundles', 'sort_order')) {
                    $table->dropColumn('sort_order');
                }
            });
        }
    }
};
