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
    //cargo
    $schedule->command('tastevn:s3todo', [1, 1])
      ->withoutOverlapping()
      ->everyFiveSeconds()
      ->runInBackground();

    $schedule->command('tastevn:s3todo', [1, 2])
      ->withoutOverlapping()
      ->everyFiveSeconds()
      ->runInBackground();
    //deli
    $schedule->command('tastevn:s3todo', [1, 3])
      ->withoutOverlapping()
      ->everyFiveSeconds()
      ->runInBackground();

    $schedule->command('tastevn:s3todo', [1, 4])
      ->withoutOverlapping()
      ->everyFiveSeconds()
      ->runInBackground();
    //market
    $schedule->command('tastevn:s3todo', [1, 5])
      ->withoutOverlapping()
      ->everyFiveSeconds()
      ->runInBackground();
    //poison
    $schedule->command('tastevn:s3todo', [1, 6])
      ->withoutOverlapping()
      ->everyFiveSeconds()
      ->runInBackground();


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
