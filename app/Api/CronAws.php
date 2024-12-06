<?php

namespace App\Api;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
//lib
use App\Models\Restaurant;
use App\Models\RestaurantFoodScan;
use App\Models\RestaurantParent;
use App\Models\User;

class CronAws
{

  public static function photo_sync($pars = [])
  {

    $debug = isset($pars['debug']) ? (int)$pars['debug'] : 0;

    $sync_date = isset($pars['sync_date']) ? $pars['sync_date'] : date('Y-m-d');
    $sync_hour = isset($pars['sync_hour']) ? (int)$pars['sync_hour'] : (int)date('H');
    $last_hour = $sync_hour - 1;

    $s3_region = SysCore::get_sys_setting('s3_region');

    $file_log = 'public/logs/cronjob/sensors_photo_sync.log';

    $debug ? Storage::append($file_log, SysCore::var_dump_break()) : NULL;
    $debug ? Storage::append($file_log, '+ DATE= ' . $sync_date) : NULL;
    $debug ? Storage::append($file_log, '+ HOUR 1= ' . $sync_hour) : NULL;
    $debug ? Storage::append($file_log, '+ HOUR 2= ' . $last_hour) : NULL;
    $debug ? Storage::append($file_log, '+ S3 REGION= ' . $s3_region) : NULL;

    $debug ? Storage::append($file_log, '++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++') : NULL;

    $sensors = Restaurant::query()
      ->where('deleted', 0)
      ->where('restaurant_parent_id', '>', 0)
      ->where('s3_bucket_name', '<>', NULL)
      ->where('s3_bucket_address', '<>', NULL)
      ->orderBy('id', 'asc')
      ->get();
    if (count($sensors)) {
      $debug ? Storage::append($file_log, '+ TOTAL SENSORS= ' . count($sensors)) : NULL;

      foreach ($sensors as $sensor) {
        $restaurant = $sensor->get_parent();

        $s3_bucket = CronAws::restaurant_get_aws_s3_bucket($restaurant);
        $folder_directory = '/' . SysCore::str_trim_slash($sensor->s3_bucket_address) . '/' . $sync_date . '/' . $sync_hour . '/';

        $debug ? Storage::append($file_log, '++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++') : NULL;
        $debug ? Storage::append($file_log, '+++ SENSOR= ' . $sensor->id . ' - ' . $sensor->name) : NULL;
        $debug ? Storage::append($file_log, '-- S3 BUCKET= ' . $s3_bucket) : NULL;
        $debug ? Storage::append($file_log, '-- FOLDER= ' . $folder_directory) : NULL;

        if (empty($s3_bucket) || empty($s3_region)) {
          continue;
        }

        $disk_local = Storage::disk('sensors');
        $disk_s3_bucket = Storage::disk($s3_bucket);

        $files = $disk_local->allFiles($folder_directory);

        $debug ? Storage::append($file_log, '-- TOTAL FILES= ' . count($files)) : NULL;

        if (count($files)) {

          try {

            //photo sync
            foreach ($files as $file) {
              $debug ? Storage::append($file_log, '- FILE= ' . $file) : NULL;

              $rfs = RestaurantFoodScan::query()
                ->where('deleted', 0)
                ->where('restaurant_id', $sensor->id)

                ->where('photo_name', $file)

                ->whereDate('time_photo', $sync_date)

//                ->where('local_storage', 1)
                ->first();
              if (!$rfs || !$rfs->local_storage) {
                continue;
              }

              $status = $disk_s3_bucket->put($file, $disk_local->get($file));
              if ($status) {
                $img_url = "https://s3.{$s3_region}.amazonaws.com/{$sensor->s3_bucket_name}/{$file}";

                if (@getimagesize($img_url)) {

                  $rfs->update([
                    'local_storage' => 0,
                    'photo_url' => $img_url,
                  ]);
                }
              }

              $debug ? Storage::append($file_log, '- STATUS= ' . $status) : NULL;
            }

          } catch (\Exception $e) {

            SysCore::log_sys_bug([
              'type' => 'cronjob_photo_sync',
              'line' => $e->getLine(),
              'file' => $e->getFile(),
              'message' => $e->getMessage(),
              'params' => 'PARAMS_' . json_encode($pars) . '_BUG_'.  json_encode($e),
            ]);
          }
        }

        //-1 hour
        if (!$last_hour) {
          continue;
        }

        $folder_directory = '/' . SysCore::str_trim_slash($sensor->s3_bucket_address) . '/' . $sync_date . '/' . $last_hour . '/';

        $debug ? Storage::append($file_log, '++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++') : NULL;
        $debug ? Storage::append($file_log, '-- HOUR 2= ' . $last_hour) : NULL;
        $debug ? Storage::append($file_log, '-- FOLDER 2= ' . $folder_directory) : NULL;

        $files = $disk_local->allFiles($folder_directory);

        $debug ? Storage::append($file_log, '-- TOTAL FILES= ' . count($files)) : NULL;

        if (count($files)) {

          try {

            //photo sync
            foreach ($files as $file) {
              $debug ? Storage::append($file_log, '- FILE= ' . $file) : NULL;

              $rfs = RestaurantFoodScan::query()
                ->where('deleted', 0)
                ->where('restaurant_id', $sensor->id)

                ->where('photo_name', $file)

                ->whereDate('time_photo', $sync_date)

//                ->where('local_storage', 1)
                ->first();
              if (!$rfs || !$rfs->local_storage) {
                continue;
              }

              $status = $disk_s3_bucket->put($file, $disk_local->get($file));
              if ($status) {
                $img_url = "https://s3.{$s3_region}.amazonaws.com/{$sensor->s3_bucket_name}/{$file}";

                if (@getimagesize($img_url)) {

                  $rfs->update([
                    'local_storage' => 0,
                    'photo_url' => $img_url,
                  ]);
                }
              }

              $debug ? Storage::append($file_log, '- STATUS= ' . $status) : NULL;
            }

          } catch (\Exception $e) {

            SysCore::log_sys_bug([
              'type' => 'cronjob_photo_sync',
              'line' => $e->getLine(),
              'file' => $e->getFile(),
              'message' => $e->getMessage(),
              'params' => 'PARAMS_' . json_encode($pars) . '_BUG_'.  json_encode($e),
            ]);
          }
        }
      }
    }

    return count($sensors);
  }

  public static function restaurant_get_aws_s3_bucket(RestaurantParent $restaurant)
  {
    $s3_bucket = '';

    if ($restaurant) {
      switch ($restaurant->id) {
        case 1:
          $s3_bucket = 's3_bucket_cargo';
          break;

        case 3:
          $s3_bucket = 's3_bucket_market';
          break;

        case 4:
          $s3_bucket = 's3_bucket_poison';
          break;

        case 2:
        case 5:
        case 6:
        case 7:
        case 8:
        case 9:

          $s3_bucket = 's3_bucket_deli';
          break;
      }
    }

    return $s3_bucket;
  }

}
