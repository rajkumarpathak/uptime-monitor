<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected $commands = [
        Commands\CheckWebsiteStatus::class,
    ];

    protected function schedule(Schedule $schedule): void
    {
        // Run website checks every 15 minutes
        $schedule->command('monitor:check-websites')
            ->everyFifteenMinutes()
            ->withoutOverlapping()
            ->runInBackground()
            ->appendOutputTo(storage_path('logs/website-monitor.log'));

        // Clear old logs daily
        $schedule->command('queue:prune-batches')->daily();
    }

    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');
        require base_path('routes/console.php');
    }
}