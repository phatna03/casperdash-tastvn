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

//use Intervention\Image\ImageManagerStatic as Image;
//
//use Illuminate\Support\Facades\Notification;
//use App\Notifications\IngredientMissing;
//use App\Notifications\IngredientMissingMail;
//
//use Maatwebsite\Excel\Facades\Excel;
//use App\Excel\ImportData;
//use App\Excel\ExportData;
//use App\Excel\ExportDataRfs;
//use App\Excel\ExportRestaurantStatsDate;
//
//use Validator;
//use Aws\S3\S3Client;
//use App\Api\SysApp;
//use App\Api\SysAws;
//use App\Api\SysCore;
//use App\Api\SysDev;
//use App\Api\SysKas;
//use App\Api\SysRobo;
//use App\Api\SysZalo;
//
//use App\Models\User;
//use App\Models\Restaurant;
//use App\Models\RestaurantParent;
//use App\Models\RestaurantAccess;
//use App\Models\Food;
//use App\Models\Ingredient;
//use App\Models\FoodIngredient;
//use App\Models\SysSetting;
//use App\Models\RestaurantFood;
//use App\Models\RestaurantFoodScan;
//use App\Models\Comment;
//use App\Models\FoodRecipe;
//use App\Models\FoodCategory;
//use App\Models\Log;
//use App\Models\SysNotification;
//use App\Models\Report;
//use App\Models\KasBill;
//use App\Models\KasBillOrder;
//use App\Models\KasBillOrderItem;
//use App\Models\KasItem;
//use App\Models\KasRestaurant;
//use App\Models\KasStaff;
//use App\Models\KasTable;
//use App\Models\KasWebhook;
//use App\Models\ReportPhoto;
//use App\Models\ReportFood;
//use App\Models\ZaloUser;
//use App\Models\ZaloUserSend;
//use App\Models\RestaurantStatsDate;
//
//use Zalo\Zalo;
//use Zalo\Builder\MessageBuilder;
//use Zalo\ZaloEndPoint;

class TesterController extends Controller
{

  public function index(Request $request)
  {
    echo '<pre>';

//    $sys_app = new SysApp();
//
//    $values = $request->all();
//
//    $restaurant = RestaurantParent::find(6);
//    $sensor = Restaurant::find(5);
//    $rfs = RestaurantFoodScan::find(113618);
//    $date = date('Y-m-d');
//    $user = User::find(4);
//    $kas = KasWebhook::find(539);
//    $debug = true;
//    $food = Food::find(29);
//    $kas_restaurant = KasRestaurant::find(3);
//
//    $s3_region = SysCore::get_sys_setting('s3_region');
//    $s3_api_key = SysCore::get_sys_setting('s3_api_key');
//    $s3_api_secret = SysCore::get_sys_setting('s3_api_secret');

    //=======================================================================================
    //=======================================================================================

    $directory = '00-18-ae-00-e3-31/SENSOR/1/2024-10-25/12';

    $files = Storage::disk('sensors')->files($directory);

    var_dump($files);

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

}
