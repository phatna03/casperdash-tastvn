<?php

namespace App\Http\Controllers\tastevn;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

use Intervention\Image\ImageManagerStatic as Image;

use Illuminate\Support\Facades\Notification;
use App\Notifications\IngredientMissing;
use App\Notifications\IngredientMissingMail;

use Maatwebsite\Excel\Facades\Excel;
use App\Excel\ImportData;
use App\Excel\ExportData;
use App\Excel\ExportDataRfs;
use App\Excel\ExportRestaurantStatsDate;

use Validator;
use Aws\S3\S3Client;
use App\Api\SysApp;
use App\Api\SysAws;
use App\Api\SysCore;
use App\Api\SysDev;
use App\Api\SysKas;
use App\Api\SysRobo;
use App\Api\SysZalo;

use App\Models\User;
use App\Models\Restaurant;
use App\Models\RestaurantParent;
use App\Models\RestaurantAccess;
use App\Models\Food;
use App\Models\Ingredient;
use App\Models\FoodIngredient;
use App\Models\SysSetting;
use App\Models\RestaurantFood;
use App\Models\RestaurantFoodScan;
use App\Models\Comment;
use App\Models\FoodRecipe;
use App\Models\FoodCategory;
use App\Models\Log;
use App\Models\SysNotification;
use App\Models\Report;
use App\Models\KasBill;
use App\Models\KasBillOrder;
use App\Models\KasBillOrderItem;
use App\Models\KasItem;
use App\Models\KasRestaurant;
use App\Models\KasStaff;
use App\Models\KasTable;
use App\Models\KasWebhook;
use App\Models\ReportPhoto;
use App\Models\ReportFood;
use App\Models\ZaloUser;
use App\Models\ZaloUserSend;
use App\Models\RestaurantStatsDate;

use Zalo\Zalo;
use Zalo\Builder\MessageBuilder;
use Zalo\ZaloEndPoint;

class TesterController extends Controller
{

  public function index(Request $request)
  {
    echo '<pre>';

    $sys_app = new SysApp();

    $values = $request->all();

    $restaurant = RestaurantParent::find(6);
    $sensor = Restaurant::find(5);
    $rfs = RestaurantFoodScan::find(113618);
    $date = date('Y-m-d');
    $user = User::find(4);
    $kas = KasWebhook::find(539);
    $debug = true;
    $food = Food::find(29);
    $kas_restaurant = KasRestaurant::find(3);

    $s3_region = SysCore::get_sys_setting('s3_region');
    $s3_api_key = SysCore::get_sys_setting('s3_api_key');
    $s3_api_secret = SysCore::get_sys_setting('s3_api_secret');

    //=======================================================================================
    //=======================================================================================



    die('abc...');

    //=======================================================================================
    //=======================================================================================

//    $rfs->rfs_photo_predict([
//      'notification' => false,
//
//      'debug' => true,
//    ]);

//    $items = $this->checked_rfs_by_date([
//      'sensor_id' => $sensor->id,
//      'date_from' => '2024-07-01',
//      'date_to' => '2024-07-15',
//    ]);
//
//    $file = new ExportDataRfs();
//    $file->set_items($items);
//
//    return Excel::download($file, 'report_rfs_' . $sensor->id . '.xlsx');

//    $this->checked_photo_duplicated_and_not_found([
//      'limit' => 100,
//    ]);
//    $this->checked_notify_remove();
//    $this->checked_food_category_update();
//    $this->checked_zalo_user_get();
//    $this->kas_time_sheet([
//      'date_from' => '2024-09-09',
//      'date_to' => '2024-09-09',
//    ]);
//    $this->checked_photo_get_old_and_missing([
//      'hour' => 9,
//      'date' => '2024-08-06',
//
//      'limit' => 1,
//      'page' => 5,
//
////      'count_only' => true,
//      'debug' => true,
//    ]);

    //=======================================================================================
    //=======================================================================================

    echo '<br />';
    die('test ok...');

    $pageConfigs = [
      'myLayout' => 'horizontal',
      'hasCustomizer' => false,
    ];

    return view('tastevn.pages.tester', ['pageConfigs' => $pageConfigs]);
  }

  public function tester_post(Request $request)
  {
    $values = $request->post();

    $count = 0;


    return response()->json([
      'status' => true,
      'count' => $count,
    ]);
  }

  public function tester_photo_check(Request $request)
  {
    $values = $request->post();

    $validator = Validator::make($values, [
      'sensor' => 'required',
      'photo' => 'required|string',
    ]);
    if ($validator->fails()) {
      return response()->json($validator->errors(), 422);
    }

    $sensor = Restaurant::find((int)$values['sensor']);

    //save img from url
    $file_name = 'SENSOR_' . date('Y-m-d-H-i-s') . '-' . time() . '.jpg';
    $file_path = public_path('sensors/photos') . '/' . $sensor->id . '/' . date('Y-m-d') . '/' . (int)date('H') . '/';
    $file_path = SysCore::os_slash_file($file_path);
    if (!file_exists($file_path)) {
      mkdir($file_path, 0777, true);
    }

    Image::make($values['photo'])
      ->save($file_path . $file_name, 100);

    //file
    $file = 'photos/' . $sensor->id . '/' . date('Y-m-d') . '/' . (int)date('H') . '/' . $file_name;

    $rfs = RestaurantFoodScan::create([
      'restaurant_id' => $sensor->id,

      'local_storage' => 1,
      'photo_name' => $file,
      'photo_ext' => 'jpg',
      'time_photo' => date('Y-m-d H:i:s'),

      'status' => 'new',

      'time_scan' => date('Y-m-d H:i:s'),
    ]);

    //model
    $api_key = 'uYUCzsUbWxWRrO15iar5';
    $dataset = isset($values['dataset']) && !empty($values['dataset']) ? $values['dataset']
      : SysCore::str_trim_slash(SysCore::get_sys_setting('rbf_dataset_scan'));
    $version = isset($values['version']) && !empty($values['version']) ? $values['version']
      : SysCore::get_sys_setting('rbf_dataset_ver');

    $img_url = $rfs->get_photo();

    $datas = SysRobo::photo_scan([
      'img_url' => $img_url,

      'api_key' => $api_key,
      'dataset' => $dataset,
      'version' => $version,

      'confidence' => SysRobo::_RBF_CONFIDENCE,
      'overlap' => SysRobo::_RBF_OVERLAP,
      'max_objects' => SysRobo::_RBF_MAX_OBJECTS,

      'debug' => true,
      'server_url' => 'https://detect.roboflow.com',
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
      $rfs->rfs_photo_predict([
        'notification' => false,
      ]);
    }

    //time_end
    if (empty($rfs->time_end)) {
      $rfs->update([
        'time_end' => date('Y-m-d H:i:s'),
      ]);
    }

    return response()->json([
      'status' => true,

      'datas' => $datas,
    ]);
  }

  //v3
  protected function checked_notify_remove($pars = [])
  {
    $date_from = date('Y-m-d', strtotime("-30 days"));
    $date_to = date('Y-m-d');

    //notifications
    //rfs missing error
    $rows = DB::table('notifications')
      ->distinct()
      ->where('notifiable_type', 'App\Models\User')
      ->whereIn('type', ['App\Notifications\IngredientMissing'])
      ->where('restaurant_food_scan_id', '>', 0)
      ->whereIn('restaurant_food_scan_id', function ($q) use ($date_from, $date_to) {
        $q->select('id')
          ->from('restaurant_food_scans')
          ->where('missing_ids', NULL)
          ->whereDate('time_photo', '>=', $date_from)
          ->whereDate('time_photo', '<=', $date_to)
        ;
      })
      ->whereDate('created_at', '>=', $date_from)
      ->whereDate('created_at', '<=', $date_to)
      ->orderBy('id', 'desc')
      ->get();

    var_dump(SysCore::var_dump_break());
    var_dump('TOTAL_NOTIFICATIONS= ' . count($rows));

    if (count($rows)) {
      $rows = DB::table('notifications')
        ->distinct()
        ->where('notifiable_type', 'App\Models\User')
        ->whereIn('type', ['App\Notifications\IngredientMissing'])
        ->where('restaurant_food_scan_id', '>', 0)
        ->whereIn('restaurant_food_scan_id', function ($q) use ($date_from, $date_to) {
          $q->select('id')
            ->from('restaurant_food_scans')
            ->where('missing_ids', NULL)
            ->whereDate('time_photo', '>=', $date_from)
            ->whereDate('time_photo', '<=', $date_to)
          ;
        })
        ->whereDate('created_at', '>=', $date_from)
        ->whereDate('created_at', '<=', $date_to)
        ->delete();
    }
  }

  protected function checked_food_category_update()
  {
    $count = RestaurantFoodScan::where('deleted', 0)
      ->where('food_id', '>', 0)
      ->where('food_category_id', 0)
      ->where('sys_confidence', 0)
      ->orderBy('id', 'desc')
      ->count();

    var_dump($count);

    $rows = RestaurantFoodScan::where('deleted', 0)
      ->where('food_id', '>', 0)
      ->where('food_category_id', 0)
      ->where('sys_confidence', '<>', 102)
      ->orderBy('id', 'asc')
      ->limit(100)
      ->get();

    var_dump(count($rows));

    if (count($rows)) {
      foreach ($rows as $row) {

        $sensor = $row->get_restaurant();
        $food_category = $row->get_food()->get_category([
          'restaurant_parent_id' => $sensor->restaurant_parent_id
        ]);

        $row->update([
          'food_category_id' => $food_category ? $food_category->id : 0,
          'sys_confidence' => 102,
        ]);
      }
    }

    $count = RestaurantFoodScan::where('deleted', 0)
      ->where('food_id', '>', 0)
      ->where('sys_confidence', 102)
      ->count();

    var_dump($count);
  }

  protected function checked_photo_get($pars = [])
  {
//    SysRobo::photo_get([
//      'limit' => 1,
//      'page' => 1,
//
//      'debug' => true,
//
//      'date' => '2024-07-10',
//      'hour' => 11
//    ]);

    SysRobo::photo_get($pars);
  }

  protected function checked_photo_check($pars = [])
  {
//    SysRobo::photo_check([
//      'debug' => true,
//
//      'rfs' => $row,
//      'restaurant_parent_id' => 1,
//
//      'img_1024' => true,
//      'check_url' => 'https://s3.ap-southeast-1.amazonaws.com/cargo.tastevietnam.asia/58-5b-69-19-ad-83/SENSOR/1/2024-07-06/18/SENSOR_2024-07-06-18-08-53-536_693.jpg',
//
//      'sys_version' => '107',
//      'sys_dataset' => '',
//
//      'rbf_confidence' => '50',
//      'rbf_overlap' => '50',
//      'rbf_max_objects' => '50',
//    ]);

    SysRobo::photo_check($pars);
  }

  protected function checked_photo_day($pars = [])
  {
    $debug = isset($pars['debug']) ? (bool)$pars['debug'] : false;

    $date_from = date('Y-m-01');
    $date_to = date('Y-m-d');

    $sensors = Restaurant::where('deleted', 0)
      ->orderBy('id', 'asc')
      ->get();

    $items = [];

    for ($d = (int)date('d'); $d > 14; $d--) {

      if ((int)$d < 10) {
        $d = '0' . $d;
      }

      $date = date('Y-m-' . $d);

      $temps = [];

      if ($debug) {
        var_dump(SysCore::var_dump_break());
        var_dump('DATE= ' . $date);
      }

      foreach ($sensors as $sensor) {

        if ($debug) {
          var_dump($sensor->id . ' - ' . $sensor->name);
        }

        $photo1s = RestaurantFoodScan::where('restaurant_id', $sensor->id)
          ->where('deleted', 0)
          ->whereDate('time_photo', $date)
          ->get();

        $photo2s = RestaurantFoodScan::where('restaurant_id', $sensor->id)
          ->where('deleted', 0)
          ->where('status', '<>', 'duplicated')
          ->whereDate('time_photo', $date)
          ->get();

        if ($debug) {
          var_dump('PHOTO_TOTAL= ' . count($photo1s));
          var_dump('PHOTO_VALID= ' . count($photo2s));
        }

        $temps[] = [
          'sensor_id' => $sensor->id,
          'sensor_name' => $sensor->name,
          'photo_total' => count($photo1s),
          'photo_valid' => count($photo2s),
        ];
      }

      $items[$date] = $temps;
    }

//    var_dump($items);
    return $items;
  }

  protected function checked_rfs_by_date($pars = [])
  {
    $select = RestaurantFoodScan::query('restaurant_food_scans')
      ->select('id', 'photo_url', 'time_photo', 'time_scan', 'time_end')
      ->where('deleted', 0)
      ->where('status', '<>', 'duplicated')
      ->where('rbf_api', '<>', NULL)
    ;

    if (isset($pars['sensor_id'])) {
      $select->where('restaurant_id', (int)$pars['sensor_id']);
    }

    if (isset($pars['date_from'])) {
      $select->whereDate('time_photo', '>=', $pars['date_from']);
    }

    if (isset($pars['date_to'])) {
      $select->whereDate('time_photo', '<=', $pars['date_to']);
    }

    return $select->get()->toArray();
  }

  protected function checked_zalo_rfs_note_resend($pars = [])
  {
    $types = isset($pars['types']) ? (array)$pars['types'] : [];

    if (!count($types)) {
      return false;
    }

    foreach ($types as $type) {

      $rows = ZaloUserSend::where('status', 0)
        ->where('type', $type)
        ->orderBy('id', 'asc')
        ->get();
      if (count($rows)) {
        foreach ($rows as $row) {
          $user = User::find($row->user_id);

          var_dump('//=======================================================================================//');
          var_dump($row->type);

          switch ($type) {
            case 'photo_comment':

//              {"user_id":3,"zalo_user_id":"7975661731571077013","type":"photo_comment","rfs":69495,"params":[],"status":0}
              $params = (array)json_decode($row->params, true);
              var_dump($params);

              $rfs_id = 0;
              if (count($params) && isset($params['rfs'])) {
                $rfs_id = (int)$params['rfs'];

                var_dump('PHOTO ID= ' . $rfs_id);

                $rfs = RestaurantFoodScan::find($rfs_id);

                if ($rfs) {
                  $datas = SysZalo::send_rfs_note($user, $type, $rfs, [
                    'zalo_no_log' => 0,
                  ]);

                  $sended = false;
                  if (count($datas) && isset($datas['data'])) {
                    $obj = (array)$datas['data'];
                    if (isset($obj['message_id'])) {
                      $sended = true;
                    }
                  }

                  var_dump('SEND= ' . $sended);

                  if ($sended) {
                    $row->update([
                      'status' => 1,
                      'resend' => $row->resend++,
                      'datas' => json_encode($datas)
                    ]);
                  }
                }
              }

              break;
          }

        }
      }
    }
  }

  protected function checked_zalo_user_list_detail($pars = [])
  {
    $sys_app = new SysApp();

    $datas = SysZalo::user_list([
      'offset' => isset($pars['offset']) ? (int)$pars['offset'] : 0, //max 50
    ]);

//    var_dump($datas);

    if (count($datas) && isset($datas['data'])) {
      $temps = (array)$datas['data'];

      if (count($temps) && isset($temps['users']) && count($temps['users'])) {
        foreach ($temps['users'] as $temp) {
          $temp = (array)$temp;

//          var_dump($sys_app::_DEBUG_BREAK);
//
//          var_dump($temp['user_id']);

          $row = ZaloUser::where('zalo_user_id', $temp['user_id'])
            ->first();
          if (!$row) {
            $row = ZaloUser::create([
              'zalo_user_id' => $temp['user_id'],
            ]);
          }

          $row->get_detail();
        }
      }
    }

    return $datas;
  }

  protected function checked_zalo_user_get($pars = [])
  {

    $offset = 0;
    $total = 0;

    $count = 0;

    do {

      $count++;

      var_dump(SysCore::var_dump_break());
      var_dump('run= ' . $count);
      var_dump('off= ' . $offset);

      $datas = $this->checked_zalo_user_list_detail([
        'offset' => $offset,
      ]);

      if (!count($datas)) {
        break;
      }

      if (count($datas) && isset($datas['data'])) {
        $datas = (array)$datas['data'];

        if (count($datas) && isset($datas['total'])) {
          $total = (int)$datas['total'];

          $offset += 50;
        }
      }

      if (!$total || $offset > $total) {
        break;
      }

      var_dump(SysCore::var_dump_break());
      var_dump('total= ' . $total);
      var_dump('offset= ' . $offset);

      if ($count > 3) {
        break;
      }

    } while (1);
  }

  protected function checked_photo_get_old_and_missing($pars = [])
  {
    //pars
    $debug = isset($pars['debug']) ? (bool)$pars['debug'] : false;
    $count_only = isset($pars['count_only']) ? (bool)$pars['count_only'] : false;
    $count_rfs = isset($pars['count_rfs']) ? (int)$pars['count_rfs'] : 5;
    $count_current = 0;

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

    $file_log = 'public/logs/cron_photo_miss_' . $sensor->id . '.log';
    Storage::append($file_log, '===================================================================================');
    Storage::append($file_log, 'AT_' . date('Y_m_d_H_i_s'));

    if (!$sensor) {
      return false;
    }

    if ($debug) {
      var_dump(SysCore::var_dump_break());
      var_dump('SENSOR= ' . $sensor->name . ' - ID= ' . $sensor->id);
    }

//    $sensor->update([
//      's3_checking' => 1,
//    ]);

    try {

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

      $folder_setting = SysCore::str_trim_slash($sensor->s3_bucket_address);
      $directory = $folder_setting . '/' . $cur_date . '/' . $cur_hour . '/';

      Storage::append($file_log, 'FOLDER_' . $directory);

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
//        $files = array_reverse($files);
        $count = 0;

        Storage::append($file_log, 'TOTAL FILES= ' . count($files));

        //step 1= photo check
        foreach ($files as $file) {
          if ($count_current == $count_rfs) {
            break;
          }

          if ($debug) {
            var_dump(SysCore::var_dump_break());
            var_dump('FILE= ' . $file);
          }

          $rfs = NULL;
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

          if ($debug) {
            var_dump('KEYWORD= ' . $keyword);
            var_dump('COUNT= ' . $count);
          }

          //check exist
          $rfs = RestaurantFoodScan::where('restaurant_id', $sensor->id)
            ->where('photo_name', $file)
            ->first();
          if (!$rfs) {
            $count_current++;

            $status = 'new';

            $rows = RestaurantFoodScan::where('photo_name', 'LIKE', $keyword)
              ->where('restaurant_id', $sensor->id)
              ->get();
            if (count($rows)) {
              $status = 'duplicated';
            }

            //time_photo
            $temps = array_filter(explode('/', $file));
            $temps = $temps[count($temps)-1];

            $temps = str_replace('SENSOR1_', '', $temps);
            $temps = str_replace('.jpg', '', $temps);

            $temps = array_filter(explode('-', $temps));
            $time_photo = $temps[0] . '-' . $temps[1] . '-' . $temps[2] . ' ' . $temps[3] . ':' . $temps[4] . ':' . $temps[5];

            if (!$count_only) {
              //step 1= photo get
              $rfs = $sensor->photo_save([
                'local_storage' => 1,
                'photo_url' => NULL,
                'photo_name' => $file,
                'photo_ext' => 'jpg',

                'missing_notify' => 1,
                'time_photo' => $time_photo, //date('Y-m-d H:i:s'),

                'status' => $status,
              ]);
            }

            if ($debug) {
              var_dump('CREATED= ' . $file);
              var_dump('TIME_PHOTO= ' . $time_photo);

              if ($rfs) {
                var_dump('PHOTO_SAVE= ' . $rfs->id);
              }
            }

          }

          if ($debug) {
            if ($rfs) {
              var_dump('PHOTO_STATUS= ' . $rfs->status);
            }
          }

          if ($rfs && !$count_only) {
            if ($rfs->status == 'new') {
              $rfs->rfs_photo_scan([
                'created' => true,
                'notification' => false,

                'debug' => $debug,
              ]);

              //time_scan - time_end
              if ($rfs->status != 'duplicated') {
                $time_scan = $rfs->time_photo;
                $rand = rand(0, 1);
                if ($rand) {
                  $time_scan = date('Y-m-d H:i:s', strtotime($time_scan) + 1);
                }

                $rand = rand(1, 3);
                $time_end = date('Y-m-d H:i:s', strtotime($time_scan) + $rand);

                $rfs->update([
                  'time_scan' => $time_scan,
                  'time_end' => $time_end,
                ]);
              }

              if ($debug) {
                var_dump('PHOTO_SCANNED= YES');
                var_dump('PHOTO_STATUS= ' . $status);
              }
            }
          }
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

//    $sensor->update([
//      's3_checking' => 0,
//    ]);
  }

  protected function photo_time_photo_error()
  {
    $rows = RestaurantFoodScan::where('restaurant_id', 10)
      ->where('photo_name', 'LIKE', '58-5b-69-20-a8-f6/SENSOR/1/2024-07-29/17/SENSOR1_2024-07-29-17-%.jpg')
      ->get();

    var_dump(count($rows));

    foreach ($rows as $row) {
      var_dump(SysCore::var_dump_break());
      var_dump($row->photo_name);

      $temps = array_filter(explode('/', $row->photo_name));
      $temps = $temps[count($temps)-1];

      $temps = str_replace('SENSOR1_', '', $temps);
      $temps = str_replace('.jpg', '', $temps);

      $temps = array_filter(explode('-', $temps));
      $temps = $temps[0] . '-' . $temps[1] . '-' . $temps[2] . ' ' . $temps[3] . ':' . $temps[4] . ':' . $temps[5];

      $s1 = 0;
      $s2 = 0;
      if ($row->status != 'duplicated') {
        $s1 = date('s', strtotime($row->time_scan) - strtotime($row->time_photo));
        $s2 = date('s', strtotime($row->time_end) - strtotime($row->time_scan));

        var_dump($s1);
        var_dump($s2);
      }
      else {
        $row->update([
          'time_photo' => $temps,
        ]);
      }

      var_dump($temps);
      if ($row->status != 'duplicated') {
        $d1 = date('Y-m-d H:i:s', strtotime($temps) + $s1);
        $d2 = date('Y-m-d H:i:s', strtotime($temps) + $s2);

        var_dump($d1);
        var_dump($d2);

        $row->update([
          'time_photo' => $temps,
          'time_scan' => $d1,
          'time_end' => $d2,
        ]);
      }


    }
  }

  protected function checked_photo_duplicated_and_not_found($pars = [])
  {
    $limit = isset($pars['limit']) ? (int)$pars['limit'] : 50;

    $s3_region = SysCore::get_sys_setting('s3_region');

    $rows = RestaurantFoodScan::where('local_storage', 1)
      ->where('deleted', 0)
      ->orderBy('id', 'asc')
      ->limit($limit)
      ->get();

    var_dump('TOTAL= ' . count($rows));
    if (count($rows)) {

      $count_deleted = 0;
      $count_ok = 0;

      foreach ($rows as $rfs) {

        var_dump(SysCore::var_dump_break());
        var_dump('RFS= ' . $rfs->id);
        var_dump('TIME= ' . $rfs->time_photo);
        var_dump('NAME= ' . $rfs->photo_name);

        switch ($rfs->status) {
          case 'duplicated':

            $rfs->update([
              'deleted' => 1,
            ]);

            $count_deleted++;

            break;

          case 'checked':
          case 'failed':

          $sensor = $rfs->get_restaurant();
          $img_url = "https://s3.{$s3_region}.amazonaws.com/{$sensor->s3_bucket_name}/{$rfs->photo_name}";

          if (@getimagesize($img_url)) {

            $rfs->update([
              'local_storage' => 0,
              'photo_url' => $img_url,
            ]);

            $count_ok++;
          }
          else {

            $rfs->update([
              'deleted' => 1,
            ]);

            $count_deleted++;
          }

            break;
        }
      }

      var_dump(SysCore::var_dump_break());
      var_dump('DELETED= ' . $count_deleted);
      var_dump('SYNCED= ' . $count_ok);

    }
  }

  //roboflow
  protected function rbf_server_new_call_test()
  {
    $ch = curl_init();
    $headers = [
      'Accept: application/json',
      'Content-Type: application/json'
    ];

    $URL = "https://detect.roboflow.com/infer/workflows/tastvn/custom-workflow";

    $postData = [
      'api_key' => 'uYUCzsUbWxWRrO15iar5',
      'inputs' => [
        'image' => [
          'type' => 'url',
          'value' => 'https://ai.block8910.com/sensors/58-5b-69-19-ad-83/SENSOR/1/2024-10-14/12/SENSOR_2024-10-14-12-01-44-720_304.jpg',
        ]
      ]
    ];

    curl_setopt($ch, CURLOPT_URL, $URL);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    $result = curl_exec($ch);
    $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    curl_close($ch);

    $data = (array)json_decode($result);
    var_dump($data);
  }

  protected function rbf_photo_get_manually($values = [])
  {
    $page = isset($values['page']) ? (int)$values['page'] : 1;
    SysRobo::photo_get([
      'limit' => 1,
      'page' => $page,

//      'hour' => 15,
    ]);
  }

  //file
  protected function file_save($file, $content)
  {

  }

  protected function file_read($file)
  {

  }

  //kas
  protected function kas_time_sheet($pars = [])
  {
    $date_from = isset($pars['date_from']) ? $pars['date_from'] : date('Y-m-d', strtotime("-3 days"));
    $date_to = isset($pars['date_to']) ? $pars['date_to'] : date('Y-m-d');

    $ch = curl_init();
    $url_header = [
      'Accept: application/json',
      'Content-Type: application/x-www-form-urlencoded',
//      'secret_key: ' . SysZalo::_APP_SECRET_KEY,
    ];
    $url_api = 'https://tastevietnam.smac.cloud/publish/api/v1/TimesheetInfo';

    $kas_token = '95e6c4d69fd657dc0c4f0947b0d37c414ad2b6cabbfc26b43d16a40f241278f8';

    $url_params = 'token=' . $kas_token
      . '&from_date=' . $date_from
      . '&to_date=' . $date_to
    ;

//    $url_params = [
//      'token' => '95e6c4d69fd657dc0c4f0947b0d37c414ad2b6cabbfc26b43d16a40f241278f8',
//      'from_date' => '2024-07-25',
//      'to_date' => '2024-07-28',
//    ];

    curl_setopt($ch, CURLOPT_URL, $url_api);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $url_header);

    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $url_params);

    $result = curl_exec($ch);
    $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    curl_close($ch);

    $datas = (array)json_decode($result);

    var_dump($datas);

    return $datas;
  }

  //excel
  protected function restaurant_stats_date_photo_test_update()
  {
    $rows = RestaurantStatsDate::all();
    foreach ($rows as $row) {
      $restaurant_parent = RestaurantParent::find($row->restaurant_parent_id);
      $date = $row->date;

      $select_sensors = Restaurant::select('id')
        ->where('restaurant_parent_id', $restaurant_parent->id)
        ->where('deleted', 0);

      $test_photos = RestaurantFoodScan::where('deleted', 0)
        ->whereDate('time_photo', $date)
        ->whereIn('restaurant_id', $select_sensors)
        ->whereIn('status', ['tested'])
        ->count();

      $row->update([
        'test_photos' => $test_photos,
      ]);
    }
  }

  public function excel_restaurant_stats_date(Request $request)
  {
    $values = $request->all();
    $restaurant = isset($values['restaurant']) ? (int)$values['restaurant'] : 5;

    $restaurant_parent = RestaurantParent::find($restaurant);
    if (!$restaurant_parent) {
      die('Invalid restaurant...');
    }

    $items = RestaurantStatsDate::where('restaurant_parent_id', $restaurant_parent->id)
      ->orderBy('date', 'desc')
      ->get()
      ->toArray();

    $file = new ExportRestaurantStatsDate();
    $file->set_items($items);

    return Excel::download($file, 'report_stats_date_' . $restaurant_parent->id . '_'. $restaurant_parent->name . '.xlsx');
  }

}
