<?php

use App\Console\Commands\RebroadcastUnassignedConversations;
use App\Console\Commands\ScheduleTournamentEndReminders;

return [

    /*
    |--------------------------------------------------------------------------
    | Commands
    |--------------------------------------------------------------------------
    |
    | Here you may define all of the commands for your application. We'll load
    | and run these commands when your application is executed.
    |
    */

    'commands' => [
        RebroadcastUnassignedConversations::class,
        ScheduleTournamentEndReminders::class,
    ],

];
