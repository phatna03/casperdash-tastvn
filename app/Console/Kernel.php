<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
  protected $commands = [
    //custome
    'App\Console\Commands\SyncImagesToS3',
    'App\Console\Commands\ClearLocalImages',
    'App\Console\Commands\GetPhotos',
  ];

  /**
   * Define the application's command schedule.
   */
  protected function schedule(Schedule $schedule): void
  {
    //custome
    //cargo
    $schedule->command('local:check-images', [1, 1])
      ->withoutOverlapping()
      ->everyMinute()
      ->runInBackground();

    $schedule->command('local:check-images', [1, 2])
      ->withoutOverlapping()
      ->everyFifteenSeconds()
      ->runInBackground();
    //deli
    $schedule->command('local:check-images', [1, 3])
      ->withoutOverlapping()
      ->everyFifteenSeconds()
      ->runInBackground();

    $schedule->command('local:check-images', [1, 4])
      ->withoutOverlapping()
      ->everyMinute()
      ->runInBackground();
    //market
    $schedule->command('local:check-images', [1, 5])
      ->withoutOverlapping()
      ->everyFifteenSeconds()
      ->runInBackground();
    //poison
    $schedule->command('local:check-images', [1, 6])
      ->withoutOverlapping()
      ->everyFifteenSeconds()
      ->runInBackground();

    //deli - morning glory lounge
    $schedule->command('local:check-images', [1, 7])
      ->withoutOverlapping()
      ->everyFiveSeconds()
      ->runInBackground();

    //sync photos
    $schedule->command('sync:images-to-s3')
      ->twiceDaily(1, 17)
      ->withoutOverlapping()
      ->runInBackground();

    //clear photos
    $schedule->command('local:clear-images')
      ->dailyAt('02:00')
      ->withoutOverlapping()
      ->runInBackground();

    //status photos
    $schedule->command('local:check-status-images')
      ->dailyAt('03:00')
      ->withoutOverlapping()
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
