<?php

namespace App\Http\Controllers\tastevn\api;
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
use App\Excel\ExportFoodIngredient;
use App\Excel\ExportFoodRecipe;
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
  public function index(Request $request)
  {
//    echo '<pre>';
    $user = Auth::user();

    $restaurant = Restaurant::find(5);



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
