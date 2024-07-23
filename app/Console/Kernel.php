<?php

namespace App\Console;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
//lib
use App\Models\Restaurant;

class Kernel extends ConsoleKernel
{
  protected $commands = [
    //custome
    'App\Console\Commands\SyncImagesToS3',
    'App\Console\Commands\ClearLocalImages',
    'App\Console\Commands\CheckPhotos',
    'App\Console\Commands\GetPhotos',
    'App\Console\Commands\ZaloToken',
  ];

  /**
   * Define the application's command schedule.
   */
  protected function schedule(Schedule $schedule): void
  {
    //every 15s
//    local:check-images
//    $sensors = Restaurant::where('deleted', 0)
//      ->where('restaurant_parent_id', '>', 0)
//      ->where('s3_bucket_name', '<>', NULL)
//      ->where('s3_bucket_address', '<>', NULL)
//      ->orderBy('id', 'asc')
//      ->get();
//    if (count($sensors)) {
//      for ($i = 1; $i <= count($sensors); $i++) {
//        $schedule->command('local:check-images', [1, $i])
//          ->withoutOverlapping()
//          ->everyFifteenSeconds()
//          ->runInBackground();
//      }
//    }

    $schedule->command('local:check-images', [1, 1])
      ->withoutOverlapping()
      ->everyTwoSeconds()
      ->runInBackground();
    $schedule->command('local:check-images', [1, 2])
      ->withoutOverlapping()
      ->everyTwoSeconds()
      ->runInBackground();

    $schedule->command('local:check-images', [1, 3])
      ->withoutOverlapping()
      ->everyMinute()
      ->runInBackground();
    $schedule->command('local:check-images', [1, 4])
      ->withoutOverlapping()
      ->everyTwoSeconds()
      ->runInBackground();

    $schedule->command('local:check-images', [1, 5])
      ->withoutOverlapping()
      ->everyTwoSeconds()
      ->runInBackground();
    $schedule->command('local:check-images', [1, 6])
      ->withoutOverlapping()
      ->everyTwoSeconds()
      ->runInBackground();
    $schedule->command('local:check-images', [1, 7])
      ->withoutOverlapping()
      ->everyTwoSeconds()
      ->runInBackground();
    $schedule->command('local:check-images', [1, 8])
      ->withoutOverlapping()
      ->everyTwoSeconds()
      ->runInBackground();

    //every 1h
//    local:check-status-images
    $schedule->command('local:check-status-images')
      ->hourly()
      ->withoutOverlapping()
      ->runInBackground();

    //every 2h
//    sync:images-to-s3
    $schedule->command('sync:images-to-s3')
      ->everyTwoHours()
      ->withoutOverlapping()
      ->runInBackground();

    //daily at 5am
//    local:clear-images
    $schedule->command('local:clear-images')
      ->dailyAt('05:00')
      ->withoutOverlapping()
      ->runInBackground();

    //daily at 6am
//    thirdparty:zalo-token-access
    $schedule->command('thirdparty:zalo-token-access')
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
