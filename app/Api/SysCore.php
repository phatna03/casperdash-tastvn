<?php

namespace App\Api;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
//lib
use App\Models\SysBug;
use App\Models\SysSetting;

class SysCore
{
  public static function var_dump_break()
  {
    return '===========================================================================++++++++++++++++++++++++++++++++++++++++++++++++===========================================================================';
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

  public static function os_slash_file($path)
  {
    if (strtoupper(substr(PHP_OS, 0, 3)) == 'WIN') {
      $path = str_replace('/', '\\', $path);
    }

    return $path;
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
    }
  }


}
