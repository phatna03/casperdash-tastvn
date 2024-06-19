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

    $date = '2024-06-18';




    //fix live

    //=======================================================================================

    //remove notify
//    $row1s = RestaurantFoodScan::select('id')
//      ->where('deleted', '>', 0)
//      ->whereDate('time_photo', '2024-06-18')
//      ->whereDate('time_photo', '<=', '2024-06-18')
//      ->where('missing_texts', NULL)
//      ->where('food_id', '>', 0)
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



    return response()->json([
      'status' => true,
    ]);
  }
}
