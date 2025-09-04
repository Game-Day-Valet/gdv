<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Switch error_reason to TEXT to accommodate long transport messages without requiring doctrine/dbal
        DB::statement('ALTER TABLE `email_logs` MODIFY `error_reason` TEXT NULL');
    }

    public function down(): void
    {
        // Revert to VARCHAR(255)
        DB::statement('ALTER TABLE `email_logs` MODIFY `error_reason` VARCHAR(255) NULL');
    }
};


