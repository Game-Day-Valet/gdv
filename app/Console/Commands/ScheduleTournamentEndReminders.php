<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Tournament;
use App\Models\Rental;
use App\Jobs\SendTournamentEndReminderJob;
use App\Jobs\SendTournamentEndMorningReminderJob;
use Illuminate\Support\Facades\Log;

class ScheduleTournamentEndReminders extends Command
{
    protected $signature = 'rentals:send-pre-end-reminders';
    protected $description = 'Send reminder email and SMS before tournament start date and on start morning';

    public function handle(): int
    {
        // Custom log channel/file
        $logFile = storage_path('logs/tournament_reminders.log');

        $timestamp = now()->format('Y-m-d H:i:s');
        $tomorrow = now()->addDay()->toDateString();
        $today = now()->toDateString();
        $hour = (int) now()->format('H');

        $countPreStart = 0;
        $countStartMorning = 0;

        // Start log
        file_put_contents($logFile, "\n=============================\n", FILE_APPEND);
        file_put_contents($logFile, "Cron started at: {$timestamp}\n", FILE_APPEND);

        // ----------------------------
        // Pre-start reminders
        // ----------------------------
        $preStartTournaments = Tournament::whereDate('start_date', $tomorrow)->get();
        file_put_contents($logFile, "Checking tournaments for pre-start reminders (start_date = {$tomorrow})...\n", FILE_APPEND);

        foreach ($preStartTournaments as $t) {
            file_put_contents($logFile, "  Tournament #{$t->id} ({$t->name})\n", FILE_APPEND);
            $rentals = Rental::where('tournament_id', $t->id)->get();
            foreach ($rentals as $rental) {
                SendTournamentEndReminderJob::dispatch($rental)->onQueue('emails');
                $countPreStart++;
                file_put_contents($logFile, "    -> Pre-start reminder queued for Rental #{$rental->id}\n", FILE_APPEND);
            }
        }

        // ----------------------------
        // Start-morning reminders (only run around 8 AM)
        // ----------------------------
        if ($hour === 8) {
            file_put_contents($logFile, "Checking tournaments for morning reminders (start_date = {$today})...\n", FILE_APPEND);

            $startDayTournaments = Tournament::whereDate('start_date', $today)->get();
            foreach ($startDayTournaments as $t) {
                file_put_contents($logFile, "  Tournament #{$t->id} ({$t->name})\n", FILE_APPEND);
                $rentals = Rental::where('tournament_id', $t->id)->get();
                foreach ($rentals as $rental) {
                    SendTournamentEndMorningReminderJob::dispatch($rental)->onQueue('emails');
                    $countStartMorning++;
                    file_put_contents($logFile, "    -> Morning reminder queued for Rental #{$rental->id}\n", FILE_APPEND);
                }
            }
        } else {
            file_put_contents($logFile, "Skipped morning reminders (current hour = {$hour})\n", FILE_APPEND);
        }

        // ----------------------------
        // Summary
        // ----------------------------
        $summary = "Summary: Pre-start={$countPreStart}, Morning={$countStartMorning}\n";
        file_put_contents($logFile, $summary, FILE_APPEND);
        file_put_contents($logFile, "Cron finished at: " . now()->format('Y-m-d H:i:s') . "\n", FILE_APPEND);
        file_put_contents($logFile, "=============================\n", FILE_APPEND);

        $this->info($summary);

        return Command::SUCCESS;
    }
}
