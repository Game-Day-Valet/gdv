<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class RetryQueueCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'queue:retry-all-command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Retry all failed queue jobs and log the activity';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $startTime = now()->format('Y-m-d H:i:s');
        Log::channel('queue_retry')->info("Command queue:retry-all started at: {$startTime}");
        
        $this->info("Retrying all failed jobs (Started at: {$startTime})...");

        try {
            // Run the built-in retry command
            Artisan::call('queue:retry', [
                'id' => 'all',
            ]);

            $output = Artisan::output();
            Log::channel('queue_retry')->info("Retry Output: " . trim($output));
            
            $this->info('Retry process finished.');
            $this->line($output);
        } catch (\Exception $e) {
            $errorMsg = "Error during retry: " . $e->getMessage();
            Log::channel('queue_retry')->error($errorMsg);
            $this->error($errorMsg);
        }
    }
}
