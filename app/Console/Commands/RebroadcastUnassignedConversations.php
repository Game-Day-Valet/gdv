<?php

namespace App\Console\Commands;

use App\Models\Conversation;
use Illuminate\Console\Scheduling\Schedule;
use App\Events\NewMessage;
use Illuminate\Console\Command;

class RebroadcastUnassignedConversations extends Command
{
    protected $signature = 'conversations:rebroadcast';
    protected $description = 'Rebroadcast unassigned conversations after timeout';

    public function handle()
    {

        $conversations = Conversation::whereNull('responder_id')
            ->where('created_at', '<', now()->subMinutes(5))
            ->with('messages')
            ->get();

        foreach ($conversations as $conversation) {
            $message = $conversation->messages()->latest()->first();
            if ($message) {
                event(new NewMessage($message));
            }
        }
    }

    public function schedule(Schedule $schedule): void
    {
        $schedule->command(static::class)->everyFiveMinutes();
    }
}
