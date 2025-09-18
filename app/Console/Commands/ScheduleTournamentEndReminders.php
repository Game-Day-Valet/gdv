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
    protected $description = 'Send reminder email and SMS before tournament start date and on start morning';

    public function handle(): int
    {
        $tomorrow = now()->addDay()->toDateString();
        $today = now()->toDateString();
        $hour = (int) now()->format('H');

        $countPreStart = 0;
        $countStartMorning = 0;

        // Pre-start (one day before start_date)
        $preStartTournaments = Tournament::whereDate('start_date', $tomorrow)->get();
        foreach ($preStartTournaments as $t) {
            $rentals = Rental::where('tournament_id', $t->id)->get();
            foreach ($rentals as $rental) {
                SendTournamentEndReminderJob::dispatch($rental)->onQueue('emails');
                $countPreStart++;
            }
        }

        // Start-day morning (today at ~8 AM). We run this check only during hour 8 to avoid duplicates.
        if ($hour === 8) {
            $startDayTournaments = Tournament::whereDate('start_date', $today)->get();
            foreach ($startDayTournaments as $t) {
                $rentals = Rental::where('tournament_id', $t->id)->get();
                foreach ($rentals as $rental) {
                    SendTournamentEndMorningReminderJob::dispatch($rental)->onQueue('emails');
                    $countStartMorning++;
                }
            }
        }

        $this->info("Queued {$countPreStart} pre-start reminders for start date {$tomorrow}; queued {$countStartMorning} start-morning reminders for {$today} (only during 08:00 hour)");
        return Command::SUCCESS;
    }
}


