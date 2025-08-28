<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('items')) {
            Schema::table('items', function (Blueprint $table) {
                if (!Schema::hasColumn('items', 'sort_order')) {
                    $table->unsignedInteger('sort_order')->nullable()->after('status');
                }
            });
        }

        if (Schema::hasTable('faqs')) {
            Schema::table('faqs', function (Blueprint $table) {
                if (!Schema::hasColumn('faqs', 'sort_order')) {
                    $table->unsignedInteger('sort_order')->nullable()->after('status');
                }
            });
        }

        if (Schema::hasTable('rentals')) {
            Schema::table('rentals', function (Blueprint $table) {
                if (!Schema::hasColumn('rentals', 'sort_order')) {
                    $table->unsignedInteger('sort_order')->nullable()->after('status');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('items')) {
            Schema::table('items', function (Blueprint $table) {
                if (Schema::hasColumn('items', 'sort_order')) {
                    $table->dropColumn('sort_order');
                }
            });
        }
        if (Schema::hasTable('faqs')) {
            Schema::table('faqs', function (Blueprint $table) {
                if (Schema::hasColumn('faqs', 'sort_order')) {
                    $table->dropColumn('sort_order');
                }
            });
        }
        if (Schema::hasTable('rentals')) {
            Schema::table('rentals', function (Blueprint $table) {
                if (Schema::hasColumn('rentals', 'sort_order')) {
                    $table->dropColumn('sort_order');
                }
            });
        }
    }
};


