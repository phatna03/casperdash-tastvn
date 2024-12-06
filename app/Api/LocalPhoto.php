<?php

namespace App\Api;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
//lib
use App\Models\Restaurant;

class LocalPhoto
{

  public static function photo_clear($pars = [])
  {
    $debug = isset($pars['debug']) ? (int)$pars['debug'] : 0;

    $config_days = isset($pars['days']) ? (int)$pars['days'] : 7;

    $file_log = 'public/logs/cronjob/sensors_photo_clear.log';

    $debug ? Storage::append($file_log, SysCore::var_dump_break()) : NULL;

    $clear_date = date('Y-m-d', strtotime("-{$config_days} days"));
    $clear_year = (int)date('Y', strtotime($clear_date));
    $clear_month = (int)date('m', strtotime($clear_date));

    $debug ? Storage::append($file_log, '+ DATE= ' . $clear_date) : NULL;
    $debug ? Storage::append($file_log, '+ YEAR= ' . $clear_year) : NULL;
    $debug ? Storage::append($file_log, '+ MONTH= ' . $clear_month) : NULL;

    $debug ? Storage::append($file_log, '++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++') : NULL;

    $sensors = Restaurant::query()
      ->where('deleted', 0)
      ->where('restaurant_parent_id', '>', 0)
      ->where('s3_bucket_name', '<>', NULL)
      ->where('s3_bucket_address', '<>', NULL)
      ->orderBy('id', 'asc')
//      ->limit(1) //
      ->get();
    if (count($sensors)) {
      $debug ? Storage::append($file_log, '+ TOTAL SENSORS= ' . count($sensors)) : NULL;

      foreach ($sensors as $sensor) {
        $restaurant = $sensor->get_parent();

        $s3_bucket = SysCore::str_trim_slash($sensor->s3_bucket_address);

        $debug ? Storage::append($file_log, '++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++') : NULL;
        $debug ? Storage::append($file_log, '+++ SENSOR= ' . $sensor->id . ' - ' . $sensor->name) : NULL;
        $debug ? Storage::append($file_log, '+++ S3 BUCKET ADDRESS= ' . $s3_bucket) : NULL;

        for ($day = 1; $day <= 31; $day++) {

          $check_date = $clear_year . '-' .
            LocalPhoto::number_format_to_2_string($clear_month) . '-' .
            LocalPhoto::number_format_to_2_string($day);

          if (strtotime($check_date) >= strtotime($clear_date)) {
            continue;
          }

          $debug ? Storage::append($file_log, '-- DATE= ' . $check_date) : NULL;

          //folder
          $folder_dir = public_path('sensors') . '/' . $s3_bucket . '/' . $check_date;
          $folder_dir = SysCore::os_slash_file($folder_dir);

          try {

            if (is_dir($folder_dir)) {

              $debug ? Storage::append($file_log, '-- FOLDER= ' . $folder_dir) : NULL;

              LocalPhoto::folder_remove($folder_dir);

              $debug ? Storage::append($file_log, '-- FOLDER= DELETED') : NULL;
            }

          } catch (\Exception $e) {

            SysCore::log_sys_bug([
              'type' => 'cronjob_photo_clear',
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

  public static function folder_remove($dir)
  {
    if (is_dir($dir)) {
      $objects = scandir($dir);

      foreach ($objects as $object) {
        if ($object != '.' && $object != '..') {
          if (filetype($dir . '/' . $object) == 'dir') {
            LocalPhoto::folder_remove($dir . '/' . $object);
          } else {
            unlink($dir . '/' . $object);
          }
        }
      }

      reset($objects);
      rmdir($dir);
    }
  }

  public static function number_format_to_2_string($number)
  {
    if ((int)$number < 10) {
      $number = '0' . $number;
    }

    return $number;
  }

}
