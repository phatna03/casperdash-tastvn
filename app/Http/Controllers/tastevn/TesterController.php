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

    $restaurant = RestaurantParent::find(1);
    $sensor = Restaurant::find(5);

    $date = '2024-06-18';


//    $arr = $this->photo_duplicate([
//      //market test
//      'restaurant_id' => 10,
//    ]);
//
//    var_dump($arr);

    //fix live

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

  protected function photo_duplicate($pars = [])
  {
    $sys_app = new SysApp();

    $select = RestaurantFoodScan::query('restaurant_food_scans');

    if (isset($pars['restaurant_id']) && (int)$pars['restaurant_id']) {
      $select->where('restaurant_food_scans.restaurant_id', (int)$pars['restaurant_id']);
    }

    //default
    $date_from = date('Y-m-01');
    $date_to = date('Y-m-t');

    if (isset($pars['date_from']) && !empty($pars['date_from'])) {
      $date_from = $pars['date_from'];
    }
    if (isset($pars['date_to']) && !empty($pars['date_to'])) {
      $date_to = $pars['date_to'];
    }

    $select->whereDate('restaurant_food_scans.time_photo', '>=', $date_from)
      ->whereDate('restaurant_food_scans.time_photo', '<=', $date_to)
      ->limit(2)
      ->orderBy('id', 'asc');

    var_dump($sys_app::_DEBUG_BREAK);
    var_dump('QUERY=');
    var_dump($sys_app->parse_to_query($select));

    $rows = $select->get();
    var_dump('TOTAL PHOTOS= ' . count($rows));

    if (count($rows)) {

      //reset
      $select->update([
        'photo_main' => 0,
      ]);

      foreach ($rows as $row) {
        var_dump($sys_app::_DEBUG_BREAK);
        var_dump('ID CHECK= ' . $row->id);

        $photo_name = SysRobo::photo_name_query($row->photo_name);
        var_dump($row->photo_name);
        var_dump($photo_name);

        //find duplicate
        $duplicates = RestaurantFoodScan::where('photo_name', 'LIKE', $photo_name)
          ->where('id', '<>', $row->id)
          ->get();
        var_dump('TOTAL DUPLICATED= ' . count($duplicates));

        $row->update([
          'photo_main' => 1,
        ]);

        if (count($duplicates)) {

        }
      }
    }

  }

  protected function notify_remove()
  {

  }
}
