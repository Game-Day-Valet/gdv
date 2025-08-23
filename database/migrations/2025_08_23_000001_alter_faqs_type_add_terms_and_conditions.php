<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Extend enum values to include terms_and_conditions
        DB::statement("ALTER TABLE faqs MODIFY COLUMN type ENUM('faq','privacy_policy','terms_and_conditions') NOT NULL DEFAULT 'faq'");
    }

    public function down(): void
    {
        // Revert to original enum values
        DB::statement("ALTER TABLE faqs MODIFY COLUMN type ENUM('faq','privacy_policy') NOT NULL DEFAULT 'faq'");
    }
};


