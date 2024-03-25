<?php

namespace App\Api;

use Illuminate\Support\Facades\Storage;
use Aws\S3\S3Client;

use App\Jobs\PhotoGet;
use App\Jobs\PhotoScan;
use App\Jobs\PhotoPredict;

use App\Models\Restaurant;
use App\Models\RestaurantFoodScan;
use App\Models\SysSetting;
use App\Models\SysBug;
use App\Models\Food;
use App\Models\Ingredient;

class SysCore
{
  public function random_str($length = 8)
  {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
      $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
  }

  public function get_setting($key)
  {
    $row = SysSetting::where('key', $key)
      ->first();

    return $row ? $row->value : NULL;
  }
  public function bug_add($pars = [])
  {
    if (count($pars)) {
      SysBug::create($pars);
    }
  }

  public function parse_s3_bucket_address($text)
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
  public function parse_date_range($times = NULL)
  {
    $time_from = NULL;
    $time_to = NULL;

//    $times = "03/01/2024 01:30 - 28/02/2024 23:59";
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
      $second_to = '00';
      if ($minute_to == 59) {
        $second_to = 59;
      }
      $time_to = date('Y-m-d', strtotime(str_replace('/', '-', $date_to))) . ' ' . $hour_to . ':' . $minute_to . ':' . $second_to;
    }

    return [
      'time_from' => $time_from,
      'time_to' => $time_to,
    ];
  }
  public function parse_to_query($query)
  {
    return vsprintf(str_replace('?', '%s', $query->toSql()), collect($query->getBindings())->map(function ($binding) {
      $binding = addslashes($binding);
      return is_numeric($binding) ? $binding : "'{$binding}'";
    })->toArray());
  }

  public function s3_todo()
  {
//restaurants
    $select = Restaurant::where('deleted', 0)
      ->where('s3_bucket_name', '<>', NULL)
      ->where('s3_bucket_address', '<>', NULL);
    $restaurants = $select->get();

    //log
    $log_path = 'public/logs/cron_tastevn.log';
    Storage::prepend($log_path, 'RUN_AT_' . date('d_M_Y_H_i_s'));

    if (count($restaurants)) {
      foreach ($restaurants as $restaurant) {

        dispatch(new PhotoGet($restaurant));

        Storage::prepend($log_path, 'RESTAURANT - ' . $restaurant->id . ' - ' . $restaurant->name);
      }
    }
  }
  public function s3_get_photos($pars = [])
  {
//settings
    $s3_region = $this->get_setting('s3_region');
    $s3_api_key = $this->get_setting('s3_api_key');
    $s3_api_secret = $this->get_setting('s3_api_secret');

    //restaurants
    $select = Restaurant::where('deleted', 0)
      ->where('s3_bucket_name', '<>', NULL)
      ->where('s3_bucket_address', '<>', NULL);

    if (count($pars) && isset($pars['restaurant_id'])) {
      $select->where('id', (int)$pars['restaurant_id']);
    }

    $restaurants = $select->get();

    if (count($restaurants) && !empty($s3_region) && !empty($s3_api_key) && !empty($s3_api_secret)) {
      foreach ($restaurants as $restaurant) {

        $s3_bucket = $restaurant->s3_bucket_name;
        $s3_address = $this->parse_s3_bucket_address($restaurant->s3_bucket_address);
        if (empty($s3_bucket) || empty($s3_address)) {
          continue;
        }

        $scan_date = date("Y-m-d");
        $scan_hour = date('H');

        if (count($pars) && isset($pars['scan_date'])) {
          $scan_date = $pars['scan_date'];
        }
        if (count($pars) && isset($pars['scan_hour'])) {
          $scan_hour = $pars['scan_hour'];
        }

        try {

          $s3_api = new S3Client([
            'version' => 'latest',
            'region' => $s3_region,
            'credentials' => array(
              'key' => $s3_api_key,
              'secret' => $s3_api_secret
            )
          ]);

          $s3_objects = $s3_api->ListObjects([
            'Bucket' => $s3_bucket,
            'Delimiter' => '/',
//      'Prefix' => '58-5b-69-19-ad-67/SENSOR/1/2023-11-30/11/',
            'Prefix' => "{$s3_address}/{$scan_date}/{$scan_hour}/",
          ]);

          if ($s3_objects && isset($s3_objects['Contents']) && count($s3_objects['Contents'])) {
            foreach ($s3_objects['Contents'] as $content) {

              $URL = "https://s3.{$s3_region}.amazonaws.com/{$s3_bucket}/{$content['Key']}";
              //valid photo
              if (@getimagesize($URL)) {

                $row = RestaurantFoodScan::where('deleted', 0)
                  ->where('restaurant_id', $restaurant->id)
                  ->where('photo_name', $content['Key'])
                  ->first();
                if ($row) {
                  continue;
                }

                $time_photo = date('Y-m-d H:i:s', strtotime($content['LastModified']->__toString()));
                $exts = explode('.', $content['Key']);

                RestaurantFoodScan::create([
                  'restaurant_id' => $restaurant->id,
                  'photo_url' => $URL,
                  'photo_name' => $content['Key'],
                  'photo_ext' => $exts[1],
                  'status' => 'new',
                  'time_photo' => $time_photo,
                ]);
              }
            }
          }

          dispatch(new PhotoScan($restaurant));

        } catch (\Exception $e) {

          $this->bug_add([
            'type' => 's3_photo_get',
            'line' => $e->getLine(),
            'file' => $e->getFile(),
            'message' => $e->getMessage(),
            'params' => json_encode($e),
          ]);
        }

      }
    }
  }

  public function rbf_scan_photos($pars = [])
  {
    $restaurant = null;

//settings
    $rbf_dataset = $this->get_setting('rbf_dataset_scan');
    $rbf_api_key = $this->get_setting('rbf_api_key');

    //new rows
    $select = RestaurantFoodScan::where('deleted', 0)
      ->where('status', 'new')
//      ->limit(1)
      ->orderBy('id', 'asc');

    if (count($pars) && isset($pars['restaurant_id'])) {

      $select->where('restaurant_id', (int)$pars['restaurant_id']);

      $restaurant = Restaurant::find((int)$pars['restaurant_id']);
    }

    try {

      $rows = $select->get();

      if (count($rows)) {

        foreach ($rows as $row) {
          // URL for Http Request
          $url = "https://detect.roboflow.com/" . $rbf_dataset
            . "?api_key=" . $rbf_api_key
            . "&image=" . urlencode($row->photo_url);

          // Setup + Send Http request
          $options = array(
            'http' => array(
              'header' => "Content-type: application/x-www-form-urlencoded\r\n",
              'method' => 'POST'
            ));


          $context = stream_context_create($options);
          $result = file_get_contents($url, false, $context);
          if (!empty($result)) {
            $result = (array)json_decode($result);
          }

          //valid data
          if (count($result)) {

            $row->update([
              'status' => 'scanned',
              'time_scan' => date('Y-m-d H:i:s'),
              'rbf_api' => json_encode($result),
            ]);

          } else {

            $row->update([
              'status' => 'failed',
              'time_scan' => date('Y-m-d H:i:s'),
            ]);
          }

        }
      }

      if ($restaurant) {
        dispatch(new PhotoPredict($restaurant));
      }

    } catch (\Exception $e) {

      $this->bug_add([
        'type' => 'rbf_photo_scan',
        'line' => $e->getLine(),
        'file' => $e->getFile(),
        'message' => $e->getMessage(),
        'params' => json_encode($e),
      ]);
    }
  }
  public function rbf_retrain()
  {
//settings
    $rbf_dataset = $this->get_setting('rbf_dataset_upload');
    $rbf_api_key = $this->get_setting('rbf_api_key');

    //retrain rows
    $select = RestaurantFoodScan::where('deleted', 0)
      ->where('rbf_retrain', 1)
      ->orderBy('id', 'asc');

    //log
    $log_path = 'public/logs/cron_tastevn_rbf_retrain.log';
    Storage::prepend($log_path, 'RUN_AT_' . date('d_M_Y_H_i_s'));

    try {

      $rows = $select->get();

      if (count($rows)) {

        $count = 0;

        foreach ($rows as $row) {

          Storage::prepend($log_path, 'ROW_' . $row->id . '_START_');

          $count++;

          // URL for Http Request
          $url = "https://api.roboflow.com/dataset/"
            . $rbf_dataset .  "/upload"
            .  "?api_key="  .  $rbf_api_key
            .  "&name=re_training_" . date('Y_m_d_H_i_s') . "_" . $count . "." . $row->photo_ext
            . "&split=train"
            . "&image=" . urlencode($row->photo_url);

          // Setup + Send Http request
          $options = array(
            'http' => array (
              'header' => "Content-type: application/x-www-form-urlencoded\r\n",
              'method'  => 'POST'
            ));

          $context  = stream_context_create($options);
          $result = file_get_contents($url, false, $context);

          Storage::prepend($log_path, 'ROW_' . $row->id . '_RESULT_' . json_encode($result));

          if (!empty($result)) {
            $result = (array)json_decode($result);
          }

          $status = 3;
          if (count($result) && isset($result['id']) && !empty($result['id'])) {
            $status = 2;
          }

          $row->update([
            'rbf_retrain' => $status,
          ]);
        }
      }

    } catch (\Exception $e) {

      $this->bug_add([
        'type' => 'rbf_photo_retrain',
        'line' => $e->getLine(),
        'file' => $e->getFile(),
        'message' => $e->getMessage(),
        'params' => json_encode($e),
      ]);
    }

  }

  public function sys_predict_photos($pars = [])
  {
//scanned rows
    $select = RestaurantFoodScan::where('deleted', 0)
      ->where('status', 'scanned')
      ->orderBy('id', 'asc');

    if (count($pars) && isset($pars['restaurant_id'])) {
      $select->where('restaurant_id', (int)$pars['restaurant_id']);
    }

    try {

      $rows = $select->get();
      if (count($rows)) {
        foreach ($rows as $row) {

          $row->predict_food();
        }
      }

    } catch (\Exception $e) {

      $this->bug_add([
        'type' => 'sys_photo_predict',
        'line' => $e->getLine(),
        'file' => $e->getFile(),
        'message' => $e->getMessage(),
        'params' => json_encode($e),
      ]);
    }
  }
  public function sys_ingredients_found($pars = [])
  {
    $arr = [];
    $existed = [];

    if (count($pars)) {
      foreach ($pars as $prediction) {
        $prediction = (array)$prediction;

        $ingredient = Ingredient::whereRaw('LOWER(name) LIKE ?', strtolower(trim($prediction['class'])))
          ->first();
        if ($ingredient) {

          if (in_array($ingredient->id, $existed)) {
            foreach ($arr as $k => $v) {
              if ($v['id'] == $ingredient->id) {
                $arr[$k]['quantity'] += 1;
              }
            }
          } else {
            $arr[] = [
              'id' => $ingredient->id,
              'quantity' => 1,
            ];
          }

          $existed[] = $ingredient->id;
        }
      }
    }

    return $arr;
  }
  public function sys_predict_foods_by_ingredients($ingredients = [], $one_food = false)
  {
    $arr = [];

    $ingredients = array_map('current', $ingredients);

    //foods
    $foods = Food::where('deleted', 0)
      ->get();
    if (count($foods) && count($ingredients)) {
      foreach ($foods as $food) {
        $confidence = $food->check_food_confidence_by_ingredients($ingredients);
        if ($confidence) {
          $arr[] = [
            'food' => $food->id,
            'food_name' => $food->name,
            'confidence' => $confidence,
          ];
        }
      }
    }

    if (count($arr)) {
      $a1 = [];
      $a2 = [];
      foreach ($arr as $key => $row) {
        $a1[$key] = $row['confidence'];
        $a2[$key] = $row['food'];
      }
      array_multisort($a1, SORT_DESC, $a2, SORT_DESC, $arr);

      if ($one_food) {
        $arr = $arr[0];
      }
    }

    return $arr;
  }

  public function get_notifications()
  {
    return [
      'missing_ingredient'
    ];
  }
}
