<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class CheckUsers extends Command
{
    protected $signature = 'check:users';
    protected $description = 'Check users and their roles';

    public function handle()
    {
        $this->info('Checking users and their roles...');

        User::all()->each(function($user) {
            $role = $user->getRoleNames()->first() ?? 'No role';
            $this->line("{$user->email} - {$role}");
        });

        $this->info('Done!');
    }
}
