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
        Schema::table('tournaments', function (Blueprint $table) {
            $table->string('slug')->nullable()->after('name')->unique();
        });

        // Populate existing slugs
        $tournaments = \App\Models\Tournament::all();
        foreach ($tournaments as $tournament) {
            $slug = \Illuminate\Support\Str::slug($tournament->name);
            $originalSlug = $slug;
            $count = 1;

            while (\App\Models\Tournament::where('slug', $slug)->where('id', '!=', $tournament->id)->exists()) {
                $slug = $originalSlug . '-' . ($tournament->id ?? $count++);
            }

            $tournament->slug = $slug;
            $tournament->save();
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tournaments', function (Blueprint $table) {
            $table->dropColumn('slug');
        });
    }
};
