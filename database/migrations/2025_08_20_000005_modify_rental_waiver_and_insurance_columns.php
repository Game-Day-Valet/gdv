<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // Clean existing non-numeric values to NULL before altering types
        // insurance_option historically had values like '3', '7', 'none', 'custom'
        DB::statement("UPDATE rentals SET insurance_option = NULL WHERE insurance_option IS NOT NULL AND insurance_option NOT REGEXP '^[0-9]+(\\.[0-9]+)?$'");
        // damage_waiver historically was boolean; leave 0/1 as-is, null anything non-numeric
        DB::statement("UPDATE rentals SET damage_waiver = NULL WHERE damage_waiver IS NOT NULL AND damage_waiver NOT REGEXP '^[0-9]+(\\.[0-9]+)?$'");

        DB::statement("ALTER TABLE rentals MODIFY COLUMN insurance_option DECIMAL(10,2) NULL");
        DB::statement("ALTER TABLE rentals MODIFY COLUMN damage_waiver DECIMAL(10,2) NULL");
    }

    public function down(): void
    {
        // Best-effort rollback to previous types
        DB::statement("ALTER TABLE rentals MODIFY COLUMN insurance_option VARCHAR(255) NULL");
        DB::statement("ALTER TABLE rentals MODIFY COLUMN damage_waiver TINYINT(1) NULL");
    }
}; 