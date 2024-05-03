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
//    $limit = 1;
//    $page = 6; //current go live restaurant
//
//    for ($i = 1; $i <= $page; $i++) {
//      $schedule->command('tastevn:s3todo', [$limit, $i])
//        ->withoutOverlapping()
////        ->everyTwoSeconds()
//        ->everyFiveSeconds()
//        ->runInBackground();
//    }

    //v2
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
      ->everyTenSeconds()
      ->runInBackground();

    $schedule->command('tastevn:s3todo', [1, 4])
      ->withoutOverlapping()
      ->everyTenSeconds()
      ->runInBackground();
    //market
    $schedule->command('tastevn:s3todo', [1, 5])
      ->withoutOverlapping()
      ->everyTenSeconds()
      ->runInBackground();
    //poison
    $schedule->command('tastevn:s3todo', [1, 6])
      ->withoutOverlapping()
      ->everyTenSeconds()
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
