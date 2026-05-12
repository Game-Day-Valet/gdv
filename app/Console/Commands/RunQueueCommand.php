<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Queue\Events\JobFailed;

class RunQueueCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'queue:run';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run the queue worker until empty and log success/failure separately';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $startTime = now()->format('Y-m-d H:i:s');
        Log::channel('queue_run')->info("Command queue:run started at: {$startTime}");
        
        $this->info("Running queue:work --stop-when-empty (Started at: {$startTime})...");

        // We use Artisan::call to run the work in the same process.
        // The listeners in AppServiceProvider will capture the events.
        Artisan::call('queue:work', [
            '--stop-when-empty' => true,
        ]);

        $this->info('Queue worker finished.');
    }
}
