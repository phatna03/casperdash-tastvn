<?php

namespace App\Api;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
//lib
use App\Models\User;
use App\Models\SysBug;
use App\Models\SysSetting;

class SysCore
{
  public static function var_dump_break()
  {
    return '===========================================================================++++++++++++++++++++++++++++++++++++++++++++++++===========================================================================';
  }

  public static function local_img_url()
  {
    return "https://s3.ap-southeast-1.amazonaws.com/cargo.tastevietnam.asia/58-5b-69-19-ad-83/SENSOR/1/2024-07-08/19/SENSOR_2024-07-08-19-47-41-791_847.jpg";
  }

  public static function time_to_ms()
  {
    $ms = array_filter(explode('.', number_format(microtime(true), 4, '.', ',')));

    return count($ms) == 2 ? $ms[1] : '0000';
  }

  public static function str_trim_slash($text)
  {
//    '58-5b-69-19-ad-67/SENSOR/1';

    if (!empty($text)) {
      $text = ltrim($text, '/');
    }
    if (!empty($text)) {
      $text = rtrim($text, '/');
    }

    return $text;
  }

  public static function str_format_hour($hour)
  {
    if ((int)$hour < 10) {
      $hour = '0' . $hour;
    }

    return $hour;
  }

  public static function str_db_query($query)
  {
    return vsprintf(str_replace('?', '%s', $query->toSql()), collect($query->getBindings())->map(function ($binding) {
      $binding = addslashes($binding);
      return is_numeric($binding) ? $binding : "'{$binding}'";
    })->toArray());
  }

  public static function arr_date_range($dates = NULL)
  {
    $date_from = NULL;
    $date_to = NULL;

    $dates = array_filter(explode('-', $dates));

    if (count($dates) && !empty($dates[0])) {
      $arr = array_filter(explode('/', trim($dates[0])));

      $date_from = $arr[2] . '-' . $arr[1] . '-' . $arr[0];
    }

    if (count($dates) && !empty($dates[1])) {
      $arr = array_filter(explode('/', trim($dates[1])));

      $date_to = $arr[2] . '-' . $arr[1] . '-' . $arr[0];
    }

    return [
      'date_from' => $date_from,
      'date_to' => $date_to,
    ];
  }

  public static function arr_datetime_range($times = NULL)
  {
    $time_from = NULL;
    $time_to = NULL;

    $times = array_filter(explode('-', $times));

    if (count($times) && !empty($times[0])) {
      $date_from = trim(substr(trim($times[0]), 0, 10));
      $time_from = trim(substr(trim($times[0]), 10));
      $hour_from = trim(substr(trim($time_from), 0, 2));
      $minute_from = trim(substr(trim($time_from), 3, 2));

      $time_from = date('Y-m-d', strtotime(str_replace('/', '-', $date_from))) . ' ' . $hour_from . ':' . $minute_from . ':00';
    }

    if (count($times) && !empty($times[1])) {
      $date_to = trim(substr(trim($times[1]), 0, 10));
      $time_to = trim(substr(trim($times[1]), 10));
      $hour_to = trim(substr(trim($time_to), 0, 2));
      $minute_to = trim(substr(trim($time_to), 3, 2));

      $time_to = date('Y-m-d', strtotime(str_replace('/', '-', $date_to))) . ' ' . $hour_to . ':' . $minute_to . ':00';
    }

    return [
      'time_from' => $time_from,
      'time_to' => $time_to,
    ];
  }

  public static function arr_sort_by_id_quantity($arr)
  {
    $a1 = [];
    $a2 = [];

    $temps = [];
    $datas = [];

    foreach ($arr as $key => $val) {
      $temps[] = $val;

      $a1[$key] = $val['id'];
      $a2[$key] = $val['quantity'];
    }

    array_multisort($a1, SORT_ASC, $a2, SORT_DESC, $temps);

    if (count($temps)) {
      foreach ($temps as $temp) {
        $datas[$temp['id']] = $temp;
      }
    }

    return $datas;
  }

  public static function os_slash_file($path)
  {
    if (strtoupper(substr(PHP_OS, 0, 3)) == 'WIN') {
      $path = str_replace('/', '\\', $path);
    }

    return $path;
  }

  public static function file_url_existed($url)
  {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_NOBODY, true);
    curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode == 200) {
      return true;
    }

    return false;
  }

  public static function set_sys_setting($key, $val)
  {
    $row = SysSetting::where('key', $key)
      ->first();

    if (!$row) {
      $row = SysSetting::create([
        'key' => $key,
      ]);
    }

    $row->update([
      'value' => $val,
    ]);

    return $row;
  }

  public static function get_sys_setting($key)
  {
    $row = SysSetting::where('key', $key)
      ->first();

    return $row ? $row->value : NULL;
  }

  public static function log_sys_bug($pars = [])
  {
    if (count($pars)) {
      SysBug::create($pars);

      //dev
      $types = [
        'zalo_send_text_only', //ignore spam
        'zalo_ingredient_missing', 'zalo_photo_comment',
      ];
      if (!in_array($pars['type'], $types)) {

        $row = User::find(5);

        $message = $pars['message'];
        if (!empty($pars['line'])) {
          $message .= ' - ' . $pars['line'];
        }
        if (!empty($pars['file'])) {
          $message .= ' - ' . $pars['file'];
        }

        SysZalo::send_text_only($row, $message);
      }
    }
  }

  public static function log_sys_failed()
  {

  }
}
