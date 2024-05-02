<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
  protected $commands = [
    //custome
    'App\Console\Commands\TodoAPI',
  ];

  /**
   * Define the application's command schedule.
   */
  protected function schedule(Schedule $schedule): void
  {
    //custome
    $limit = 1;
    $page = 7; //current go live restaurant

    for ($i = 1; $i <= $page; $i++) {
      $schedule->command('tastevn:s3todo', [$limit, $i])
        ->withoutOverlapping()
        ->everyTwoSeconds()
        ->runInBackground();
    }

  }

  /**
   * Register the commands for the application.
   */
  protected function commands(): void
  {
    $this->load(__DIR__ . '/Commands');

    require base_path('routes/console.php');
  }
}
