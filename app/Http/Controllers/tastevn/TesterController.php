<?php

namespace App\Http\Controllers\tastevn;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
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

use Validator;
use Aws\S3\S3Client;
use App\Api\SysApp;
use App\Api\SysRobo;

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

class TesterController extends Controller
{

  public function index(Request $request)
  {
    echo '<pre>';

    $user = Auth::user();
    $sys_app = new SysApp();

    $restaurant = RestaurantParent::find(1);
    $sensor = Restaurant::find(5);

    $date = '2024-06-16';

    var_dump((int)date('i'));

    var_dump(date('Y-m-d', strtotime("-1 days")));

    //fix live
//    $rfs = RestaurantFoodScan::find(36968);
//    $rfs->update([
//      'rbf_api' => NULL,
//    ]);
//
//    $rfsss = RestaurantFoodScan::whereIn('id', [
//      41922, 42202, 42085, 42125, 41963, 41953,
//      42058, 42163, 42057, 42174, 36968, 23020,
//      23021, 35317, 36482,
//    ])
//      ->get();
//    if (count($rfsss)) {
//      foreach ($rfsss as $rfs) {
//        $rfs->predict_food([
//          'notification' => false,
//        ]);
//      }
//    }
    //=======================================================================================

    //remove notify
//    $row1s = RestaurantFoodScan::select('id')
//      ->where('deleted', 0)
//      ->whereDate('time_photo', '>=', '2024-06-01')
//      ->whereDate('time_photo', '<=', '2024-06-17')
//      ->where('missing_texts', NULL)
//      ->where('food_id', '>', 0)
//
//    ;
//
//    $row2s = DB::table('notifications')
//      ->distinct()
//      ->where('type', 'App\Notifications\IngredientMissing')
//      ->whereIn('restaurant_food_scan_id', $row1s)
//      ->get();
//
//    DB::table('notifications')
//      ->distinct()
//      ->where('type', 'App\Notifications\IngredientMissing')
//      ->whereIn('restaurant_food_scan_id', $row1s)
//      ->delete();
//
//    var_dump(count($row1s->get()));
//    var_dump(count($row2s));

    //old s3 photo
//    $sensor->s3_photo([
//      's3_region' => $sys_app->get_setting('s3_region'),
//      's3_api_key' => $sys_app->get_setting('s3_api_key'),
//      's3_api_secret' => $sys_app->get_setting('s3_api_secret'),
//
//      's3_bucket' => $sensor->s3_bucket_name,
//      's3_address' => $sys_app->parse_s3_bucket_address($sensor->s3_bucket_address),
//
//      'scan_date' => '2024-06-05',
////      'scan_hour' => 20,
//    ]);

//    $report = Report::find(1);
//    $report->start();

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

    $datas = (new ImportData())->toArray($request->file('excel'));
    if (!count($datas) || !count($datas[0])) {
      return response()->json([
        'error' => 'Invalid data'
      ], 404);
    }

    $file_log = 'public/logs/rbf_re_scan_data.log';

    foreach ($datas[0] as $k => $data) {

      $col1 = trim($data[0]);

      $row = RestaurantFoodScan::find((int)$col1);
      if (!$row) {
        continue;
      }

      Storage::append($file_log, $row->id);

      //step 2= photo scan
      $datas = SysRobo::photo_scan($row, [
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

    return response()->json([
      'status' => true,
    ]);
  }
}
