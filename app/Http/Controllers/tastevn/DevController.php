<?php

namespace App\Http\Controllers\tastevn;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
//lib
use App\Api\SysCore;
use App\Api\SysRobo;
use App\Models\Restaurant;
use App\Models\RestaurantParent;
use App\Models\Food;
use App\Models\RestaurantFoodScan;

class DevController extends Controller
{
  protected $_viewer = null;

  public function __construct()
  {
    $this->middleware(function ($request, $next) {

      $this->_viewer = Auth::user();

      return $next($request);
    });

    $this->middleware('auth');
  }

  public function index(Request $request)
  {
    if (!$this->_viewer->is_dev()) {
      return redirect('/admin');
    }

    $pageConfigs = [
      'myLayout' => 'horizontal',
      'hasCustomizer' => false,
    ];

    return view('tastevn.pages.dev', ['pageConfigs' => $pageConfigs]);
  }

  public function photo_check(Request $request)
  {
    $values = $request->post();

    $sensor = isset($values['sensor']) ? (int)$values['sensor'] : 0;
    $restaurant = isset($values['restaurant']) ? (int)$values['restaurant'] : 0;
    $dates = isset($values['dates']) && !empty($values['dates'])
      ? SysCore::arr_date_range($values['dates']) : [];

//    return response()->json([
//      'values' => $values,
//      'dates' => $dates,
//    ], 422);

    $debug = true;
    $file_log = 'public/logs/dev_photo_check.log';
    $debug ? Storage::append($file_log, 'RUN_AT= ' . date('d_M_Y_H_i_s')) : SysCore::log_sys_failed();

    $restaurant = RestaurantParent::find($restaurant);
    $sensor = Restaurant::find($sensor);

    $sensors = [];
    $datas = [];
    $date_diff = 0;

    if ($sensor) {
      $restaurant = $sensor->get_parent();
    }
    if ($restaurant) {
      $sensors = $restaurant->get_sensors();
    }

    if (count($sensors) && count($dates)) {
      $date_diff = strtotime($dates['date_to']) - strtotime($dates['date_from']);
      $date_diff = round($date_diff / (60 * 60 * 24));

      foreach ($sensors as $sensor) {
        $sensor_folder = SysCore::str_trim_slash($sensor->s3_bucket_address);
        $date = $dates['date_from'];
        $arr = [];

        for ($i = 0; $i <= $date_diff; $i++) {
          if ($i) {
            $date = date('Y-m-d', strtotime($dates['date_from'] . ' +' . $i . ' days'));
          }

          //folder
          $folder_path = public_path('sensors') . '/' . $sensor_folder . '/' . $date;
          $folder_path = SysCore::os_slash_file($folder_path);

          $hours = [];
          if (is_dir($folder_path)) {
            $hours = scandir($folder_path);

            if (count($hours)) {
              unset($hours[array_search('.', $hours, true)]);
              unset($hours[array_search('..', $hours, true)]);
            }
          }

          if (count($hours)) {

          }

          $arr[] = [
            'date' => $date,
            'folder' => $sensor_folder . '/' . $date,
            'hours' => (array)$hours,
          ];
        }

        $datas[] = [
          'sensor_id' => $sensor->id,
          'sensor_name' => $sensor->name,
          'dates' => $arr,
        ];
      }
    }

    $debug ? Storage::append($file_log, 'PARAMS= ' . json_encode($values)) : SysCore::log_sys_failed();
    $debug ? Storage::append($file_log, 'RESTAURANT= ' . $restaurant->name) : SysCore::log_sys_failed();

    return response()->json([
      'datas' => $datas,

      'restaurant_id' => $restaurant ? $restaurant->id : 0,
    ], 200);
  }

  public function photo_check_hour_get(Request $request)
  {
    $values = $request->post();

    $sensor = isset($values['sensor']) ? (int)$values['sensor'] : 0;
    $hour = isset($values['hour']) ? (int)$values['hour'] : 0;
    $date = isset($values['date']) && !empty($values['date']) ? $values['date'] : NULL;
    $folder = isset($values['folder']) && !empty($values['folder']) ? $values['folder'] : NULL;

    $sensor = Restaurant::find($sensor);

    $debug = true;
    $file_log = 'public/logs/dev_photo_check_hour.log';
    $debug ? Storage::append($file_log, 'RUN_AT= ' . date('d_M_Y_H_i_s')) : SysCore::log_sys_failed();

    $status = false;

    try {

      //photo get
      $directory = $folder . '/' . $hour;
      $files = Storage::disk('sensors')->files($directory);

      if (count($files)) {
        $debug ? Storage::append($file_log, 'TOTAL_FILES= ' . count($files)) : SysCore::log_sys_failed();

        foreach ($files as $file) {
          $debug ? Storage::append($file_log, $file . ' => CHECKED') : SysCore::log_sys_failed();

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

          //check exist
          $rfs = RestaurantFoodScan::where('restaurant_id', $sensor->id)
            ->where('photo_name', $file)
            ->first();
          if ($rfs) {
            continue;
          }

          $debug ? Storage::append($file_log, $file . ' => NEW OR DUPLICATE???') : SysCore::log_sys_failed();

          //no duplicate
          $keyword = SysRobo::photo_name_query($file);
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
            'time_photo' => date('Y-m-d H:i:s'), //checking

            'status' => $status,
          ]);

          //duplicated
          if ($status == 'duplicated') {
            continue;
          }

          $debug ? Storage::append($file_log, $file . ' => NEW => SCANED') : SysCore::log_sys_failed();

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

          //img_1024
          $img_url = $rfs->get_photo(); //$rfs->photo_1024_create();

          //time_scan
          if (empty($rfs->time_scan)) {
            $rfs->update([
              'time_scan' => date('Y-m-d H:i:s'),
            ]);
          }

          //photo scan
          $datas = SysRobo::photo_scan([
            'img_url' => $img_url,

            'api_key' => $api_key,
            'dataset' => $dataset,
            'version' => $version,

            'confidence' => SysRobo::_RBF_CONFIDENCE,
            'overlap' => SysRobo::_RBF_OVERLAP,
            'max_objects' => SysRobo::_RBF_MAX_OBJECTS,

//            'debug' => $debug,
          ]);

          $no_data = false;
          if (!count($datas) || !$datas['status']
            || ($datas['status'] && (!isset($datas['result']['predictions'])) || !count($datas['result']['predictions']))) {
            $no_data = true;
          }

          $rfs->update([
            'status' => $no_data ? 'failed' : 'scanned',
            'total_seconds' => isset($datas['result']['time']) ? $datas['result']['time'] : 0,
            'rbf_api' => json_encode($datas),
            'rbf_version' => json_encode([
              'dataset' => $dataset,
              'version' => $version,
            ]),
          ]);

          if (!$no_data) {

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

//              'debug' => $debug,
            ]);

            $no_food = true;

            if (count($foods)) {
              //find food 1
              $foods = SysRobo::foods_valid($foods, [
                'predictions' => $predictions,

//                'debug' => $debug,
              ]);

              if (count($foods)) {

                //valid food
                $food = Food::find($foods['food']);

                //find ingredients found
                $ingredients_found = SysRobo::ingredients_found($food, [
                  'predictions' => $predictions,
                  'restaurant_parent_id' => $sensor->restaurant_parent_id,

//                  'debug' => $debug
                ]);

                //find ingredients missing
                $ingredients_missing = SysRobo::ingredients_missing($food, [
                  'predictions' => $predictions,
                  'restaurant_parent_id' => $sensor->restaurant_parent_id,
                  'ingredients_found' => $ingredients_found,

//                  'debug' => $debug
                ]);

                if (count($ingredients_missing) < 5) {

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
          }

          //time_end
          if (empty($rfs->time_end)) {
            $rfs->update([
              'time_end' => date('Y-m-d H:i:s'),
            ]);
          }

        }
      }

      $status = true;

    } catch (\Exception $e) {

      SysCore::log_sys_bug([
        'type' => 'dev_photo_check_get',
        'line' => $e->getLine(),
        'file' => $e->getFile(),
        'message' => $e->getMessage(),
        'params' => json_encode($values) . '_BUG_'.  json_encode($e),
      ]);
    }

    return response()->json([
      'datas' => $values,

      'status' => $status,
    ], 200);
  }

  public function photo_check_hour_sync(Request $request)
  {
    $values = $request->post();

    $sensor = isset($values['sensor']) ? (int)$values['sensor'] : 0;
    $hour = isset($values['hour']) ? (int)$values['hour'] : 0;
    $date = isset($values['date']) && !empty($values['date']) ? $values['date'] : NULL;
    $folder = isset($values['folder']) && !empty($values['folder']) ? $values['folder'] : NULL;

    $sensor = Restaurant::find($sensor);
    $restaurant = $sensor->get_parent();

    $debug = true;
    $file_log = 'public/logs/dev_photo_check_hour.log';
    $debug ? Storage::append($file_log, 'RUN_AT= ' . date('d_M_Y_H_i_s')) : SysCore::log_sys_failed();

    $status = false;

    switch ($restaurant->id) {
      case 1:
        $file_system_disk = 's3_bucket_cargo';
        break;

      case 2:
      case 5:
      case 6:
      case 7:

      $file_system_disk = 's3_bucket_deli';
        break;

      case 3:
        $file_system_disk = 's3_bucket_market';
        break;

      case 4:
        $file_system_disk = 's3_bucket_poison';
        break;

      default:
      $file_system_disk = '';
    }

    if (empty($file_system_disk)) {

      return response()->json([
        'datas' => $values,

        'status' => $status,
      ], 200);
    }

    $debug ? Storage::append($file_log, SysCore::var_dump_break()) : SysCore::log_sys_failed();
    $debug ? Storage::append($file_log, 'BUCKET= ' . $file_system_disk) : SysCore::log_sys_failed();

    try {

      //photo sync
      $s3_region = SysCore::get_sys_setting('s3_region');
      $directory = '/' . $folder . '/' . $hour . '/';

      $debug ? Storage::append($file_log, 'DIRECTORY= ' . $directory) : SysCore::log_sys_failed();

      $localDisk = Storage::disk('sensors');
      $s3Disk = Storage::disk($file_system_disk);

      $files = $localDisk->allFiles($directory);

      $debug ? Storage::append($file_log, 'TOTAL_FILES= ' . count($files)) : SysCore::log_sys_failed();

      if (count($files)) {
        foreach ($files as $file) {
          $debug ? Storage::append($file_log, $file . ' => CHECKING') : SysCore::log_sys_failed();

          $status = $s3Disk->put($file, $localDisk->get($file));
          if ($status) {

            $rfs = RestaurantFoodScan::where('photo_name', $file)
              ->where('local_storage', 1)
              ->first();
            if ($rfs) {

              $sensor = $rfs->get_restaurant();
              $img_url = "https://s3.{$s3_region}.amazonaws.com/{$sensor->s3_bucket_name}/{$file}";

              if (@getimagesize($img_url)) {

                $rfs->update([
                  'local_storage' => 0,
                  'photo_url' => $img_url,
                ]);
              }
            }
          }

          $debug ? Storage::append($file_log, $file . ' => STATUS = ' . $status) : SysCore::log_sys_failed();
        }
      }

      $status = true;

    } catch (\Exception $e) {

      SysCore::log_sys_bug([
        'type' => 'dev_photo_check_sync',
        'line' => $e->getLine(),
        'file' => $e->getFile(),
        'message' => $e->getMessage(),
        'params' => json_encode($values) . '_BUG_'.  json_encode($e),
      ]);
    }

    return response()->json([
      'datas' => $values,

      'status' => $status,
    ], 200);
  }
}
