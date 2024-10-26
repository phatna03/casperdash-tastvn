<?php

namespace App\Api;
use App\Models\FoodIngredient;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
//lib
use App\Api\SysCore;
use App\Notifications\IngredientMissing;
use App\Models\SysNotification;
use App\Models\Restaurant;
use App\Models\RestaurantParent;
use App\Models\RestaurantFoodScan;
use App\Models\Food;
use App\Models\Ingredient;
use App\Models\RestaurantFood;

class SysRobo
{
  public const _RBF_CONFIDENCE = 30;
  public const _RBF_OVERLAP = 60;
  public const _RBF_MAX_OBJECTS = 70;

  public const _SYS_BURGER_GROUP_1 = [32, 33, 71, 72];
  public const _SYS_BURGER_GROUP_2 = [34];
  public const _SYS_BURGER_GROUP_VEGAN = [32];
  public const _SYS_BURGER_INGREDIENTS = [45, 114];

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
//      'deli1' => [
//        'restaurant' => 'deli',
//        'bucket' => 's3_bucket_deli',
//        'folder' => '/58-5b-69-19-ad-b6/',
//      ],
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
      //leloi
      'leloi51' => [
        'restaurant' => 'deli',
        'bucket' => 's3_bucket_deli',
        'folder' => '/58-5b-69-21-f7-c5/',
      ],
      //MGO 160 NTH
      'raumuong' => [
        'restaurant' => 'deli',
        'bucket' => 's3_bucket_deli',
        'folder' => '/58-5b-69-21-f7-cd/',
      ],
    ];
  }

  public static function photo_get($pars = [])
  {
    if ((int)date('H') < 8) {
      return false;
    }

    //pars
    $debug = isset($pars['debug']) ? (bool)$pars['debug'] : false;
    $limit = isset($pars['limit']) ? (int)$pars['limit'] : 1;
    $page = isset($pars['page']) ? (int)$pars['page'] : 1;

    //run
    $sensor = Restaurant::where('deleted', 0)
      ->where('restaurant_parent_id', '>', 0)
      ->where('s3_bucket_name', '<>', NULL)
      ->where('s3_bucket_address', '<>', NULL)
      ->orderBy('id', 'asc')
      ->paginate($limit, ['*'], 'page', $page)
      ->first();

    $file_log = 'public/logs/' . date('Y-m-d') . '/' . date('H') . '/cron_photo_get_run.log';
    Storage::append($file_log, '***************************************************************************'
      . 'START_' . date('Y_m_d_H_i_s') . '_LIMIT_' .$limit . '_PAGE_' . $page . '_MS_' . SysCore::time_to_ms());

    if (!$sensor) {
      return false;
    }

    $file_log = 'public/logs/' . date('Y-m-d') . '/' . date('H') . '/cron_photo_get_' . $sensor->id . '.log';
    Storage::append($file_log, SysCore::var_dump_break());
    Storage::append($file_log, '***************************************************************************'
      . 'START_' . date('Y_m_d_H_i_s') . '_' . SysCore::time_to_ms());

    if (!$sensor || ($sensor && $sensor->s3_checking)) {
      //time over
      if ($sensor && $sensor->s3_checking && time() - strtotime($sensor->updated_at) > 60 * 2) {
        $sensor->update([
          's3_checking' => 0,
        ]);
      }
      return false;
    }

    if ($debug) {
      var_dump(SysCore::var_dump_break());
      var_dump('SENSOR= ' . $sensor->name . ' - ID= ' . $sensor->id);
    }

    $sensor->update([
      's3_checking' => 1,
    ]);

    $cur_date = date('Y-m-d');
    $cur_hour = (int)date('H');
    $cur_minute = (int)date('i');

    if (isset($pars['date']) && !empty($pars['date'])) {
      $cur_date = $pars['date'];
    }
    if (isset($pars['hour'])) {
      $cur_hour = (int)$pars['hour'] ? (int)$pars['hour'] : $pars['hour'];
    }

    //re-call for 59 like 18h59 -> 19h00
    if (!$cur_minute) {
      $cur_hour -= 1;
    }

    try {

      $folder_setting = SysCore::str_trim_slash($sensor->s3_bucket_address);
      $directory = $folder_setting . '/' . $cur_date . '/' . $cur_hour . '/';

      Storage::append($file_log, 'FOLDER= ' . $directory);

      $files = Storage::disk('sensors')->files($directory);

      if ($debug) {
        var_dump('FILE_LOG= ' . $file_log);
        var_dump('DATE= ' . $cur_date);
        var_dump('HOUR= ' . $cur_hour);
        var_dump('SETTING= ' . $folder_setting);
        var_dump('FOLDER= ' . $directory);
        var_dump('TOTAL_FILES= ' . count($files));
      }

      if (count($files)) {
        //desc
        $files = array_reverse($files);
        $count = 0;

        Storage::append($file_log, 'TOTAL FILES= ' . count($files));

        //step 1= photo check
        foreach ($files as $file) {

          Storage::append($file_log, '*************************************************************************'
            . 'STEP_01_' . date('Y_m_d_H_i_s') . '_' . SysCore::time_to_ms());
          Storage::append($file_log, 'FILE= ' . $file);

          if ($debug) {
            var_dump(SysCore::var_dump_break());
            var_dump('FILE= ' . $file);
          }

          $rfs = NULL;

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

          Storage::append($file_log, '*************************************************************************'
            . 'STEP_02_' . date('Y_m_d_H_i_s') . '_' . SysCore::time_to_ms());
          Storage::append($file_log, 'FILE= VALID');

          //no duplicate
          $keyword = SysRobo::photo_name_query($file);

          if ($debug) {
            var_dump('KEYWORD= ' . $keyword);
          }

          $count++;

          //check exist
          $rfs = RestaurantFoodScan::where('restaurant_id', $sensor->id)
            ->where('photo_name', $file)
            ->first();
          if (!$rfs) {
            Storage::append($file_log, '*************************************************************************'
              . 'STEP_03_' . date('Y_m_d_H_i_s') . '_' . SysCore::time_to_ms());
            Storage::append($file_log, 'FILE= NEW');

            $status = 'new';

            $rows = RestaurantFoodScan::where('photo_name', 'LIKE', $keyword)
              ->where('restaurant_id', $sensor->id)
              ->get();
            if (count($rows)) {
              $status = 'duplicated';
            }

            //1
            $rfs = RestaurantFoodScan::create([
              'restaurant_id' => $sensor->id,

              'local_storage' => 1,
              'photo_name' => $file,
              'photo_ext' => 'jpg',
              'time_photo' => date('Y-m-d H:i:s'),

              'status' => $status,
            ]);

            if ($debug) {
              var_dump('PHOTO_SAVE= ' . $rfs->id);
            }

          } else {

            Storage::append($file_log, '*************************************************************************'
              . 'STEP_03_' . date('Y_m_d_H_i_s') . '_' . SysCore::time_to_ms());
            Storage::append($file_log, 'FILE= CREATED');
          }

          if ($debug) {
            var_dump('PHOTO_STATUS= ' . $rfs->status);
          }

          Storage::append($file_log, 'STATUS= ' . $rfs->status);

          if (in_array($rfs->status, ['checked', 'failed'])) {
            break;
          }

          if ($rfs->status == 'new') {
            if ($debug) {
              var_dump('PHOTO_SCANNED= YES');
              var_dump('PHOTO_STATUS= ' . $status);
            }

            //robot setting
            Storage::append($file_log, '*************************************************************************'
              . 'STEP_04_' . date('Y_m_d_H_i_s') . '_' . SysCore::time_to_ms());
            Storage::append($file_log, 'FILE= GET SETTING');

            //model 1
            $api_key = 'uYUCzsUbWxWRrO15iar5';
            $dataset = 'missing-dish-ingredients';
            $version = SysCore::get_sys_setting('rbf_dataset_ver');

            //model 2
            $restaurant = $sensor->get_parent();
            if ($restaurant->model_scan && !empty($restaurant->model_name) && !empty($restaurant->model_version)) {
              $dataset = SysCore::str_trim_slash($restaurant->model_name);
              $version = $restaurant->model_version;
            }

            //file 1024 create
            Storage::append($file_log, '*************************************************************************'
              . 'STEP_05_' . date('Y_m_d_H_i_s') . '_' . SysCore::time_to_ms());
            Storage::append($file_log, 'FILE= CREATE 1024');

            //img_1024
            $img_url = $rfs->get_photo(); //$rfs->photo_1024_create();

            //robot scan start
            Storage::append($file_log, '*************************************************************************'
              . 'STEP_06_' . date('Y_m_d_H_i_s') . '_' . SysCore::time_to_ms());
            Storage::append($file_log, 'FILE= SCAN START');

            //time_scan
            if (empty($rfs->time_scan)) {
              $rfs->update([
                'time_scan' => date('Y-m-d H:i:s'),
              ]);
            }

            $datas = SysRobo::photo_scan([
              'img_url' => $img_url,

              'api_key' => $api_key,
              'dataset' => $dataset,
              'version' => $version,

              'confidence' => SysRobo::_RBF_CONFIDENCE,
              'overlap' => SysRobo::_RBF_OVERLAP,
              'max_objects' => SysRobo::_RBF_MAX_OBJECTS,

              'debug' => $debug,
            ]);

            $no_data = false;
            if (!count($datas) || !$datas['status']
              || ($datas['status'] && (!isset($datas['result']['predictions'])) || !count($datas['result']['predictions']))) {
              $no_data = true;
            }

            //robot scan end
            Storage::append($file_log, '*************************************************************************'
              . 'STEP_07_' . date('Y_m_d_H_i_s') . '_' . SysCore::time_to_ms());
            Storage::append($file_log, 'FILE= SCAN END');

            $rfs->update([
              'status' => $no_data ? 'failed' : 'scanned',
              'total_seconds' => isset($datas['result']['time']) ? $datas['result']['time'] : 0,
              'rbf_api' => json_encode($datas),
              'rbf_version' => json_encode([
                'dataset' => $dataset,
                'version' => $version,
              ]),
            ]);

//            $rfs->rfs_photo_predict($pars);

            if (!$no_data) {
              //system predict start
              Storage::append($file_log, '*************************************************************************'
                . 'STEP_08_' . date('Y_m_d_H_i_s') . '_' . SysCore::time_to_ms());
              Storage::append($file_log, 'FILE= PREDICT START');

              //model 1
              $api_result = (array)$datas; //json_decode($rfs->rbf_api, true);
              $predictions = isset($api_result['result']) && isset($api_result['result']['predictions'])
                ? (array)$api_result['result']['predictions'] : [];
              if (!count($predictions)) {
                //old
                $predictions = isset($api_result['predictions']) && isset($api_result['predictions'])
                  ? (array)$api_result['predictions'] : [];
              }

              $notification = isset($pars['notification']) ? (bool)$pars['notification'] : true;

              if (!$notification) {
                $rfs->update([
                  'missing_notify' => 1,
                ]);
              }

              //find foods
              $foods = SysRobo::foods_find([
                'predictions' => $predictions,
                'restaurant_parent_id' => $sensor->restaurant_parent_id,

                'debug' => $debug,
              ]);

              $no_food = true;

              if (count($foods)) {
                //find food 1
                $foods = SysRobo::foods_valid($foods, [
                  'predictions' => $predictions,

                  'debug' => $debug,
                ]);

                if (count($foods)) {
                  Storage::append($file_log, '*************************************************************************'
                    . 'STEP_09_' . date('Y_m_d_H_i_s') . '_' . SysCore::time_to_ms());
                  Storage::append($file_log, 'FILE= FOOD FOUND');

                  //valid food
                  $food = Food::find($foods['food']);

                  //find ingredients found
                  $ingredients_found = SysRobo::ingredients_found($food, [
                    'predictions' => $predictions,
                    'restaurant_parent_id' => $sensor->restaurant_parent_id,

                    'debug' => $debug
                  ]);

                  //find ingredients missing
                  $ingredients_missing = SysRobo::ingredients_missing($food, [
                    'predictions' => $predictions,
                    'restaurant_parent_id' => $sensor->restaurant_parent_id,
                    'ingredients_found' => $ingredients_found,

                    'debug' => $debug
                  ]);

                  if (count($ingredients_missing) < 5) {
                    Storage::append($file_log, '*************************************************************************'
                      . 'STEP_10_' . date('Y_m_d_H_i_s') . '_' . SysCore::time_to_ms());
                    Storage::append($file_log, 'FILE= INGRDEIENT MISSING ' . count($ingredients_missing));

                    $no_food = false;

                    //find category
                    $food_category = $food->get_category([
                      'restaurant_parent_id' => $sensor->restaurant_parent_id,
                    ]);

                    $rfs->update([
                      'status' => 'checked',

                      'food_id' => $food->id,
                      'food_category_id' => $food_category ? $food_category->id : 0,
                      'confidence' => $foods['confidence'],
                      'rbf_confidence' => $foods['confidence'],
                      'found_by' => 'rbf',
                      'rbf_predict' => $food->id,
                    ]);

                    $rfs->rfs_ingredients_missing($food, $ingredients_missing, $notification);
                  }
                }
              }

              if ($no_food) {
                $rfs->update([
                  'status' => 'failed',
                ]);
              }

              Storage::append($file_log, '*************************************************************************'
                . 'STEP_11_' . date('Y_m_d_H_i_s') . '_' . SysCore::time_to_ms());
              Storage::append($file_log, 'FILE= PREDICT END');
            }

            //time_end
            if (empty($rfs->time_end)) {
              $rfs->update([
                'time_end' => date('Y-m-d H:i:s'),
              ]);
            }
          }

          Storage::append($file_log, '***************************************************************************'
            . 'FINAL_' . date('Y_m_d_H_i_s') . '_' . SysCore::time_to_ms());

          //latest file
          break;
        }
      }

    } catch (\Exception $e) {

      SysCore::log_sys_bug([
        'type' => 'photo_get',
        'line' => $e->getLine(),
        'file' => $e->getFile(),
        'message' => $e->getMessage(),
        'params' => json_encode($e),
      ]);
    }

    $sensor->update([
      's3_checking' => 0,
    ]);
  }

  public static function photo_get_old($pars = [])
  {
    //pars
    $debug = isset($pars['debug']) ? (bool)$pars['debug'] : false;
    $limit = isset($pars['limit']) ? (int)$pars['limit'] : 1;
    $page = isset($pars['page']) ? (int)$pars['page'] : 1;

    //run
    $sensor = Restaurant::where('deleted', 0)
      ->where('restaurant_parent_id', '>', 0)
      ->where('s3_bucket_name', '<>', NULL)
      ->where('s3_bucket_address', '<>', NULL)
      ->orderBy('id', 'asc')
      ->paginate($limit, ['*'], 'page', $page)
      ->first();

    $file_log = 'public/logs/' . date('Y-m-d') . '/' . date('H') . '/cron_photo_get_old_' . $sensor->id . '.log';
    Storage::append($file_log, SysCore::var_dump_break());
    Storage::append($file_log, '***************************************************************************'
      . 'START_' . date('Y_m_d_H_i_s') . '_' . SysCore::time_to_ms());

    if (!$sensor) {
      return false;
    }

    if ($debug) {
      var_dump(SysCore::var_dump_break());
      var_dump('SENSOR= ' . $sensor->name . ' - ID= ' . $sensor->id);
    }

    $cur_date = date('Y-m-d');
    $cur_hour = (int)date('H');

    if (isset($pars['date']) && !empty($pars['date'])) {
      $cur_date = $pars['date'];
    }
    if (isset($pars['hour'])) {
      $cur_hour = (int)$pars['hour'] ? (int)$pars['hour'] : $pars['hour'];
    }

    try {

      $folder_setting = SysCore::str_trim_slash($sensor->s3_bucket_address);
      $directory = $folder_setting . '/' . $cur_date . '/' . $cur_hour . '/';

      Storage::append($file_log, 'FOLDER= ' . $directory);

      $files = Storage::disk('sensors')->files($directory);

      if ($debug) {
        var_dump('FILE_LOG= ' . $file_log);
        var_dump('DATE= ' . $cur_date);
        var_dump('HOUR= ' . $cur_hour);
        var_dump('SETTING= ' . $folder_setting);
        var_dump('FOLDER= ' . $directory);
        var_dump('TOTAL_FILES= ' . count($files));
      }

      if (count($files)) {
        //desc
        $files = array_reverse($files);
        $count = 0;

        Storage::append($file_log, 'TOTAL FILES= ' . count($files));

        //step 1= photo check
        foreach ($files as $file) {

          Storage::append($file_log, '*************************************************************************'
            . 'STEP_01_' . date('Y_m_d_H_i_s') . '_' . SysCore::time_to_ms());
          Storage::append($file_log, 'FILE= ' . $file);

          if ($debug) {
            var_dump(SysCore::var_dump_break());
            var_dump('FILE= ' . $file);
          }

          $rfs = NULL;

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

          Storage::append($file_log, '*************************************************************************'
            . 'STEP_02_' . date('Y_m_d_H_i_s') . '_' . SysCore::time_to_ms());
          Storage::append($file_log, 'FILE= VALID');

          //no duplicate
          $keyword = SysRobo::photo_name_query($file);

          if ($debug) {
            var_dump('KEYWORD= ' . $keyword);
          }

          $count++;

          //check exist
          $rfs = RestaurantFoodScan::where('restaurant_id', $sensor->id)
            ->where('photo_name', $file)
            ->first();
          if (!$rfs) {
            Storage::append($file_log, '*************************************************************************'
              . 'STEP_03_' . date('Y_m_d_H_i_s') . '_' . SysCore::time_to_ms());
            Storage::append($file_log, 'FILE= NEW');

            $status = 'new';

            $rows = RestaurantFoodScan::where('photo_name', 'LIKE', $keyword)
              ->where('restaurant_id', $sensor->id)
              ->get();
            if (count($rows)) {
              $status = 'duplicated';
            }

            //1
            $rfs = RestaurantFoodScan::create([
              'restaurant_id' => $sensor->id,

              'local_storage' => 1,
              'photo_name' => $file,
              'photo_ext' => 'jpg',
              'time_photo' => date('Y-m-d H:i:s'),

              'status' => $status,
            ]);

            if ($debug) {
              var_dump('PHOTO_SAVE= ' . $rfs->id);
            }

          } else {

            Storage::append($file_log, '*************************************************************************'
              . 'STEP_03_' . date('Y_m_d_H_i_s') . '_' . SysCore::time_to_ms());
            Storage::append($file_log, 'FILE= CREATED');
          }

          if ($debug) {
            var_dump('PHOTO_STATUS= ' . $rfs->status);
          }

          Storage::append($file_log, 'STATUS= ' . $rfs->status);

          if (in_array($rfs->status, ['checked', 'failed'])) {
            break;
          }

          if ($rfs->status == 'new') {
            if ($debug) {
              var_dump('PHOTO_SCANNED= YES');
              var_dump('PHOTO_STATUS= ' . $status);
            }

            //robot setting
            Storage::append($file_log, '*************************************************************************'
              . 'STEP_04_' . date('Y_m_d_H_i_s') . '_' . SysCore::time_to_ms());
            Storage::append($file_log, 'FILE= GET SETTING');

            //model 1
            $api_key = 'uYUCzsUbWxWRrO15iar5';
            $dataset = 'missing-dish-ingredients';
            $version = SysCore::get_sys_setting('rbf_dataset_ver');

            //model 2
            $restaurant = $sensor->get_parent();
            if ($restaurant->model_scan && !empty($restaurant->model_name) && !empty($restaurant->model_version)) {
              $dataset = SysCore::str_trim_slash($restaurant->model_name);
              $version = $restaurant->model_version;
            }

            //file 1024 create
            Storage::append($file_log, '*************************************************************************'
              . 'STEP_05_' . date('Y_m_d_H_i_s') . '_' . SysCore::time_to_ms());
            Storage::append($file_log, 'FILE= CREATE 1024');

            //img_1024
            $img_url = $rfs->get_photo(); //$rfs->photo_1024_create();

            //robot scan start
            Storage::append($file_log, '*************************************************************************'
              . 'STEP_06_' . date('Y_m_d_H_i_s') . '_' . SysCore::time_to_ms());
            Storage::append($file_log, 'FILE= SCAN START');

            //time_scan
            if (empty($rfs->time_scan)) {
              $rfs->update([
                'time_scan' => date('Y-m-d H:i:s'),
              ]);
            }

            $datas = SysRobo::photo_scan([
              'img_url' => $img_url,

              'api_key' => $api_key,
              'dataset' => $dataset,
              'version' => $version,

              'confidence' => SysRobo::_RBF_CONFIDENCE,
              'overlap' => SysRobo::_RBF_OVERLAP,
              'max_objects' => SysRobo::_RBF_MAX_OBJECTS,

              'debug' => $debug,
            ]);

            $no_data = false;
            if (!count($datas) || !$datas['status']
              || ($datas['status'] && (!isset($datas['result']['predictions'])) || !count($datas['result']['predictions']))) {
              $no_data = true;
            }

            //robot scan end
            Storage::append($file_log, '*************************************************************************'
              . 'STEP_07_' . date('Y_m_d_H_i_s') . '_' . SysCore::time_to_ms());
            Storage::append($file_log, 'FILE= SCAN END');

            $rfs->update([
              'status' => $no_data ? 'failed' : 'scanned',
              'total_seconds' => isset($datas['result']['time']) ? $datas['result']['time'] : 0,
              'rbf_api' => json_encode($datas),
              'rbf_version' => json_encode([
                'dataset' => $dataset,
                'version' => $version,
              ]),
            ]);

//            $rfs->rfs_photo_predict($pars);

            if (!$no_data) {
              //system predict start
              Storage::append($file_log, '*************************************************************************'
                . 'STEP_08_' . date('Y_m_d_H_i_s') . '_' . SysCore::time_to_ms());
              Storage::append($file_log, 'FILE= PREDICT START');

              //model 1
              $api_result = (array)$datas; //json_decode($rfs->rbf_api, true);
              $predictions = isset($api_result['result']) && isset($api_result['result']['predictions'])
                ? (array)$api_result['result']['predictions'] : [];
              if (!count($predictions)) {
                //old
                $predictions = isset($api_result['predictions']) && isset($api_result['predictions'])
                  ? (array)$api_result['predictions'] : [];
              }

              $notification = false; //isset($pars['notification']) ? (bool)$pars['notification'] : true;

              if (!$notification) {
                $rfs->update([
                  'missing_notify' => 1,
                ]);
              }

              //find foods
              $foods = SysRobo::foods_find([
                'predictions' => $predictions,
                'restaurant_parent_id' => $sensor->restaurant_parent_id,

                'debug' => $debug,
              ]);

              $no_food = true;

              if (count($foods)) {
                //find food 1
                $foods = SysRobo::foods_valid($foods, [
                  'predictions' => $predictions,

                  'debug' => $debug,
                ]);

                if (count($foods)) {
                  Storage::append($file_log, '*************************************************************************'
                    . 'STEP_09_' . date('Y_m_d_H_i_s') . '_' . SysCore::time_to_ms());
                  Storage::append($file_log, 'FILE= FOOD FOUND');

                  //valid food
                  $food = Food::find($foods['food']);

                  //find ingredients found
                  $ingredients_found = SysRobo::ingredients_found($food, [
                    'predictions' => $predictions,
                    'restaurant_parent_id' => $sensor->restaurant_parent_id,

                    'debug' => $debug
                  ]);

                  //find ingredients missing
                  $ingredients_missing = SysRobo::ingredients_missing($food, [
                    'predictions' => $predictions,
                    'restaurant_parent_id' => $sensor->restaurant_parent_id,
                    'ingredients_found' => $ingredients_found,

                    'debug' => $debug
                  ]);

                  if (count($ingredients_missing) < 5) {
                    Storage::append($file_log, '*************************************************************************'
                      . 'STEP_10_' . date('Y_m_d_H_i_s') . '_' . SysCore::time_to_ms());
                    Storage::append($file_log, 'FILE= INGRDEIENT MISSING ' . count($ingredients_missing));

                    $no_food = false;

                    //find category
                    $food_category = $food->get_category([
                      'restaurant_parent_id' => $sensor->restaurant_parent_id,
                    ]);

                    $rfs->update([
                      'status' => 'checked',

                      'food_id' => $food->id,
                      'food_category_id' => $food_category ? $food_category->id : 0,
                      'confidence' => $foods['confidence'],
                      'rbf_confidence' => $foods['confidence'],
                      'found_by' => 'rbf',
                      'rbf_predict' => $food->id,
                    ]);

                    $rfs->rfs_ingredients_missing($food, $ingredients_missing, $notification);
                  }
                }
              }

              if ($no_food) {
                $rfs->update([
                  'status' => 'failed',
                ]);
              }

              Storage::append($file_log, '*************************************************************************'
                . 'STEP_11_' . date('Y_m_d_H_i_s') . '_' . SysCore::time_to_ms());
              Storage::append($file_log, 'FILE= PREDICT END');
            }

            //time_end
            if (empty($rfs->time_end)) {
              $rfs->update([
                'time_end' => date('Y-m-d H:i:s'),
              ]);
            }
          }

          Storage::append($file_log, '***************************************************************************'
            . 'FINAL_' . date('Y_m_d_H_i_s') . '_' . SysCore::time_to_ms());

          //latest file
          break;
        }
      }

    } catch (\Exception $e) {

      SysCore::log_sys_bug([
        'type' => 'photo_get_old',
        'line' => $e->getLine(),
        'file' => $e->getFile(),
        'message' => $e->getMessage(),
        'params' => json_encode($e),
      ]);
    }
  }

  public static function photo_handle($pars = [])
  {
    //pars
    $debug = isset($pars['debug']) ? (bool)$pars['debug'] : false;
    $limit = isset($pars['limit']) ? (int)$pars['limit'] : 1;
    $page = isset($pars['page']) ? (int)$pars['page'] : 1;

    //run
    $sensor = Restaurant::where('deleted', 0)
      ->where('restaurant_parent_id', '>', 0)
      ->where('s3_bucket_name', '<>', NULL)
      ->where('s3_bucket_address', '<>', NULL)
      ->orderBy('id', 'asc')
      ->paginate($limit, ['*'], 'page', $page)
      ->first();

    if (!$sensor) {
      return false;
    }

    if ($debug) {
      var_dump(SysCore::var_dump_break());
      var_dump('SENSOR= ' . $sensor->name . ' - ID= ' . $sensor->id);
    }

    try {

      $file_log = 'public/logs/cron_photo_handle_' . $sensor->id . '.log';
      Storage::append($file_log, SysCore::var_dump_break());
      Storage::append($file_log, 'AT_' . date('Y_m_d_H_i_s'));

      $rows = RestaurantFoodScan::where('deleted', 0)
        ->where('restaurant_id', $sensor->id)
        ->whereDate('time_photo', date('Y-m-d'))
        ->where('status', 'new')
        ->where('rbf_api', NULL)
        ->orderBy('id', 'desc')
        ->limit(1)
        ->get();

      Storage::append($file_log, 'TOTAL ROWS= ' . count($rows));

      if ($debug) {
        var_dump('FILE_LOG= ' . $file_log);
        var_dump('TOTAL_FILES= ' . count($rows));
      }

      if (count($rows)) {
        foreach ($rows as $rfs) {

          Storage::append($file_log, 'START RFS= ' . $rfs->id);

          if ($debug) {
            var_dump('RFS= ' . $rfs->id);
          }

          $rfs->rfs_photo_scan([
            'created' => true,

            'debug' => $debug,
          ]);

          Storage::append($file_log, 'END RFS= ' . $rfs->id);
        }
      }

    } catch (\Exception $e) {

      SysCore::log_sys_bug([
        'type' => 'photo_get',
        'line' => $e->getLine(),
        'file' => $e->getFile(),
        'message' => $e->getMessage(),
        'params' => json_encode($e),
      ]);
    }
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
      . '.jpg%';

    return $keyword;
  }

  public static function photo_duplicate($pars = [])
  {
    //pars
    $debug = isset($pars['debug']) ? (bool)$pars['debug'] : false;

    //date
    $date_from = date('Y-m-01');
    $date_to = date('Y-m-t');

    if (isset($pars['date_from']) && !empty($pars['date_from'])) {
      $date_from = $pars['date_from'];
    }
    if (isset($pars['date_to']) && !empty($pars['date_to'])) {
      $date_to = $pars['date_to'];
    }

    $sensors = Restaurant::where('deleted', 0)
      ->where('restaurant_parent_id', '>', 0)
      ->where('s3_bucket_name', '<>', NULL)
      ->where('s3_bucket_address', '<>', NULL)
      ->orderBy('id', 'asc')
      ->get();

    if (count($sensors)) {
      foreach ($sensors as $sensor) {
        $file_log = 'public/logs/cron_photo_duplicate_' . $sensor->id . '.log';
        Storage::append($file_log, SysCore::var_dump_break());
        Storage::append($file_log, 'AT_' . date('Y_m_d_H_i_s'));

        $select = RestaurantFoodScan::query('restaurant_food_scans')
          ->where('restaurant_food_scans.restaurant_id', $sensor->id)
          ->where('status', '<>', 'duplicated')
          ->whereDate('restaurant_food_scans.time_photo', '>=', $date_from)
          ->whereDate('restaurant_food_scans.time_photo', '<=', $date_to)
          ->orderBy('id', 'asc');

        if ($debug) {
          var_dump(SysCore::var_dump_break());
          var_dump('QUERY=');
          var_dump(SysCore::str_db_query($select));
        }

        $rows = $select->get();

        if ($debug) {
          var_dump('TOTAL PHOTOS= ' . count($rows));
        }

        $ids_checked = [];
        $main_status_invalids = [
          'duplicated', 'failed', 'scanned',
        ];

        if (count($rows)) {

          //reset
          $select->update([
            'photo_main' => 0,
          ]);

          foreach ($rows as $row) {
            if ($debug) {
              var_dump(SysCore::var_dump_break());
              var_dump('ID= ' . $row->id);
            }

            //checked
            if (in_array($row->id, $ids_checked)) {
              continue;
            }

            //1024_
            $temps = explode('/', $row->photo_name);
            $photo_name = $temps[count($temps) - 1];
            if (substr($photo_name, 0, 5) == '1024_') {

              $row->update([
                'deleted' => 1,
              ]);

              continue;
            }

            $ids_checked[] = $row->id;

            if ($debug) {
              var_dump('ID START CHECK= ' . $row->id);
            }

            $keyword = SysRobo::photo_name_query($row->photo_name);

            if ($debug) {
              var_dump($row->photo_name);
              var_dump($keyword);
            }

            //find duplicate
            $duplicates = RestaurantFoodScan::where('deleted', 0)
              ->where('status', '<>', 'duplicated')
              ->where('photo_name', 'LIKE', $keyword)
              ->where('id', '<>', $row->id)
              ->orderBy('food_id', 'desc')
              ->get();

            if ($debug) {
              var_dump('TOTAL DUPLICATED= ' . count($duplicates));
            }

            //check missing
            $id_main = 0;
            if ($row->food_id) {

              if (!empty($row->missing_ids)) {

                $temp1 = RestaurantFoodScan::where('deleted', 0)
                  ->where('status', '<>', 'duplicated')
                  ->where('photo_name', 'LIKE', $keyword)
                  ->where('id', '<>', $row->id)
                  ->where('food_id', $row->food_id)
                  ->where('missing_ids', NULL)
                  ->orderBy('food_id', 'desc')
                  ->orderBy('id', 'asc')
                  ->first();

                if ($temp1) {
                  $id_main = $temp1->id;
                } else {
                  $id_main = $row->id;
                }

              } else {
                $id_main = $row->id;
              }
            }

            $id_duplicates = [];
            $need_compare = false;

            if (count($duplicates)) {

              $need_compare = true;

              foreach ($duplicates as $rfs) {

                $ids_checked[] = $rfs->id;

                if ($debug) {
                  var_dump('ID DUPLICATED= ' . $rfs->id);
                }

                if (!$id_main && empty($rfs->missing_ids)) {
                  $id_main = $rfs->id;
                }

                $id_duplicates[] = $rfs->id;
              }
            } else {
              //main
              $row->update([
                'photo_main' => 1,
              ]);
              if (in_array($row->status, $main_status_invalids)) {
                $row->update([
                  'status' => 'checked',
                ]);
              }
            }

            //main or not
            if ($need_compare) {
              if (!$id_main || $id_main == $row->id) {
                $row->update([
                  'photo_main' => 1,
                ]);
                if (in_array($row->status, $main_status_invalids)) {
                  $row->update([
                    'status' => 'checked',
                  ]);
                }

                if (count($duplicates)) {
                  foreach ($duplicates as $rfs) {
                    $rfs->update([
                      'status' => 'duplicated',
                    ]);
                  }
                }
              } else {

                if ($id_main) {

                  $row->update([
                    'status' => 'duplicated',
                  ]);

                  foreach ($duplicates as $rfs) {
                    if ($id_main == $rfs->id) {

                      $rfs->update([
                        'photo_main' => 1,
                      ]);
                      if (in_array($rfs->status, $main_status_invalids)) {
                        $rfs->update([
                          'status' => 'checked',
                        ]);
                      }

                    } else {

                      $rfs->update([
                        'status' => 'duplicated',
                      ]);
                    }
                  }
                }
              }
            }
          }
        }
      }
    }
  }

  public static function photo_sync($pars = [])
  {
    $s3_region = SysCore::get_sys_setting('s3_region');

    $directories = SysRobo::s3_bucket_folder();
    foreach ($directories as $restaurant => $directory) {
      $file_log = 'public/logs/cron_photo_sync_' . $restaurant . '.log';
      Storage::append($file_log, SysCore::var_dump_break());
      $count = 0;

      $localDisk = Storage::disk('sensors');
      $s3Disk = Storage::disk($directory['bucket']);

      $files = $localDisk->allFiles($directory['folder']);
      foreach ($files as $file) {

        $status = $s3Disk->put($file, $localDisk->get($file));
        if ($status) {

          $rfs = RestaurantFoodScan::where('photo_name', $file)
            ->first();
          if ($rfs) {

            $sensor = $rfs->get_restaurant();
            $img_url = "https://s3.{$s3_region}.amazonaws.com/{$sensor->s3_bucket_name}/{$file}";

            if (@getimagesize($img_url)) {

              $rfs->update([
                'local_storage' => 0,
                'photo_url' => $img_url,
              ]);

              $count++;
            }
          }
        }

        Storage::append($file_log, 'FILE_SYNC_STATUS= ' . $status);
        Storage::append($file_log, 'FILE_SYNC_DATA= ' . $file);
      }

      Storage::append($file_log, 'TOTAL_FILES= ' . $count);
    }
  }

  public static function photo_clear($pars = [])
  {
    $directories = SysRobo::s3_bucket_folder();
    foreach ($directories as $restaurant => $directory) {
      $file_log = 'public/logs/cron_photo_clear_' . $restaurant . '.log';
      Storage::append($file_log, SysCore::var_dump_break());
      $count = 0;

      $localDisk = Storage::disk('sensors');
      $s3Disk = Storage::disk($directory['bucket']);

      $date = date('Y-m-d', strtotime("-3 days"));
      $dir = "{$directory['folder']}SENSOR/1/{$date}/";

      $files = $localDisk->allFiles($dir);
      foreach ($files as $file) {
        $storagePath = public_path('sensors') . '/' . $file;

        if (is_file($storagePath)) {
          unlink($storagePath);

          $count++;
        }
      }

      Storage::append($file_log, 'TOTAL_FILES= ' . $count);
    }
  }

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
      $img_url = SysCore::local_img_url();
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

    $type = isset($pars['type']) ? $pars['type'] : NULL; //modal_testing

    $server_url = 'https://detect.roboflow.com'; //robot

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


    //ec2 clone
    //localhost
    if (App::environment() == 'local') {
      $datas['server_url'] = 'http://52.77.242.51:9001'; //IP public
      $datas['img_url'] = SysCore::local_img_url();;
    } else {
      $datas['server_url'] = 'http://172.31.42.57:9001'; //IP private
    }

    if ($debug) {
      var_dump('rbf prepare...');
      var_dump($datas);
    }

    $datas['server_url'] = 'http://171.244.46.137:9001';
//    $datas['server_url'] = 'http://52.77.242.51:9001';

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
      'http' => array(
        'header' => "Content-type: application/x-www-form-urlencoded\r\n",
        'method' => 'POST'
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

    if ($debug) {
      var_dump('<br />');
      var_dump('sensor: ' . $restaurant_parent_id);
    }

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

      $rbf_confidence = (int)($prediction['confidence'] * 100);
      $rbf_width = (int)$prediction['width'];
      $class = strtolower(trim($prediction['class']));

      $item = RestaurantFood::query('restaurant_foods')
        ->select('foods.id')
        ->leftJoin('foods', 'foods.id', '=', 'restaurant_foods.food_id') //serve
        ->where('restaurant_parent_id', $restaurant_parent_id)
        ->where('foods.deleted', 0)
        ->where('restaurant_foods.deleted', 0)
        ->where('restaurant_foods.confidence', '<=', $rbf_confidence) //confidence
        ->whereRaw('LOWER(foods.name) LIKE ?', $class)
        ->first();

      if ($item && $item->id) {
        $food = Food::find($item->id);

        if ($debug) {
          var_dump('food found = ' . $food->name . ' - ID= ' . $food->id);
          var_dump('food confidence = ' . $rbf_confidence);
        }

        //check ingredient valid
        $valid_food = true;
        $food_ingredients = FoodIngredient::where('deleted', 0)
          ->where('food_id', $food->id)
          ->where('restaurant_parent_id', $restaurant_parent_id)
          ->count();
        if (!$food_ingredients) {
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
          var_dump('ingredient count = ' . $food_ingredients);
          var_dump('ingredient valid = ' . $valid_food);
          var_dump('ingredient core = ' . $valid_core);
        }

        if ($valid_core && $valid_food) {
          $foods[] = [
            'food' => $food->id,
            'confidence' => $rbf_confidence,

            'width' => $rbf_width,
          ];
        }

        if ($valid_food && $rbf_confidence >= 90) {
          $food_temps[] = [
            'food' => $food->id,
            'confidence' => $rbf_confidence,
          ];
        }
      }

    }

    if ($debug) {
      var_dump('foods temps...');
      var_dump($food_temps);
    }

    if (!count($foods) && count($food_temps) == 1) {
//      $foods = $food_temps;
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

        if (!$count) {
//        if ($count < $core['ingredient_quantity']) {
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
    $food = null;

    if (count($temps)) {

      if (count($temps) > 1) {
        $a1 = [];
        $a2 = [];
        foreach ($temps as $key => $val) {
          $a1[$key] = $val['width'];
          $a2[$key] = $val['confidence'];
        }
        array_multisort($a1, SORT_DESC, $a2, SORT_DESC, $temps);
      }

      $temp = $temps[0];

      $food_id = $temp['food'];
      $food_confidence = $temp['confidence'];

      $food = Food::find($food_id);
    }

    if ($debug) {
      var_dump('food 1 found= ' . $food_id . ' - confidence= ' . $food_confidence);

      if ($food) {
        var_dump('food 1 name= ' . $food->name);
      }
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

  public static function burger_ingredient_chicken_beef($text)
  {
    if (strtolower(trim($text)) == 'beef buger'
      || strtolower(trim($text)) == 'beef burger'
      || strtolower(trim($text)) == 'grilled chicken') {
      $text = 'beef burger or grilled chicken';
    }

    return $text;
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

  public static function photo_notify($pars = [])
  {
//    return false;
    $debug = isset($pars['debug']) ? (bool)$pars['debug'] : false;

    $rows = RestaurantFoodScan::where('deleted', 0)
      ->where('food_id', '>', 0)
      ->where('status', 'checked')
      ->whereDate('time_photo', date('Y-m-d')) //today
      ->where('missing_notify', 0)
      ->where('missing_ids', '<>', NULL)
      ->where('rbf_api', '<>', NULL)
      ->orderBy('time_photo', 'desc')
      ->orderBy('id', 'asc')
      ->limit(3)
      ->get();

    if (count($rows)) {
      foreach ($rows as $rfs) {

        $food = $rfs->get_food();
        $sensor = $rfs->get_restaurant();
        $restaurant = $sensor->get_parent();
        $users = $sensor->get_users();

        $ingredients = $rfs->get_ingredients_missing();

        $rfs->update([
          'missing_notify' => 1,
        ]);

        $time = time() - strtotime($rfs->time_photo);
        if ($time > 60 * 5) { //in 5min
          continue;
        }

        //notify
        if (count($ingredients) && count($users)) {
          $live_group = $restaurant->get_food_live_group($food);

          foreach ($users as $user) {

            //live_group
            $valid_group = true;
            if ($live_group > 1 || $rfs->confidence < 85 || count($ingredients) > 3) {
              $valid_group = false;
            }
//            if ($live_group == 2 && count($ingredients) < 2 && $rfs->confidence > 85) {
//              $valid_group = true;
//            }
//            if ($user->is_super_admin()) {
//              $valid_group = true;
//            }

            //debug
            if ($debug) {
              if (!$user->is_dev()) {
                continue;
              }

              var_dump(SysCore::var_dump_break());
              var_dump('RFS= ' . $rfs->id);
            }

            //isset notify
            $notify = DB::table('notifications')
              ->distinct()
              ->where('notifiable_type', 'App\Models\User')
              ->where('notifiable_id', $user->id)
              ->where('restaurant_food_scan_id', $rfs->id)
              ->whereIn('type', ['App\Notifications\IngredientMissing'])
              ->orderBy('id', 'desc')
              ->limit(1)
              ->first();

            if ($debug) {
              if ($notify) {
                $notify->delete();
              }

              $valid_group = true;
            }

            if (!$valid_group || $notify) {
              continue;
            }

            //notify db
            Notification::sendNow($user, new IngredientMissing([
              'restaurant_food_scan_id' => $rfs->id,
            ]), ['database']);

            //temp off
            //notify mail
//        if ((int)$user->get_setting('missing_ingredient_alert_email')) {
//          $user->notify((new IngredientMissingMail([
//            'type' => 'ingredient_missing',
//            'restaurant_id' => $sensor->id,
//            'restaurant_food_scan_id' => $this->id,
//            'user' => $user,
//          ]))->delay([
//            'mail' => now()->addMinutes(5),
//          ]));
//        }

            //notify zalo
            SysZalo::send_rfs_note($user, 'ingredient_missing', $rfs);

            //notify db update
            $rows = $user->notifications()
              ->whereIn('type', ['App\Notifications\IngredientMissing'])
              ->where('data', 'LIKE', '%{"restaurant_food_scan_id":' . $rfs->id . '}%')
              ->where('restaurant_food_scan_id', 0)
              ->get();
            if (count($rows)) {
              foreach ($rows as $row) {
                $notify = SysNotification::find($row->id);
                if ($notify) {
                  $notify->update([
                    'restaurant_food_scan_id' => $rfs->id,
                    'restaurant_id' => $sensor->id,
                    'food_id' => $food ? $food->id : 0,
                    'object_type' => 'restaurant_food_scan',
                    'object_id' => $rfs->id,
                    'data' => json_encode([
                      'status' => 'valid'
                    ]),
                  ]);
                }
              }
            }
          }
        }

        if ($debug) {
          var_dump(SysCore::var_dump_break());
          var_dump('DONE...');
        }
      }
    }
  }
}
