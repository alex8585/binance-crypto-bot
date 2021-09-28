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
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('check_counts')->cron('55 * * * *');

        $schedule->command('get_candidates')->cron('00 * * * *');
        $schedule->command('check_candidates')->cron('* * * * *');
        //$schedule->command('get_statistics')->cron('* * * * *');


        //$schedule->command('get_tv_technical')->cron('05 * * * *');
        //$schedule->command('get_tv_statistics')->cron('* * * * *');


        $schedule->command('clear_old_statistics')->cron('30 04 */7 * *');
        $schedule->command('clear_old_order_books')->cron('30 05 */7 * *');

        //$schedule->command('get_24_change')->cron('00 * * * *');
        $schedule->command('get_green_count')->cron('*/1 * * * *');


        $schedule->command('update_balances')->cron('*/30 * * * *');


        // $schedule->command('update_balance_history')->cron('59 23 * * *');
        $schedule->command('update_balance_history')->cron('00 00 * * *');

        // $schedule->command('inspire')
        //     ->everyMinute()
        //     ->appendOutputTo(storage_path('logs/inspire.log'));

        //$schedule->command('tickers --s --vv --telegramm')->cron('*/5 * * * *');
        //$schedule->command('graphs')->cron('7,12,22,32,42,52 * * * *');
        //$schedule->command('notif')->everyMinute();
        //$schedule->command('purge')->cron('* * */3 * *');
        //$schedule->command('mqtt_ticker')->cron('* * * * *');
        // $schedule->command('kuna_stop')->cron('* * * * *');
        //$schedule->command('tickers --s')->cron('*/5 * * * *')->emailOutputTo('blyakher85@gmail.com');
        //$schedule->command('graphs')->cron('7,12,22,32,42,52 * * * *')->emailOutputTo('blyakher85@gmail.com');
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
