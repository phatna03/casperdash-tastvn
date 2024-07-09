<?php

namespace App\Api;
use App\Models\RestaurantFood;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Storage;
//lib
use App\Api\SysApp;
use App\Api\SysCore;
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
      //morning glory
      'deli3' => [
        'restaurant' => 'deli',
        'bucket' => 's3_bucket_deli',
        'folder' => '/58-5b-69-21-f7-cb/',
      ],
      'deli4' => [
        'restaurant' => 'deli',
        'bucket' => 's3_bucket_deli',
        'folder' => '/58-5b-69-21-f7-ca/',
      ],
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
      ->orderBy('rbf_scan', 'asc')
      ->orderBy('id', 'asc')
      ->paginate($limit, ['*'], 'page', $page)
      ->first();

    if (!$restaurant) {
      return false;
    }

    $file_log = 'public/logs/cron_get_photos_' . $restaurant->id . '.log';
    Storage::append($file_log, '===================================================================================');

    $restaurant_parent = $restaurant->get_parent();

    $cur_date = date('Y-m-d');
    $cur_hour = (int)date('H');
    $cur_minute = (int)date('i');

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

    //re-call for 59 like 18h59 -> 19h00
    if (!$cur_minute) {
      $cur_hour -= 1;
    }

    $folder_setting = $sys_app->parse_s3_bucket_address($restaurant->s3_bucket_address);
    $directory = $folder_setting . '/' . $cur_date . '/' . $cur_hour . '/';

    //model2
    $model2 = false;
    if ($restaurant_parent && $restaurant_parent->model_scan
      && !empty($restaurant_parent->model_name) && !empty($restaurant_parent->model_version)
    ) {
      $model2 = true;
    }

    Storage::append($file_log, 'MODEL 2 CALL?= ' . $model2);

    $files = Storage::disk('sensors')->files($directory);
    if (count($files)) {
      //desc
//      $files = array_reverse($files);
      $count = 0;

      Storage::append($file_log, 'TOTAL FILES= ' . count($files));

      //step 1= photo check
      foreach ($files as $file) {

        Storage::append($file_log, 'FILE CHECK= ' . $file);

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

        Storage::append($file_log, 'FILE VALID= OK');

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

          //step 2= photo scan
          //model2
          if ($model2) {
            $row->model_api_2([
              'dataset' => $restaurant_parent->model_name,
              'version' => $restaurant_parent->model_version,
            ]);
          }
          else {
            $row->model_api_1([
              'confidence' => SysRobo::_SCAN_CONFIDENCE,
              'overlap' => SysRobo::_SCAN_OVERLAP,
            ]);
          }

          //step 3= photo predict
          $row->predict_food([
            'notification' => $old ? false : $notify,
          ]);
        }
      }
    }

    Storage::append($file_log, '===================================================================================');
  }

  public static function photo_name_query($file)
  {
    $temps = explode('/', $file);
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

  //v3
  public const _RBF_CONFIDENCE = 30;
  public const _RBF_OVERLAP = 60;
  public const _RBF_MAX_OBJECTS = 70;

  public const _SYS_BURGER_GROUP_1 = [32, 33, 71, 72];
  public const _SYS_BURGER_GROUP_2 = [34];
  public const _SYS_BURGER_GROUP_VEGAN = [32];
  public const _SYS_BURGER_INGREDIENTS = [45, 114];

  public static function photo_1024($img_url)
  {
    $img_1024 = 'https://resize.sardo.work/?imageUrl=' . $img_url . '&width=1024';
    if (@getimagesize($img_1024)) {
      $img_url = $img_1024;
    }

    return $img_url;
  }

  public static function photo_check($pars = [])
  {
    //pars
    $debug = isset($pars['debug']) ? (bool)$pars['debug'] : false;
    if ($debug) {
      var_dump(SysCore::var_dump_break());
      var_dump('photo check...');
    }

    //img_url
    $img_url = isset($pars['check_url']) && !empty($pars['check_url']) ? $pars['check_url'] : NULL;
    //localhost
    if (App::environment() == 'local') {
      $img_url = "https://s3.ap-southeast-1.amazonaws.com/cargo.tastevietnam.asia/58-5b-69-19-ad-83/SENSOR/1/2024-07-06/18/SENSOR_2024-07-06-18-08-53-536_693.jpg";
    }

    $rfs = isset($pars['rfs']) && !empty($pars['rfs']) ? $pars['rfs'] : NULL;
    if ($rfs) {
      $pars['rfs'] = $rfs->id;

      $img_url = $rfs->get_photo();
    }

    $img_1024 = isset($pars['img_1024']) ? (bool)$pars['img_1024'] : false;
    $img_url_1024 = SysRobo::photo_1024($img_url);
    if ($img_1024 && !empty($img_url_1024)) {
      $img_url = $img_url_1024;
    }

    if ($debug) {
      var_dump('img_url= ' . $img_url);
    }

    //scan
    //setting
    $api_key = SysCore::get_sys_setting('rbf_api_key');
    $dataset = SysCore::str_trim_slash(SysCore::get_sys_setting('rbf_dataset_scan'));
    $version = SysCore::get_sys_setting('rbf_dataset_ver');
    //pars
    $dataset = isset($pars['sys_dataset']) && !empty($pars['sys_dataset']) ? $pars['sys_dataset'] : $dataset;
    $version = isset($pars['sys_version']) && !empty($pars['sys_version']) ? $pars['sys_version'] : $version;

    $confidence = isset($pars['rbf_confidence']) && !empty($pars['rbf_confidence']) ? $pars['rbf_confidence'] : SysRobo::_RBF_CONFIDENCE;
    $overlap = isset($pars['rbf_overlap']) && !empty($pars['rbf_overlap']) ? $pars['rbf_overlap'] : SysRobo::_RBF_OVERLAP;
    $max_objects = isset($pars['rbf_max_objects']) && !empty($pars['rbf_max_objects']) ? $pars['rbf_max_objects'] : SysRobo::_RBF_MAX_OBJECTS;

    $datas = SysRobo::photo_scan([
      'img_url' => $img_url,

      'api_key' => $api_key,
      'dataset' => $dataset,
      'version' => $version,

      'confidence' => $confidence,
      'overlap' => $overlap,
      'max_objects' => $max_objects,

      'debug' => $debug,
    ]);

    if ($debug) {
      var_dump(SysCore::var_dump_break());
      var_dump('photo scan datas...');
      var_dump($datas);
    }

    if (!$datas['status']) {

      SysCore::log_sys_bug([
        'type' => 'photo_check',
        'file' => isset($datas['error']['file']) ? $datas['error']['file'] : NULL,
        'line' => isset($datas['error']['line']) ? $datas['error']['line'] : NULL,
        'message' => isset($datas['error']['message']) ? $datas['error']['message'] : NULL,
        'params' => json_encode(array_merge($pars, $datas['result']))
      ]);

      if ($debug) {
        var_dump(SysCore::var_dump_break());
        var_dump('photo scan error...');
      }

      return false;
    }

    $rbf_result = $datas['result'];
    $restaurant_parent_id = isset($pars['restaurant_parent_id']) ? (int)$pars['restaurant_parent_id'] : 0;
    $restaurant_parent = RestaurantParent::find($restaurant_parent_id);
    if (!$restaurant_parent) {

      if ($debug) {
        var_dump('invalid restaurant...');
      }

      return false;
    }

    //find foods
    $foods = SysRobo::foods_find([
      'predictions' => isset($rbf_result['predictions']) ? $rbf_result['predictions'] : [],
      'restaurant_parent_id' => $restaurant_parent_id,

      'debug' => $debug,
    ]);

    if ($debug) {
      var_dump(SysCore::var_dump_break());
      var_dump('find foods...');
      var_dump($foods);
    }

    if (!count($foods)) {

      if ($debug) {
        var_dump(SysCore::var_dump_break());
        var_dump('no foods found...');
      }

      return false;
    }

    //find food 1
    $foods = SysRobo::foods_valid($foods, [
      'predictions' => isset($rbf_result['predictions']) ? $rbf_result['predictions'] : [],

      'debug' => $debug,
    ]);

    if (!count($foods)) {

      if ($debug) {
        var_dump(SysCore::var_dump_break());
        var_dump('no food 1 found...');
      }

      return false;
    }

    if ($debug) {
      var_dump(SysCore::var_dump_break());
      var_dump('food 1 final= ' . $foods['food'] . ' - confidence= ' . $foods['confidence']);
    }

    //find category
    $food = Food::find($foods['food']);

    $food_category = $food->get_category([
      'restaurant_parent_id' => $restaurant_parent_id
    ]);

    if ($debug) {
      var_dump(SysCore::var_dump_break());
      if ($food_category) {
        var_dump('category= ' . $food_category->name . ' - ID= ' . $food_category->id);
      } else {
        var_dump('no category found...');
      }
    }

    //find ingredients found
    $ingredients_found = SysRobo::ingredients_found($food, [
      'predictions' => isset($rbf_result['predictions']) ? $rbf_result['predictions'] : [],
      'restaurant_parent_id' => $restaurant_parent_id,

      'debug' => $debug
    ]);

    if ($debug) {
      var_dump(SysCore::var_dump_break());
      var_dump('food ingredients found...');
      var_dump($ingredients_found);
    }

    //find ingredients missing
    $ingredients_missing = SysRobo::ingredients_missing($food, [
      'predictions' => isset($rbf_result['predictions']) ? $rbf_result['predictions'] : [],
      'restaurant_parent_id' => $restaurant_parent_id,
      'ingredients_found' => $ingredients_found,

      'debug' => $debug
    ]);

    if ($debug) {
      var_dump(SysCore::var_dump_break());
      var_dump('food ingredients missing...');
      var_dump($ingredients_missing);
    }
  }

  public static function photo_scan($pars = [])
  {
    //pars
    $debug = isset($pars['debug']) ? (bool)$pars['debug'] : false;
    if ($debug) {
      var_dump('<br />');
      var_dump('photo scan...');
    }

    $server_url = 'https://detect.roboflow.com'; //robot
    $server_url = 'http://47.128.217.148:9001'; //ec2 clone

    //datas
    $datas = [
      'server_url' => $server_url,

      'img_url' => isset($pars['img_url']) ? $pars['img_url'] : NULL,

      'api_key' => isset($pars['api_key']) ? $pars['api_key'] : NULL,
      'dataset' => isset($pars['dataset']) ? $pars['dataset'] : NULL,
      'version' => isset($pars['version']) ? $pars['version'] : NULL,

      'confidence' => isset($pars['confidence']) ? $pars['confidence'] : NULL,
      'overlap' => isset($pars['overlap']) ? $pars['overlap'] : NULL,
      'max_objects' => isset($pars['max_objects']) ? $pars['max_objects'] : NULL,
    ];

    if ($debug) {
      var_dump('rbf prepare...');
      var_dump($datas);
    }

    //rbf
    $status = true;
    $error = [];

    // URL for Http Request
    $api_url = $datas['server_url'] . "/" . $datas['dataset'] . "/" . $datas['version']
      . "?api_key=" . $datas['api_key']
      . "&confidence=" . $datas['confidence']
      . "&overlap=" . $datas['overlap']
      . "&max_objects=" . $datas['max_objects']
      . "&image=" . urlencode($datas['img_url']);

    if ($debug) {
      var_dump('rbf api url= ' . $api_url);
    }

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

      'result' => array_merge($datas, $result),
    ];
  }

  public static function foods_find($pars = [])
  {
    //pars
    $debug = isset($pars['debug']) ? (bool)$pars['debug'] : false;
    if ($debug) {
      var_dump('<br />');
      var_dump('foods find...');
    }

    $foods = [];
    $restaurant_parent_id = isset($pars['restaurant_parent_id']) ? (int)$pars['restaurant_parent_id'] : 0;

    $predictions = isset($pars['predictions']) ? (array)$pars['predictions'] : [];
    if (!count($predictions)) {

      if ($debug) {
        var_dump('no predictions found...');
      }

      return $foods;
    }

    $food_temps = [];
    $food_only = isset($pars['food_only']) ? (bool)$pars['food_only'] : false;

    foreach ($predictions as $prediction) {
      $prediction = (array)$prediction;

      $confidence = (int)($prediction['confidence'] * 100);
      $class = strtolower(trim($prediction['class']));

      $item = RestaurantFood::query('restaurant_foods')
        ->select('foods.id')
        ->leftJoin('foods', 'foods.id', '=', 'restaurant_foods.food_id') //serve
        ->where('foods.deleted', 0)
        ->where('restaurant_foods.deleted', 0)
        ->where('restaurant_foods.confidence', '<=', $confidence) //confidence
        ->whereRaw('LOWER(foods.name) LIKE ?', $class)
        ->first();

      if ($item && $item->id) {
        $food = Food::find($item->id);

        if ($debug) {
          var_dump('food found = ' . $food->name . ' - ID= ' . $food->id);
          var_dump('food confidence = ' . $confidence);
        }

        //check ingredient valid
        $valid_food = true;
        $food_ingredients = $food->get_ingredients([
          'restaurant_parent_id' => $restaurant_parent_id,
        ]);
        if (!count($food_ingredients)) {
          $valid_food = false;
        }

        //check ingredient core
        $valid_core = true;
        $core_ids = $food->get_ingredients_core([
          'restaurant_parent_id' => $restaurant_parent_id,
        ]);
        if (count($core_ids)) {
          //percent
          $valid_core = SysRobo::ingredients_core_valid([
            'predictions' => $predictions,
            'cores' => $core_ids,

            'debug' => $debug,
          ]);
        }
        if ($food_only) {
          $valid_core = $food_only;
        }

        if ($debug) {
          var_dump('ingredient valid = ' . $valid_food);
          var_dump('ingredient core = ' . $valid_core);
        }

        if ($valid_core && $valid_food) {
          $foods[] = [
            'food' => $food->id,
            'confidence' => $confidence,
          ];
        }

        if ($valid_food && $confidence >= 90) {
          $food_temps[] = [
            'food' => $food->id,
            'confidence' => $confidence,
          ];
        }
      }

    }


    if ($debug) {
      var_dump('foods temps...');
      var_dump($food_temps);
    }

    if (!count($foods) && count($food_temps) == 1) {
      $foods = $food_temps;
    }

    return $foods;
  }

  public static function ingredients_core_valid($pars = [])
  {
    $predictions = isset($pars['predictions']) ? (array)$pars['predictions'] : [];
    $cores = isset($pars['cores']) ? (array)$pars['cores']->toArray() : [];

    $valid = true;

    if (count($predictions) && count($cores)) {

      foreach ($cores as $core) {
        $count = 0;
        $str1 = trim(strtolower($core['ingredient_name']));

        foreach ($predictions as $prediction) {
          $prediction = (array)$prediction;

          $confidence = round($prediction['confidence'] * 100);
          $str2 = trim(strtolower($prediction['class']));

          if ($confidence >= $core['ingredient_confidence'] && $str1 === $str2) {
            $count++;
          }
        }

        if ($count < $core['ingredient_quantity']) {
          $valid = false;
          break;
        }
      }
    }

    return $valid;
  }

  public static function foods_valid($temps, $pars = [])
  {
    //pars
    $debug = isset($pars['debug']) ? (bool)$pars['debug'] : false;
    if ($debug) {
      var_dump('<br />');
      var_dump('food find 1 valid...');
    }

    $predictions = isset($pars['predictions']) ? (array)$pars['predictions'] : [];

    //confidence highest
    $food_id = 0;
    $food_confidence = 0;

    if (count($temps)) {

      if (count($temps) > 1) {
        $a1 = [];
        $a2 = [];
        foreach ($temps as $key => $val) {
          $a1[$key] = $val['confidence'];
          $a2[$key] = $val['food'];
        }
        array_multisort($a1, SORT_DESC, $a2, SORT_DESC, $temps);
      }

      $temp = $temps[0];

      $food_id = $temp['food'];
      $food_confidence = $temp['confidence'];
    }

    if ($debug) {
      var_dump('food 1 found= ' . $food_id . ' - confidence= ' . $food_confidence);
    }

    //group burger
    $burger1s = SysRobo::_SYS_BURGER_GROUP_1;
    $burger2s = SysRobo::_SYS_BURGER_GROUP_2;

    if ($food_id && (in_array($food_id, $burger1s)) || in_array($food_id, $burger2s)) {
      if ($debug) {
        var_dump('<br />');
        var_dump('food in group burger...');
      }

      $total_hambuger_bread = 0;
      if (count($predictions)) {
        foreach ($predictions as $prediction) {
          $prediction = (array)$prediction;

          $class = trim(strtolower($prediction['class']));

          if ($class === 'hamburger bread') {
            $total_hambuger_bread++;
          }
        }
      }

      if ($debug) {
        var_dump('total hamburger bread = ' . $total_hambuger_bread);
      }

      if (in_array($food_id, $burger1s)) {
        if ($total_hambuger_bread > 1) {
          foreach ($temps as $temp) {
            if (in_array($temp['food'], $burger2s)) {
              $food_id = $temp['food'];
              $food_confidence = $temp['confidence'];
            }
          }

          if ($debug) {
            var_dump('food 1 change= ' . $food_id . ' - confidence=' . $food_confidence);
          }
        }
      } elseif (in_array($food_id, $burger2s)) {
        if ($total_hambuger_bread == 1) {
          foreach ($temps as $temp) {
            if (in_array($temp['food'], $burger1s)) {
              $food_id = $temp['food'];
              $food_confidence = $temp['confidence'];
            }
          }

          if ($debug) {
            var_dump('food 1 change= ' . $food_id . ' - confidence=' . $food_confidence);
          }
        }
      }
    }

    return [
      'food' => $food_id,
      'confidence' => $food_confidence,
    ];
  }

  public static function ingredients_found(Food $food, $pars = [])
  {
    //pars
    $debug = isset($pars['debug']) ? (bool)$pars['debug'] : false;
    if ($debug) {
      var_dump('<br />');
      var_dump('food find ingredients compact...');
    }

    $predictions = isset($pars['predictions']) ? (array)$pars['predictions'] : [];
    $restaurant_parent_id = isset($pars['restaurant_parent_id']) ? (int)$pars['restaurant_parent_id'] : 0;

    $ingredients = $food->get_ingredients_info([
      'restaurant_parent_id' => $restaurant_parent_id,
      'predictions' => $predictions,

      'debug' => $debug,
    ]);

    return $ingredients;
  }

  public static function ingredients_missing(Food $food, $pars = [])
  {
    //pars
    $debug = isset($pars['debug']) ? (bool)$pars['debug'] : false;
    if ($debug) {
      var_dump('<br />');
      var_dump('food find ingredients missing...');
    }

    $predictions = isset($pars['predictions']) ? (array)$pars['predictions'] : [];
    $restaurant_parent_id = isset($pars['restaurant_parent_id']) ? (int)$pars['restaurant_parent_id'] : 0;
    $ingredients_found = isset($pars['ingredients_found']) ? (array)$pars['ingredients_found'] : [];

    $ingredients = [];
    $ids = [];

    $food_ingredients = $food->get_ingredients([
      'restaurant_parent_id' => $restaurant_parent_id,
    ]);
    if (count($food_ingredients) && count($ingredients_found)) {
      foreach ($food_ingredients as $food_ingredient) {
        $found = false;

        foreach ($ingredients_found as $ing_found) {
          if ($ing_found['id'] == $food_ingredient->id) {
            $found = true;

            if ($ing_found['quantity'] < $food_ingredient->ingredient_quantity) {
              if (!in_array($ing_found['id'], $ids)) {
                $ing_found['quantity'] = $food_ingredient->ingredient_quantity - $ing_found['quantity'];

                $ing = Ingredient::find($ing_found['id']);
                $ingredients[] = [
                  'id' => $ing->id,
                  'quantity' => $ing_found['quantity'],
                  'name' => $ing->name,
                  'name_vi' => $ing->name_vi,
                  'type' => $ing->ingredient_type,
                ];

                $ids[] = $ing_found['id'];
              }
            }
          }
        }

        if (!$found) {
          $ingredients[] = [
            'id' => $food_ingredient->id,
            'quantity' => $food_ingredient->ingredient_quantity,
            'name' => $food_ingredient->name,
            'name_vi' => $food_ingredient->name_vi,
            'type' => $food_ingredient->ingredient_type,
          ];
        }
      }

    } else {

      if (count($food_ingredients)) {
        foreach ($food_ingredients as $food_ingredient) {
          $ingredients[] = [
            'id' => $food_ingredient->id,
            'quantity' => $food_ingredient->ingredient_quantity,
            'name' => $food_ingredient->name,
            'name_vi' => $food_ingredient->name_vi,
            'type' => $food_ingredient->ingredient_type,
          ];
        }
      }
    }

    //group burger
    $burger1s = SysRobo::_SYS_BURGER_GROUP_1;
    $burger2s = SysRobo::_SYS_BURGER_GROUP_2;
    $burger3s = SysRobo::_SYS_BURGER_GROUP_VEGAN;
    $burger_ingredients = SysRobo::_SYS_BURGER_INGREDIENTS;
    $burger_check = false;

    if (count($ingredients)) {

      $temps = [];
      $burger_needed = false;

      foreach ($ingredients as $ingredient) {
        if (in_array($ingredient['id'], $burger_ingredients)) {
          $burger_check = true;
        }

        $temps[$ingredient['id']] = $ingredient;
      }

      if ($burger_check) {
        if ($debug) {
          var_dump('<br />');
          var_dump('burger ingredients check...');
        }

        if (in_array($food->id, $burger3s)) {
          if ($debug) {
            var_dump('<br />');
            var_dump('burger VEGAN...');
          }
        } else {

          $burger_founds_quantity = SysRobo::burger_ingredients_quantity($predictions);
          if ($debug) {
            var_dump('<br />');
            var_dump('burger ingredients quantity= ' . $burger_founds_quantity);
          }

          if ($burger_founds_quantity) {
            if (in_array($food->id, $burger1s)) {
              $burger_needed = true;
            } elseif (in_array($food->id, $burger2s)) {
              if ($burger_founds_quantity >= 2) {
                $burger_needed = true;
              } else {

                if ($debug) {
                  var_dump('<br />');
                  var_dump('burger ingredients change...');
                }

                //missing 1
                $food_ingredient = Ingredient::find(45); //grilled chicken

                foreach ($burger_ingredients as $burger_ingredient) {
                  if (isset($temps[$burger_ingredient])) {
                    unset($temps[$burger_ingredient]);
                  }
                }

                $temps[] = [
                  'id' => $food_ingredient->id,
                  'quantity' => 1,
                  'name' => $food_ingredient->name,
                  'name_vi' => $food_ingredient->name_vi,
                  'type' => $food_ingredient->ingredient_type,
                ];

                $ingredients = $temps;
              }
            }
          }
        }
      }

      if ($burger_needed) {
        if ($debug) {
          var_dump('<br />');
          var_dump('burger ingredients change...');
        }

        foreach ($burger_ingredients as $burger_ingredient) {
          if (isset($temps[$burger_ingredient])) {
            unset($temps[$burger_ingredient]);
          }
        }

        $ingredients = $temps;
      }
    }

    return $ingredients;
  }

  public static function burger_ingredients_quantity($predictions)
  {
    $quantity = 0;

    if (count($predictions)) {
      foreach ($predictions as $prediction) {
        $prediction = (array)$prediction;

        if (strtolower(trim($prediction['class'])) == 'beef buger'
          || strtolower(trim($prediction['class'])) == 'beef burger'
          || strtolower(trim($prediction['class'])) == 'grilled chicken') {
          $quantity++;
        }
      }
    }

    return $quantity;
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
}
