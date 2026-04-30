<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('booking_options', function (Blueprint $table) {
            $table->text('testimonial_quote')->nullable()->after('description');
            $table->string('testimonial_author')->nullable()->after('testimonial_quote');
            $table->string('support_phone_number')->nullable()->after('testimonial_author');
        });
    }

    public function down(): void
    {
        Schema::table('booking_options', function (Blueprint $table) {
            $table->dropColumn(['testimonial_quote', 'testimonial_author', 'support_phone_number']);
        });
    }
};
