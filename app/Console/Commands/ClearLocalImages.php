<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

use App\Api\SysApp;
use App\Api\SysRobo;
use App\Models\RestaurantFoodScan;

class ClearLocalImages extends Command
{
  protected $signature = 'local:clear-images';
  protected $description = 'Clear local photos';

  public function __construct()
  {
    parent::__construct();
  }

  public function handle()
  {
    $sys_app = new SysApp();
    $s3_region = $sys_app->get_setting('s3_region');

    $directories = SysRobo::s3_bucket_folder();
    foreach ($directories as $restaurant => $directory) {

      $count = 0;

      $file_log = 'public/logs/cron_clear_photos_' . $restaurant . '.log';
      Storage::append($file_log, '===================================================================================');

      $localDisk = Storage::disk('sensors');
      $s3Disk = Storage::disk($directory['bucket']);

      $date = date('Y-m-d', strtotime("-3 days"));
      $dir = "{$directory['folder']}SENSOR/1/{$date}/";

      $files = $localDisk->allFiles($dir);

      foreach ($files as $file) {

        Storage::append($file_log, 'FILE_CLEAR= ' . $file);

        $storagePath = public_path('sensors') . '/' . $file;
        if (is_file($storagePath)) {
          unlink($storagePath);
        }
      }

      Storage::append($file_log, 'TOTAL= ' . $count);
      Storage::append($file_log, '===================================================================================');
    }
  }
}
