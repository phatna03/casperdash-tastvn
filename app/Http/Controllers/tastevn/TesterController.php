<?php

namespace App\Http\Controllers\tastevn;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

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

class TesterController extends Controller
{

  public function index(Request $request)
  {
    echo '<pre>';

    $user = Auth::user();
    $sys_app = new SysApp();

    $restaurant = RestaurantParent::find(1);
    $sensor = Restaurant::find(5);

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

      $img_url = $row->get_photo();

      //step 2= photo scan
      $datas = SysRobo::photo_scan($img_url, [
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
}
