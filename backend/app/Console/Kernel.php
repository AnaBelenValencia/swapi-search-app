<?php

namespace App\Console;

use App\Jobs\RecalculateStatsJob;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule): void
    {
        $schedule->job(new RecalculateStatsJob)->everyFiveMinutes();
    }
}