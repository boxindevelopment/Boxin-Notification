<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        // '\App\Console\Commands\NotifReminderPaymentSchedule',
        // '\App\Console\Commands\ExpiredBox',
        '\App\Console\Commands\RejectedStatusPending',
        '\App\Console\Commands\RejectedReturn',
        '\App\Console\Commands\RejectedTerminate',
        '\App\Console\Commands\CronPickup',
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('Notif:ReminderPayment')->everyMinute();
        // $schedule->command('Notif:ExpiredBox')->everyMinute();
        $schedule->command('status:reject')->everyFiveMinutes()->timezone('Asia/Jakarta');
        $schedule->command('return:reject')->everyTenMinutes()->timezone('Asia/Jakarta');
        $schedule->command('terminate:reject')->everyFifteenMinutes()->timezone('Asia/Jakarta');
        $schedule->command('cron:pickup')->everyFifteenMinutes()->timezone('Asia/Jakarta');
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
