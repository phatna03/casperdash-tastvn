<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

use App\Api\SysApp;
use App\Models\RestaurantFoodScan;

class SyncImagesToS3 extends Command
{
  protected $signature = 'sync:images-to-s3';

  protected $description = 'Sync photos to S3 bucket';

  protected $directories = [
    'cargo' => [
      'bucket' => 's3_bucket_cargo',
      'folder' => '/58-5b-69-19-ad-83/',
    ],
    'cargo' => [
      'bucket' => 's3_bucket_cargo',
      'folder' => '/58-5b-69-19-ad-67/',
    ],
    'deli' => [
      'bucket' => 's3_bucket_deli',
      'folder' => '/58-5b-69-19-ad-b6/',
    ],
    'deli' => [
      'bucket' => 's3_bucket_deli',
      'folder' => '/58-5b-69-20-11-7b/',
    ],
    'market' => [
      'bucket' => 's3_bucket_market',
      'folder' => '/58-5b-69-20-a8-f6/',
    ],
    'poison' => [
      'bucket' => 's3_bucket_poison',
      'folder' => '/58-5b-69-15-cd-2b/',
    ],
  ];

  public function __construct()
  {
    parent::__construct();
  }

  public function handle()
  {
    $sys_app = new SysApp();
    $s3_region = $sys_app->get_setting('s3_region');

    foreach ($this->directories as $restaurant => $directory) {

      $count = 0;
      $date = date('Y-m-d',strtotime("-1 days"));

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
                'local_storage' => 2,
                'photo_url' => $URL,
              ]);

              //remove local file

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
