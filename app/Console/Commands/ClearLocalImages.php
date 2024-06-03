<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

use App\Api\SysApp;
use App\Models\RestaurantFoodScan;

class ClearLocalImages extends Command
{
  protected $signature = 'local:clear-images';

  protected $description = 'Clear local photos';

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
