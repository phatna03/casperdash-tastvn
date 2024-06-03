<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

use App\Api\SysApp;
use App\Api\SysRobo;
use App\Models\RestaurantFoodScan;

class SyncImagesToS3 extends Command
{
  protected $signature = 'sync:images-to-s3';
  protected $description = 'Sync photos to S3 bucket';

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

      $file_log = 'public/logs/cron_sync_s3_' . $restaurant . '.log';
      Storage::append($file_log, '===================================================================================');

      $localDisk = Storage::disk('sensors');
      $s3Disk = Storage::disk($directory['bucket']);

      $files = $localDisk->allFiles($directory['folder']);

      foreach ($files as $file) {

        $status = $s3Disk->put($file, $localDisk->get($file));
        if ($status) {

          $count++;

          $row = RestaurantFoodScan::where('photo_name', $file)
            ->first();
          if ($row) {

            $restaurant = $row->get_restaurant();
            $URL = "https://s3.{$s3_region}.amazonaws.com/{$restaurant->s3_bucket_name}/{$file}";

            if (@getimagesize($URL)) {

              $row->update([
                'local_storage' => 0,
                'photo_url' => $URL,
              ]);
            }
          }
        }

        Storage::append($file_log, 'FILE_SYNC_STATUS= ' . $status);
        Storage::append($file_log, 'FILE_SYNC_DATA= ' . $file);
      }

      Storage::append($file_log, 'TOTAL= ' . $count);
      Storage::append($file_log, '===================================================================================');
    }
  }
}
