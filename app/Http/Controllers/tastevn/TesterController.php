<?php

namespace App\Http\Controllers\tastevn;
use App\Http\Controllers\Controller;
use App\Models\ZaloUserSend;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
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

use Validator;
use Aws\S3\S3Client;
use App\Api\SysApp;
use App\Api\SysAws;
use App\Api\SysCore;
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
use App\Models\KasWebhook;
use App\Models\ReportPhoto;
use App\Models\ReportFood;
use App\Models\ZaloUser;

use Zalo\Zalo;
use Zalo\Builder\MessageBuilder;
use Zalo\ZaloEndPoint;

class TesterController extends Controller
{

  public function index(Request $request)
  {
    echo '<pre>';

    $sys_app = new SysApp();

    $restaurant = RestaurantParent::find(1);
    $sensor = Restaurant::find(5);
    $rfs = RestaurantFoodScan::find(69218);
    $date = date('Y-m-d');
    $user = User::find(5);
    $kas = KasWebhook::find(5);

    //=======================================================================================
    //=======================================================================================




    //=======================================================================================
    //=======================================================================================

//    $datas = SysZalo::zalo_token([
//
//    ]);
//    var_dump($datas);

//    $rfs->rfs_photo_scan_before();
//    $rfs->rfs_photo_predict([
//      'notification' => true,
//
//      'debug' => true,
//    ]);

//live
//    $cur_date = date('Y-m-d');
//    $cur_hour = (int)date('H');
//    //sensor folder
//    $folder_setting = SysCore::str_trim_slash($sensor->s3_bucket_address);
//    $directory = $folder_setting . '/' . $cur_date . '/' . $cur_hour . '/';
//    //sensor files
//    $files = Storage::disk('sensors')->files($directory);
//    if (count($files)) {
//      //desc = order by last updated or modified
//      $files = array_reverse($files);
//
//      foreach ($files as $file) {
//        //sensor ext = jpg
//        $ext = array_filter(explode('.', $file));
//        if (!count($ext) || $ext[count($ext) - 1] != 'jpg') {
//          continue;
//        }
//
//        //photo width 1024
//        $temps = array_filter(explode('/', $file));
//        $photo_name = $temps[count($temps) - 1];
//        if (substr($photo_name, 0, 5) == '1024_') {
//          continue;
//        }
//
//        var_dump($file);
//      }
//    }
//
//    if (!$rfs || ($rfs && $rfs->status == 'duplicated')) {
//      $rfs = RestaurantFoodScan::where('restaurant_id', $sensor->id)
//        ->where('status', '<>', 'duplicated')
//        ->where('deleted', 0)
//        ->orderBy('id', 'desc')
//        ->limit(1)
//        ->first();
//    }

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

    //=======================================================================================
    //=======================================================================================
    //fix live

//    $this->checked_notify_remove();
//    $this->checked_food_category_update();
//    $this->checked_zalo_user_get();

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



    return response()->json([
      'status' => true,
    ]);
  }

  //v3
  protected function checked_notify_remove($pars = [])
  {
    $date_from = date('Y-m-d', strtotime("-30 days"));
    $date_to = date('Y-m-d');

    //notifications
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
    $rows = RestaurantFoodScan::where('deleted', 0)
      ->where('food_id', '>', 0)
      ->where('food_category_id', 0)
      ->orderBy('id', 'desc')
      ->get();

    var_dump(count($rows));

    $rows = RestaurantFoodScan::where('deleted', 0)
      ->where('food_id', '>', 0)
      ->where('food_category_id', 0)
      ->where('sys_confidence', '<>', 102)
      ->orderBy('id', 'desc')
      ->limit(500)
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

    $rows = RestaurantFoodScan::where('deleted', 0)
      ->where('food_id', '>', 0)
      ->where('sys_confidence', 102)
      ->get();

    var_dump(count($rows));
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
}
