<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rentals', function (Blueprint $table) {
            if (Schema::hasColumn('rentals', 'team_name') && !Schema::hasColumn('rentals', 'team_name_with_age_group')) {
                $table->renameColumn('team_name', 'team_name_with_age_group');
            }
        });
    }

    public function down(): void
    {
        Schema::table('rentals', function (Blueprint $table) {
            if (Schema::hasColumn('rentals', 'team_name_with_age_group')) {
                $table->renameColumn('team_name_with_age_group', 'team_name');
            }
        });
    }
};


