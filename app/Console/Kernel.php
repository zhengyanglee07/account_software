<?php

namespace App\Console;

use App\Jobs\AdAudiences\SyncGoogleSegmentAdAudiences;
use App\Jobs\ProcessTriggeredSteps;
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
        Commands\CheckEmailQuota::class,
        Commands\CalculateCreditBalance::class,
        Commands\CheckPromotionStatus::class,
    ];

    /**
     * Define the application's command schedule.
     * Note: remember to add cron in server
     *
     * @param \Illuminate\Console\Scheduling\Schedule $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule
            ->command('emails:send-standard')
            ->everyFiveMinutes()
            ->onOneServer()
            ->withoutOverlapping();

        $schedule
            ->job(new ProcessTriggeredSteps)
            ->everyFiveMinutes()
            ->onOneServer()
            ->withoutOverlapping();

        $schedule
            ->command('reset:emailquota')
            ->everyMinute()
            ->onOneServer();

        $schedule
            ->command('check:promotionstatus')
            ->everyMinute()
            ->onOneServer();


        $schedule
            ->command('calculate:creditBalance')
            ->daily();

        // check date based automation date daily
        $schedule
            ->command('auto:date-reached')
            ->daily()
            ->onOneServer();

        $schedule
            ->command('auto:abandoned-cart')
            ->hourly()
            ->onOneServer();

        $schedule
            ->command('auto:check-contact-segment')
            ->everyMinute()
            ->onOneServer();

        $schedule
            ->command('am:auto-approve-commission')
            ->daily()
            ->onOneServer();

        // $schedule
        //     ->command('send:signup-automation-email')
        //     ->hourly()
        //     ->withoutOverlapping()
        //     ->onOneServer();

        $schedule
            ->command('telescope:prune')
            ->daily();

        $schedule
            ->command('cache:prune-stale-tags')
            ->hourly();
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
