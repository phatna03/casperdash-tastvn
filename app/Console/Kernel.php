<?php

namespace App\Console;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
//lib


class Kernel extends ConsoleKernel
{
  protected $commands = [
    //custome
    'App\Console\Commands\PhotoGet',
    'App\Console\Commands\PhotoScan',
    'App\Console\Commands\PhotoCheck',
    'App\Console\Commands\PhotoSync',
    'App\Console\Commands\PhotoClear',
    'App\Console\Commands\PhotoNotify',

    'App\Console\Commands\ZaloToken',
  ];

  /**
   * Define the application's command schedule.
   */
  protected function schedule(Schedule $schedule): void
  {

    //every 1s
//    web:photo-get
    $schedule->command('web:photo-get', [1, 1])
      ->withoutOverlapping()
      ->everySecond()
      ->runInBackground();
    $schedule->command('web:photo-get', [1, 2])
      ->withoutOverlapping()
      ->everySecond()
      ->runInBackground();

    $schedule->command('web:photo-get', [1, 3])
      ->withoutOverlapping()
      ->everySecond()
      ->runInBackground();
    $schedule->command('web:photo-get', [1, 4])
      ->withoutOverlapping()
      ->everySecond()
      ->runInBackground();

    $schedule->command('web:photo-get', [1, 5])
      ->withoutOverlapping()
      ->everySecond()
      ->runInBackground();

    $schedule->command('web:photo-get', [1, 6])
      ->withoutOverlapping()
      ->everySecond()
      ->runInBackground();
    $schedule->command('web:photo-get', [1, 7])
      ->withoutOverlapping()
      ->everySecond()
      ->runInBackground();
    $schedule->command('web:photo-get', [1, 8])
      ->withoutOverlapping()
      ->everySecond()
      ->runInBackground();

    $schedule->command('web:photo-get', [1, 9])
      ->withoutOverlapping()
      ->everySecond()
      ->runInBackground();

//    $schedule->command('web:photo-get', [1, 10])
//      ->withoutOverlapping()
//      ->everySecond()
//      ->runInBackground();

//    for ($i=1; $i<=9; $i++) {
//      $schedule->command('web:photo-get', [1, $i])
//        ->withoutOverlapping()
//        ->everySecond()
//        ->runInBackground();
//    }

    //every 5s
//    web:photo-notify
    $schedule->command('web:photo-notify')
      ->withoutOverlapping()
      ->everyFiveSeconds()
      ->runInBackground();

    //every 1h
//    web:photo-check
    $schedule->command('web:photo-check')
      ->hourly()
      ->withoutOverlapping()
      ->runInBackground();

    //every 1h
//    kas:bill-check
    $schedule->command('kas:bill-check')
      ->hourly()
      ->withoutOverlapping()
      ->runInBackground();

    //daily at 1am
//    web:photo-sync
//    $schedule->command('web:photo-sync')
//      ->dailyAt('01:00')
//      ->withoutOverlapping()
//      ->runInBackground();

    //daily at 5am
//    web:photo-clear
//    $schedule->command('web:photo-clear')
//      ->dailyAt('05:00')
//      ->withoutOverlapping()
//      ->runInBackground();

    //daily at 6am
//    zalo:token-access
    $schedule->command('zalo:token-access')
      ->dailyAt('06:00')
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
