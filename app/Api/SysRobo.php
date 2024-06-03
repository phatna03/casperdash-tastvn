<?php

namespace App\Api;
use Illuminate\Support\Facades\Storage;
//lib
use App\Api\SysApp;
use App\Models\Restaurant;
use App\Models\RestaurantFoodScan;

class SysRobo
{
  public const _SCAN_CONFIDENCE = 30;
  public const _SCAN_OVERLAP = 60;

  public static function s3_bucket_folder()
  {
    return [
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
  }

  public static function photo_scan($img_url, $pars = [])
  {
    $sys_app = new SysApp();

    //setting web
    $dataset = $sys_app->parse_s3_bucket_address($sys_app->get_setting('rbf_dataset_scan'));
    $version = $sys_app->get_setting('rbf_dataset_ver');
    $api_key = $sys_app->get_setting('rbf_api_key');

    //pars
    $confidence = isset($pars['confidence']) ? (int)$pars['confidence'] : 50;
    $overlap = isset($pars['overlap']) ? (int)$pars['overlap'] : 50;
    $max_objects = isset($pars['max_objects']) ? (int)$pars['max_objects'] : 100;

    $status = true;
    $error = [];

    // URL for Http Request
    $api_url =  "https://detect.roboflow.com/" . $dataset . "/" . $version
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

  public static function photo_get($pars = [])
  {
    $sys_app = new SysApp();

    $limit = isset($pars['limit']) ? (int)$pars['limit'] : 1;
    $page = isset($pars['page']) ? (int)$pars['page'] : 1;

    //run
    $restaurant = Restaurant::where('deleted', 0)
      ->where('s3_bucket_name', '<>', NULL)
      ->where('s3_bucket_address', '<>', NULL)
      ->where('s3_checking', 0)
      ->orderBy('rbf_scan', 'desc')
      ->orderBy('id', 'asc')
      ->paginate($limit, ['*'], 'page', $page)
      ->first();

    if (!$restaurant) {
      return false;
    }

    $cur_date = date('Y-m-d');
    $cur_hour = (int)date('H');
    //old
    if (isset($pars['hour']) && !empty($pars['hour'])) {
      $cur_hour = (int)$pars['hour'];
    }

    $row = NULL;

    $folder_setting = $sys_app->parse_s3_bucket_address($restaurant->s3_bucket_address);
    $directory = $folder_setting . '/' . $cur_date . '/' . $cur_hour . '/';

    $files = Storage::disk('sensors')->files($directory);
    if (count($files)) {
      //desc
//      $files = array_reverse($files);

      //step 1= photo check
      foreach ($files as $file) {
        $ext = array_filter(explode('.', $file));
        if (!count($ext) || $ext[count($ext) - 1] != 'jpg') {
          continue;
        }

        //check exist
        $row = RestaurantFoodScan::where('restaurant_id', $restaurant->id)
          ->where('photo_name', $file)
          ->first();
        if (!$row) {

          //step 1= photo get
          $row = $restaurant->photo_save([
            'local_storage' => 1,
            'photo_url' => NULL,
            'photo_name' => $file,
            'photo_ext' => 'jpg',
            'time_photo' => date('Y-m-d H:i:s'),
          ]);
        }

        if ($row->status == 'new') {

          $row = RestaurantFoodScan::find($row->id);

          //step 2= photo scan
          $datas = SysRobo::photo_scan($row->get_photo(), [
            'confidence' => SysRobo::_SCAN_CONFIDENCE,
            'overlap' => SysRobo::_SCAN_OVERLAP,
          ]);

          $row->update([
            'time_scan' => date('Y-m-d H:i:s'),
            'status' => $datas['status'] ? 'scanned' : 'failed',
            'rbf_api' => $datas['status'] ? json_encode($datas['result']) : NULL,
          ]);

          //step 3= photo predict
          $row->predict_food([
            'notification' => false,
          ]);
        }
      }
    }

  }
}
