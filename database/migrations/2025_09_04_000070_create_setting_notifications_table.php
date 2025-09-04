<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('setting_notifications', function (Blueprint $table) {
            $table->id();
            $table->boolean('email_enabled')->default(true);
            $table->boolean('sms_enabled')->default(true);
            $table->boolean('fcm_enabled')->default(true);
            $table->timestamps();
        });

        // seed one row default enabled
        \DB::table('setting_notifications')->insert([
            'email_enabled' => true,
            'sms_enabled' => true,
            'fcm_enabled' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('setting_notifications');
    }
};


