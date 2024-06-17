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


//    $rfs = RestaurantFoodScan::find(42324);
//
//    var_dump($rfs->photo_name);
//    var_dump($this->photo_name_query($rfs->photo_name));
//
//    $rows = RestaurantFoodScan::where('photo_name', 'LIKE', $this->photo_name_query($rfs->photo_name))
//      ->get();
//
//    var_dump(count($rows));
//    if (count($rows)) {
//      foreach ($rows as $row) {
//        var_dump($row->id);
//      }
//    }
//
//    die;


    $date = '2024-06-16';

//    $cur_date = date('Y-m-d');
//    $cur_hour = (int)date('H');
//
//    $folder_setting = $sys_app->parse_s3_bucket_address($sensor->s3_bucket_address);
//    $directory = $folder_setting . '/' . $cur_date . '/' . $cur_hour . '/';
//
//    $files = Storage::disk('sensors')->files($directory);
//    if (count($files)) {
//      foreach ($files as $file) {
//        $ext = array_filter(explode('.', $file));
//        if (!count($ext) || $ext[count($ext) - 1] != 'jpg') {
//          continue;
//        }
//
//        //no 1024
//        $temps = array_filter(explode('/', $file));
//        $photo_name = $temps[count($temps) - 1];
//        if (substr($photo_name, 0, 5) == '1024_') {
//          continue;
//        }
//
//        var_dump($file);
//        var_dump($this->photo_name_query($file));
//
//      }
//    }

//    $rows = RestaurantFoodScan::where('restaurant_id', 5)
//      ->whereDate('time_photo', $date)
//      ->get();
//    if (count($rows)) {
//      foreach ($rows as $row) {
//        var_dump('=====================');
//        var_dump($row->photo_name);
//      }
//    }


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

  protected function clear_photos()
  {
    $count = 0;

    $date = date('Y-m-d', strtotime("-7 days"));

    var_dump('***************************************************************************************');
    var_dump($date);

    $directories = SysRobo::s3_bucket_folder();
    foreach ($directories as $restaurant => $directory) {

      var_dump('***************************************************************************************');
      var_dump($restaurant);
      var_dump($directory);

      $localDisk = Storage::disk('sensors');
      $s3Disk = Storage::disk($directory['bucket']);

      $dir = "{$directory['folder']}SENSOR/1/{$date}/";
      $files = $localDisk->allFiles($dir);
      if (count($files)) {
        foreach ($files as $file) {

          var_dump('--------------------------------------------------------');
          var_dump($file);


          $storagePath = public_path('sensors') . '/' . $file;
          var_dump($storagePath);

          if (is_file($storagePath)) {
            unlink($storagePath);
            $count++;
          }
        }
      }
    }

    return $count;
  }

  protected function photo_name_query($file)
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
