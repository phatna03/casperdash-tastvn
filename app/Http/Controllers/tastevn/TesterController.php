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

class TesterController extends Controller
{
  protected $directories = [
    'cargo' => [
      'bucket' => 's3_bucket_cargo',
      'folder' => '/58-5b-69-19-ad-83/',
    ],
//    'cargo' => [
//      'bucket' => 's3_bucket_cargo',
//      'folder' => '/58-5b-69-19-ad-67/',
//    ],
//    'deli' => [
//      'bucket' => 's3_bucket_deli',
//      'folder' => '/58-5b-69-19-ad-b6/',
//    ],
//    'deli' => [
//      'bucket' => 's3_bucket_deli',
//      'folder' => '/58-5b-69-20-11-7b/',
//    ],
//    'market' => [
//      'bucket' => 's3_bucket_market',
//      'folder' => '/58-5b-69-20-a8-f6/',
//    ],
//    'poison' => [
//      'bucket' => 's3_bucket_poison',
//      'folder' => '/58-5b-69-15-cd-2b/',
//    ],
  ];

  public function index(Request $request)
  {
//    echo '<pre>';
    $user = Auth::user();

    $restaurant = Restaurant::find(5);

    $sys_app = new SysApp();
    $s3_region = $sys_app->get_setting('s3_region');

    var_dump($s3_region);

    foreach ($this->directories as $restaurant => $directory) {

      var_dump('====================================================');

      $count = 0;
      $date = date('Y-m-d',strtotime("-1 days"));

      $file_log = 'public/logs/cron_sync_s3_' . $restaurant . '.log';
      Storage::append($file_log, '===================================================================================');

      $localDisk = Storage::disk('sensors');
      $s3Disk = Storage::disk($directory['bucket']);

      $files = $localDisk->allFiles($directory['folder']);

      foreach ($files as $file) {

        $status = $s3Disk->put($file, $localDisk->get($file));
        if ($status) {

          $count++;

          $row = RestaurantFoodScan::where('photo_name', $file)
            ->first();
          if ($row) {

            $restaurant = $row->get_restaurant();
            $URL = "https://s3.{$s3_region}.amazonaws.com/{$restaurant->s3_bucket_name}/{$file}";

            if (@getimagesize($URL)) {

              $row->update([
                'local_storage' => 2,
                'photo_url' => $URL,
              ]);

              //remove local file

            }
          }
        }

        Storage::append($file_log, 'FILE_SYNC_STATUS= ' . $status);
        Storage::append($file_log, 'FILE_SYNC_DATA= ' . $file);
      }

      Storage::append($file_log, 'TOTAL= ' . $count);
      Storage::append($file_log, '===================================================================================');
    }

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
}
