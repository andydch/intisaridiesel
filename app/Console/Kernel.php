<?php

namespace App\Console;

use App\Console\Commands\ReportUID;
use Illuminate\Console\Scheduling\Schedule;
use App\Console\Commands\BeginningBalanceJob;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        ReportUID::class,
        BeginningBalanceJob::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('GenReport:StockInventoryAccurationPerBranch')
        // ->lastDayOfMonth('16:00')
        ->monthlyOn(1, '18:05')
        ->withoutOverlapping()
        ->sendOutputTo(public_path("schedule.log"));

        $schedule->command('GenBalance:BeginningBalanceAmountPerMonth')
        ->lastDayOfMonth('23:45')
        ->withoutOverlapping()
        ->sendOutputTo(public_path("beginning-balance.log"));

        // $schedule->command('backup:clean')->daily()->at('21:30');
        // $schedule->command('backup:run --only-db')->daily()->at('22:00');
        // $schedule->command('optimize:clear')->daily()->at('22:30');

        // $schedule->command('GenReport:CashFlow')
        // ->dailyAt('18:00')
        // ->withoutOverlapping()
        // ->sendOutputTo(public_path("cash-flow.log"));

        // $schedule->command('DelData:Session')
        // ->dailyAt('18:00')
        // ->withoutOverlapping()
        // ->sendOutputTo(public_path("del-session.log"));
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
