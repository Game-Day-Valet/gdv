<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Tournament;
use App\Models\Rental;
use App\Jobs\SendTournamentEndReminderJob;
use App\Jobs\SendTournamentEndMorningReminderJob;

class ScheduleTournamentEndReminders extends Command
{
    protected $signature = 'rentals:send-pre-end-reminders';
    protected $description = 'Send reminder email and SMS to users one day before tournament end date';

    public function handle(): int
    {
        $targetDate = now()->addDay()->toDateString();
        $tournaments = Tournament::whereDate('end_date', $targetDate)->get();
        $count = 0;
        foreach ($tournaments as $t) {
            $rentals = Rental::where('tournament_id', $t->id)->get();
            foreach ($rentals as $rental) {
                // One day before end
                SendTournamentEndReminderJob::dispatch($rental)->onQueue('emails');
                // Same-day morning reminder
                SendTournamentEndMorningReminderJob::dispatch($rental)->onQueue('emails');
                $count += 2;
            }
        }
        $this->info("Queued {$count} reminders (pre-end and morning) for tournaments ending on {$targetDate}");
        return Command::SUCCESS;
    }
}


