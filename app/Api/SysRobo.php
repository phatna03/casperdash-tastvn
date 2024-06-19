<?php

namespace App\Api;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Storage;
//lib
use App\Api\SysApp;
use App\Models\Restaurant;
use App\Models\RestaurantParent;
use App\Models\RestaurantFoodScan;
use App\Models\Food;
use App\Models\Ingredient;

class SysRobo
{
  public const _SCAN_CONFIDENCE = 30;
  public const _SCAN_OVERLAP = 60;
  public const _FOOD_CONFIDENCE = 70;

  public static function s3_bucket_folder()
  {
    return [
      'cargo1' => [
        'restaurant' => 'cargo',
        'bucket' => 's3_bucket_cargo',
        'folder' => '/58-5b-69-19-ad-83/',
      ],
      'cargo2' => [
        'restaurant' => 'cargo',
        'bucket' => 's3_bucket_cargo',
        'folder' => '/58-5b-69-19-ad-67/',
      ],
      'deli1' => [
        'restaurant' => 'deli',
        'bucket' => 's3_bucket_deli',
        'folder' => '/58-5b-69-19-ad-b6/',
      ],
      'deli2' => [
        'restaurant' => 'deli',
        'bucket' => 's3_bucket_deli',
        'folder' => '/58-5b-69-20-11-7b/',
      ],
      'market' => [
        'restaurant' => 'market',
        'bucket' => 's3_bucket_market',
        'folder' => '/58-5b-69-20-a8-f6/',
      ],
      'poison' => [
        'restaurant' => 'poison',
        'bucket' => 's3_bucket_poison',
        'folder' => '/58-5b-69-15-cd-2b/',
      ],
    ];
  }

  public static function photo_scan($rfs, $pars = [])
  {
    $sys_app = new SysApp();

    //setting web
    $dataset = $sys_app->parse_s3_bucket_address($sys_app->get_setting('rbf_dataset_scan'));
    $version = $sys_app->get_setting('rbf_dataset_ver');
    $api_key = $sys_app->get_setting('rbf_api_key');

    if (isset($pars['version'])) {
      $version = (int)$pars['version'];
    }
    if (isset($pars['dataset'])) {
      $dataset = $pars['dataset'];
    }

    //pars
    $confidence = isset($pars['confidence']) ? (int)$pars['confidence'] : 50;
    $overlap = isset($pars['overlap']) ? (int)$pars['overlap'] : 50;
    $max_objects = isset($pars['max_objects']) ? (int)$pars['max_objects'] : 100;

    $status = true;
    $error = [];

    //img
    $img_url = $rfs ? $rfs->img_1024() : null;

    $new_url = 'https://detect.roboflow.com';
    $new_url = 'http://47.128.217.148:9001';

    //api_testing
    if (isset($pars['img_url']) && !empty($pars['img_url'])) {

      $img_url = $pars['img_url'];

      if (isset($pars['api_testing']) && !empty($pars['api_testing'])) {
        $img_url = SysRobo::photo_1024($pars['img_url']);

        $new_url = 'http://47.128.217.148:9001';
      }
    }

    //localhost
    if (App::environment() == 'local') {
      $img_url = "https://s3.ap-southeast-1.amazonaws.com/cargo.tastevietnam.asia/58-5b-69-19-ad-83/SENSOR/1/2024-06-06/21/SENSOR_2024-06-06-21-21-34-723_176.jpg";
    }

    if (empty($img_url) || !@getimagesize($img_url)) {

      //s3 before 13/6/2024
      if ($rfs) {
        $img_url = SysRobo::photo_1024($rfs->get_photo());
      }

      if (empty($img_url) || !@getimagesize($img_url)) {
        return [
          'status' => false,
          'error' => 'invalid image URL',
          'pars' => $pars,
          'rfs' => $rfs,
        ];
      }
    }

    // URL for Http Request
    $api_url = $new_url . "/" . $dataset . "/" . $version
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

    $result = [];

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

  public static function photo_1024($img_url)
  {
    $img_1024 = 'https://resize.sardo.work/?imageUrl=' . $img_url . '&width=1024';
    if (@getimagesize($img_1024)) {
      $img_url = $img_1024;
    }

    return $img_url;
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
      ->orderBy('rbf_scan', 'asc')
      ->orderBy('id', 'asc')
      ->paginate($limit, ['*'], 'page', $page)
      ->first();

    if (!$restaurant) {
      return false;
    }

    $cur_date = date('Y-m-d');
    $cur_hour = (int)date('H');

    //old
    $notify = true;
    $old = false;

    if (isset($pars['date']) && !empty($pars['date'])) {
      $cur_date = $pars['date'];
      $old = true;
    }
    if (isset($pars['hour'])) {
      $cur_hour = (int)$pars['hour'] ? (int)$pars['hour'] : $pars['hour'];
      $notify = false;
      $old = true;
    }

    $row = NULL;

    $folder_setting = $sys_app->parse_s3_bucket_address($restaurant->s3_bucket_address);
    $directory = $folder_setting . '/' . $cur_date . '/' . $cur_hour . '/';

//    if ($old) {
//      $cur_hour += 11;
//    }

    //live call every hour
//    if ($restaurant->rbf_scan) {
//      if ((int)(date('i')) > 1) {
//        return false;
//      } else {
//        if (!$cur_hour || $cur_hour == 24) {
//          $cur_hour = 23;
//          $date = date('Y-m-d', strtotime("-1 days"));
//        } else {
//          $cur_hour -= 1;
//        }
//      }
//    }

    $files = Storage::disk('sensors')->files($directory);
    if (count($files)) {
      //desc
//      $files = array_reverse($files);
      $count = 0;

      //step 1= photo check
      foreach ($files as $file) {
        $ext = array_filter(explode('.', $file));
        if (!count($ext) || $ext[count($ext) - 1] != 'jpg') {
          continue;
        }

        //no 1024
        $temps = array_filter(explode('/', $file));
        $photo_name = $temps[count($temps) - 1];
        if (substr($photo_name, 0, 5) == '1024_') {
          continue;
        }

        //no duplicate
        $keyword = SysRobo::photo_name_query($file);

        $count++;

        //check exist
        $row = RestaurantFoodScan::where('restaurant_id', $restaurant->id)
          ->where('photo_name', $file)
          ->first();
        if (!$row) {

          $status = 'new';

          $rows = RestaurantFoodScan::where('photo_name', 'LIKE', $keyword)
            ->where('restaurant_id', $restaurant->id)
            ->get();
          if (count($rows)) {
            $status = 'duplicated';
          }

          //step 1= photo get
          $row = $restaurant->photo_save([
            'local_storage' => 1,
            'photo_url' => NULL,
            'photo_name' => $file,
            'photo_ext' => 'jpg',
            'time_photo' => date('Y-m-d H:i:s'),

            'status' => $status,
          ]);

          if ($old) {

            $time_photo = $cur_date . ' '
              . $sys_app->parse_hour_format($cur_hour) . ':'
              . $sys_app->parse_hour_format($count) . ':'
              . $sys_app->parse_hour_format($count);

            $row->update([
              'time_photo' => $time_photo,
            ]);
          }
        }

        if ($row->status == 'new') {

          $row = RestaurantFoodScan::find($row->id);

          $dataset = $sys_app->parse_s3_bucket_address($sys_app->get_setting('rbf_dataset_scan'));
          $version = $sys_app->get_setting('rbf_dataset_ver');

          $rbf_version = [
            'dataset' => $dataset,
            'version' => $version,
          ];

          $row->update([
            'rbf_model' => 3, //running
            'time_scan' => date('Y-m-d H:i:s'),
          ]);

          //step 2= photo scan
          $datas = SysRobo::photo_scan($row, [
            'confidence' => SysRobo::_SCAN_CONFIDENCE,
            'overlap' => SysRobo::_SCAN_OVERLAP,

            'dataset' => $dataset,
            'version' => $version,
          ]);

          $row->update([
            'status' => $datas['status'] ? 'scanned' : 'failed',
            'rbf_api' => $datas['status'] ? json_encode($datas['result']) : NULL,
            'rbf_version' => json_encode($rbf_version),
            'rbf_model' => 0,
          ]);

          //step 3= photo predict
          $row->predict_food([
            'notification' => $old ? false : $notify,
          ]);
        }
      }
    }

  }

  public static function ingredients_compact($pars = [])
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

  public static function foods_find($pars = [])
  {
    $sys_app = new SysApp();

    $arr = [];

    $food_confidence = (int)$sys_app->get_setting('rbf_food_confidence');
    if (!$food_confidence || $food_confidence >= 100) {
      $food_confidence = SysRobo::_FOOD_CONFIDENCE;
    }

    $debug = isset($pars['debug']) ? (bool)$pars['debug'] : false;
    $food_only = isset($pars['food_only']) ? (bool)$pars['food_only'] : false;
    $restaurant_parent_id = isset($pars['restaurant_parent_id']) ? (int)$pars['restaurant_parent_id'] : 0;
    $restaurant_parent = RestaurantParent::find($restaurant_parent_id);

    $predictions = isset($pars['predictions']) ? (array)$pars['predictions'] : [];


    if (count($predictions) && $restaurant_parent) {

      $ingredients_found = SysRobo::ingredients_compact($predictions);

      foreach ($predictions as $prediction) {
        $prediction = (array)$prediction;

        $confidence = (int)($prediction['confidence'] * 100);
        $class = strtolower(trim($prediction['class']));

        $food = Food::where('deleted', 0)
          ->whereRaw('LOWER(name) LIKE ?', $class)
          ->first();

        if ($debug) {
          var_dump('***** FOOD? = ' . ($food ? $food->id . ' - ' . $food->name : 0));
        }

        if ($debug && $food) {
          var_dump('***** FOOD SERVE? = ' . $restaurant_parent->food_serve($food));
        }

        if ($food && $restaurant_parent->food_serve($food) && $confidence >= $food_confidence) {

          //check valid ingredient
          $valid_food = true;
          $food_ingredients = $food->get_ingredients([
            'restaurant_parent_id' => $restaurant_parent_id,
          ]);
          if (!count($food_ingredients)) {
            $valid_food = false;
          }

          //check core ingredient
          $valid_core = true;
          $core_ids = $food->get_ingredients_core([
            'restaurant_parent_id' => $restaurant_parent_id,
            'ingredient_id_only' => 1,
          ]);
          if (count($core_ids)) {
            $found_ids = array_column($ingredients_found, 'id');
            $found_count = 0;
            foreach ($found_ids as $found_id) {
              if (in_array($found_id, $core_ids)) {
                $found_count++;
              }
            }
            if ($found_count != count($core_ids)) {
              $valid_core = false;
            }
          }
          if ($food_only) {
            $valid_core = $food_only;
          }

          if ($debug) {
            var_dump('***** FOODS VALID? = ' . $valid_core . ' && ' . $valid_food);
          }

          if ($valid_core && $valid_food) {
            $arr[] = [
              'food' => $food->id,
              'confidence' => $confidence,
            ];
          }
        }
      }
    }

    return $arr;
  }

  public static function foods_valid($arr = [])
  {
    $food_id = 0;
    $food_confidence = 0;

    if (count($arr)) {

      if (count($arr) > 1) {
        $a1 = [];
        $a2 = [];
        foreach ($arr as $key => $val) {
          $a1[$key] = $val['confidence'];
          $a2[$key] = $val['food'];
        }
        array_multisort($a1, SORT_DESC, $a2, SORT_DESC, $arr);
      }

      $arr = $arr[0];

      $food_id = $arr['food'];
      $food_confidence = $arr['confidence'];
    }

    return [
      'food' => $food_id,
      'confidence' => $food_confidence,
    ];
  }

  public static function photo_name_query($file)
  {
    $temps = array_filter(explode('/', $file));
    $photo_name = $temps[count($temps) - 1];

    $photo_address = str_replace($photo_name, '', $file);

    $photo_name = str_replace('.jpg', '', $photo_name);
    $temp1s = array_filter(explode('_', $photo_name));
    $temp2s = array_filter(explode('-', $temp1s[1]));

    $keyword = '%' . trim($photo_address, '/')
      . '/' . $temp1s[0] . '_'
      . $temp2s[0] . '-' . $temp2s[1] . '-' . $temp2s[2] . '-' . $temp2s[3] . '-' . $temp2s[4]
      . '-%'
      . '_' . $temp1s[2]
      . '.jpg%'
    ;

    return $keyword;
  }
}
