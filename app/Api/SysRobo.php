<?php

namespace App\Api;

use App\Api\SysApp;

class SysRobo
{
  public const _SCAN_CONFIDENCE = 30;
  public const _SCAN_OVERLAP = 60;

  public static function photo_scan($img_url, $pars = [])
  {
    $sys_app = new SysApp();

    //setting web
    $dataset = $sys_app->get_setting('rbf_dataset_scan');
    $api_key = $sys_app->get_setting('rbf_api_key');

    //pars
    $confidence = isset($pars['confidence']) ? (int)$pars['confidence'] : 50;
    $overlap = isset($pars['overlap']) ? (int)$pars['overlap'] : 50;
    $max_objects = isset($pars['max_objects']) ? (int)$pars['max_objects'] : 100;

    $status = true;
    $error = [];

    // URL for Http Request
    $api_url =  "https://detect.roboflow.com/" . $dataset
      . "?api_key=" . $api_key
      . "&confidence=" . $confidence
      . "&overlap=" . $overlap
      . "&max_objects=" . $max_objects
      . "&image=" . urlencode($img_url);

    // Setup + Send Http request
    $options = array(
      'http' => array (
        'header' => "Content-type: application/x-www-form-urlencoded\r\n",
        'method'  => 'POST'
      ));

    try {

      $context = stream_context_create($options);
      $result = file_get_contents($api_url, false, $context);
      if (!empty($result)) {
        $result = (array)json_decode($result);
      }

    } catch (\Exception $e) {

      $status = false;
      $error = [
        'line' => $e->getLine(),
        'file' => $e->getFile(),
        'message' => $e->getMessage(),
      ];
    }

    return [
      'status' => $status,
      'error' => $error,
      'pars' => $pars,

      'api_url' => $api_url,
      'img_url' => $img_url,
      'result' => $result,
    ];
  }

}
