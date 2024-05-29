<?php

namespace App\Api;


use App\Models\SysSetting;

class SysApp
{

  public static function get_setting($key)
  {
    $row = SysSetting::where('key', $key)
      ->first();

    return $row ? $row->value : NULL;
  }

  public static function parse_s3_bucket_address($text)
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



}
