<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('fx:fetch')
                 ->everyThreeHours()
                 ->weekdays();
        $schedule->command('app:aggregate-daily-fx-rates')
                 ->dailyAt('00:01')
                 ->when(function () {
                    return now()->dayOfWeek !== 0;
                 });
        $schedule->command('valuelogs:clear')
                 ->dailyAt('00:02')
                 ->when(function () {
                    return now()->dayOfWeek !== 0;
                 });
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
