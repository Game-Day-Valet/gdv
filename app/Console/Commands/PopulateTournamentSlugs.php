<?php

namespace App\Console\Commands;

use App\Models\Tournament;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class PopulateTournamentSlugs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tournaments:populate-slugs {--force : Re-generate all slugs even if they exist}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Populate missing slugs for ALL tournaments (including inactive and deleted)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $force = $this->option('force');
        
        // Use withTrashed() and withoutGlobalScopes() to catch EVERYTHING
        $query = Tournament::withTrashed()->withoutGlobalScopes();
        
        if (!$force) {
            $query->where(function ($q) {
                $q->whereNull('slug')->orWhere('slug', '');
            });
        }

        $tournaments = $query->get();

        if ($tournaments->isEmpty()) {
            $this->info('No tournaments found with missing slugs.');
            return;
        }

        $this->info('Found ' . $tournaments->count() . ' tournaments to process.');

        foreach ($tournaments as $tournament) {
            $name = $tournament->name ?: 'tournament';
            $slug = Str::slug($name);
            
            if (empty($slug)) {
                $slug = 'tournament-' . $tournament->id;
            }

            $originalSlug = $slug;
            $count = 1;

            // Check for uniqueness across ALL records
            while (Tournament::withTrashed()->withoutGlobalScopes()
                ->where('slug', $slug)
                ->where('id', '!=', $tournament->id)
                ->exists()
            ) {
                $slug = $originalSlug . '-' . ($tournament->id ?? $count++);
            }

            $tournament->slug = $slug;
            $tournament->save();
            
            $this->line("Updated: [{$tournament->id}] {$tournament->name} -> {$tournament->slug}");
        }

        $this->info('Slug population completed.');
    }
}
