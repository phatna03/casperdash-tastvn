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
use App\Models\ReportPhoto;
use App\Models\ReportFood;

class TesterController extends Controller
{

  public function index(Request $request)
  {
    echo '<pre>';

    $sys_app = new SysApp();

    $restaurant = RestaurantParent::find(1);
    $sensor = Restaurant::find(5);
    $date = date('Y-m-d');

//    var_dump(phpinfo());

//    $restaurant->count_foods();


//    $row = RestaurantFoodScan::find(45535);
//
//    $row->predict_food([
//      'notification' => false,
//
//      'debug' => true,
//    ]);


    //fix live

    //sync deli food to market + poison + morning
//    $this->food_remove([
//      'sensors' => [10, 11, 12, 13],
//
//      'restaurants' => [3, 4, 5],
//    ]);
//
//    $this->food_add([
//      'sensors' => [10, 11, 12, 13],
//      'sensor_id' => 9,
//
//      'restaurants' => [3, 4, 5],
//      'restaurant_parent_id' => 2,
//    ]);

//    food category

//    $this->food_category_update();

//    $this->food_restaurant_sync();

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

    $select = RestaurantFoodScan::query('restaurant_food_scans')
      ->where('status', '<>', 'duplicated');

    if (isset($pars['restaurant_id']) && (int)$pars['restaurant_id']) {
      $select->where('restaurant_food_scans.restaurant_id', (int)$pars['restaurant_id']);
    }

    if (isset($pars['rfs_id']) && (int)$pars['rfs_id']) {
      $select->where('restaurant_food_scans.id', '>=', (int)$pars['rfs_id']);
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
      ->orderBy('id', 'asc');

    var_dump($sys_app::_DEBUG_BREAK);
    var_dump('QUERY=');
    var_dump($sys_app->parse_to_query($select));

    $rows = $select->get();
    var_dump('TOTAL PHOTOS= ' . count($rows));

    $ids_checked = [];
    $main_status_invalids = [
      'duplicated', 'failed', 'scanned',
    ];

    if (count($rows)) {

      //reset
      $select->update([
        'photo_main' => 0,
      ]);

      foreach ($rows as $row) {
        var_dump($sys_app::_DEBUG_BREAK);
        var_dump('ID= ' . $row->id);

        //checked
        if (in_array($row->id, $ids_checked)) {
          continue;
        }

        //1024_
        $temps = explode('/', $row->photo_name);
        $photo_name = $temps[count($temps) - 1];
        if (substr($photo_name, 0, 5) == '1024_') {

          $row->update([
            'deleted' => 1,
          ]);

          continue;
        }

        $ids_checked[] = $row->id;

        var_dump('ID START CHECK= ' . $row->id);

        $keyword = SysRobo::photo_name_query($row->photo_name);
        var_dump($row->photo_name);
        var_dump($keyword);

        //find duplicate
        $duplicates = RestaurantFoodScan::where('deleted', 0)
          ->where('status', '<>', 'duplicated')
          ->where('photo_name', 'LIKE', $keyword)
          ->where('id', '<>', $row->id)
          ->orderBy('food_id', 'desc')
          ->get();
        var_dump('TOTAL DUPLICATED= ' . count($duplicates));

        //check missing
        $id_main = 0;
        if ($row->food_id) {

          if (!empty($row->missing_ids)) {

            $temp1 = RestaurantFoodScan::where('deleted', 0)
              ->where('status', '<>', 'duplicated')
              ->where('photo_name', 'LIKE', $keyword)
              ->where('id', '<>', $row->id)
              ->where('food_id', $row->food_id)
              ->where('missing_ids', NULL)
              ->orderBy('food_id', 'desc')
              ->orderBy('id', 'asc')
              ->first();

            if ($temp1) {
              $id_main = $temp1->id;
            } else {
              $id_main = $row->id;
            }

          } else {
            $id_main = $row->id;
          }
        }

        $id_duplicates = [];
        $need_compare = false;

        if (count($duplicates)) {

          $need_compare = true;

          foreach ($duplicates as $rfs) {

            $ids_checked[] = $rfs->id;

            var_dump('ID DUPLICATED= ' . $rfs->id);

            if (!$id_main && empty($rfs->missing_ids)) {
              $id_main = $rfs->id;
            }

            $id_duplicates[] = $rfs->id;
          }
        }
        else {
          //main
          $row->update([
            'photo_main' => 1,
          ]);
          if (in_array($row->status, $main_status_invalids)) {
            $row->update([
              'status' => 'checked',
            ]);
          }
        }

        //main or not
        if ($need_compare) {
          if (!$id_main || $id_main == $row->id) {
            $row->update([
              'photo_main' => 1,
            ]);
            if (in_array($row->status, $main_status_invalids)) {
              $row->update([
                'status' => 'checked',
              ]);
            }

            if (count($duplicates)) {
              foreach ($duplicates as $rfs) {
                $rfs->update([
                  'status' => 'duplicated',
                ]);
              }
            }
          }
          else {

            if ($id_main) {

              $row->update([
                'status' => 'duplicated',
              ]);

              foreach ($duplicates as $rfs) {
                if ($id_main == $rfs->id) {

                  $rfs->update([
                    'photo_main' => 1,
                  ]);
                  if (in_array($rfs->status, $main_status_invalids)) {
                    $rfs->update([
                      'status' => 'checked',
                    ]);
                  }

                } else {

                  $rfs->update([
                    'status' => 'duplicated',
                  ]);
                }
              }
            }
          }
        }

//        var_dump('IDS CHECKED= ');
//        var_dump($ids_checked);
      }
    }

  }

  protected function notify_remove($pars = [])
  {
    $date_from = date('Y-m-d', strtotime("-5 days"));
    $date_to = date('Y-m-d');

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

    var_dump(count($rows));

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

  protected function photo_sync($pars = [])
  {
    $sys_app = new SysApp();
    $s3_region = $sys_app->get_setting('s3_region');

    $date_to = date('Y-m-d', strtotime("-3 days"));
    $date_from = date('Y-m-d', strtotime("-30 days"));

    $photos = RestaurantFoodScan::where('deleted', 0)
      ->where('local_storage', 1)
      ->whereDate('time_photo', '>=', $date_from)
      ->whereDate('time_photo', '<=', $date_to)
      ->orderBy('time_photo', 'desc')
      ->orderBy('id', 'desc')
      ->get();

    var_dump('ERROR= ' . count($photos));

    if (count($photos)) {
      foreach ($photos as $photo) {
        var_dump($sys_app::_DEBUG_BREAK);
        var_dump('ID= ' . $photo->id);
        var_dump('TIME= ' . $photo->time_photo);
        var_dump('STATUS= ' . $photo->status);

        $sensor = $photo->get_restaurant();
        $URL = "https://s3.{$s3_region}.amazonaws.com/{$sensor->s3_bucket_name}/{$photo->photo_name}";

        var_dump('URL= ' . $URL);
        var_dump('SYNC= ');
        var_dump(@getimagesize($URL));

        if (@getimagesize($URL)) {

          $photo->update([
            'local_storage' => 0,
            'photo_url' => $URL,
          ]);
        }
        else {
          $photo->update([
            'deleted' => 1,
          ]);
        }

      }
    }
  }

  protected function food_remove($pars = [])
  {
    $sensors = isset($pars['sensors']) ? (array)$pars['sensors'] : [];
    $restaurants = isset($pars['restaurants']) ? (array)$pars['restaurants'] : [];

    if (count($sensors) && count($restaurants)) {
      RestaurantFood::whereIn('restaurant_id', $sensors)
        ->delete();

      FoodIngredient::whereIn('restaurant_parent_id', $restaurants)
        ->delete();

      FoodRecipe::whereIn('restaurant_parent_id', $restaurants)
        ->delete();

    }
  }

  protected function food_add($pars = [])
  {
    $sensors = isset($pars['sensors']) ? (array)$pars['sensors'] : [];
    $restaurants = isset($pars['restaurants']) ? (array)$pars['restaurants'] : [];

    $sensor_id = isset($pars['sensor_id']) ? (int)$pars['sensor_id'] : 0;
    $sensor = Restaurant::find($sensor_id);

    $restaurant_parent_id = isset($pars['restaurant_parent_id']) ? (int)$pars['restaurant_parent_id'] : 0;
    $restaurant = RestaurantParent::find($restaurant_parent_id);

    if (count($sensors) && $sensor) {
      $rows = RestaurantFood::where('restaurant_id', $sensor->id)
        ->where('deleted', 0)
        ->get()
        ->toArray();
      if (count($rows)) {
        foreach ($rows as $row) {
          unset($row['id']);
          unset($row['restaurant_id']);
          unset($row['created_at']);
          unset($row['updated_at']);

          foreach ($sensors as $itd) {

            $row['restaurant_id'] = $itd;

            RestaurantFood::create($row);
          }
        }
      }
    }

    if (count($restaurants) && $restaurant) {
      $rows = FoodIngredient::where('restaurant_parent_id', $restaurant->id)
        ->where('deleted', 0)
        ->get()
        ->toArray();
      if (count($rows)) {
        foreach ($rows as $row) {
          unset($row['id']);
          unset($row['restaurant_parent_id']);
          unset($row['created_at']);
          unset($row['updated_at']);

          foreach ($restaurants as $itd) {

            $row['restaurant_parent_id'] = $itd;

            FoodIngredient::create($row);
          }
        }
      }

      $rows = FoodRecipe::where('restaurant_parent_id', $restaurant->id)
        ->where('deleted', 0)
        ->get()
        ->toArray();
      if (count($rows)) {
        foreach ($rows as $row) {
          unset($row['id']);
          unset($row['restaurant_parent_id']);
          unset($row['created_at']);
          unset($row['updated_at']);

          foreach ($restaurants as $itd) {

            $row['restaurant_parent_id'] = $itd;

            FoodRecipe::create($row);
          }
        }
      }

      foreach ($restaurants as $itd) {
        //count
        $restaurant_parent = RestaurantParent::find($itd);
        $restaurant_parent->re_count();
      }
    }
  }

  protected function food_category_update()
  {
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

  protected function food_restaurant_sync()
  {
    $rows = RestaurantFood::where('deleted', 0)
      ->where('restaurant_parent_id', 0)
      ->get();
    if (count($rows)) {
      foreach ($rows as $row) {
        $row->update([
          'restaurant_parent_id' => $row->get_restaurant()->restaurant_parent_id,
        ]);
      }
    }

    RestaurantFood::whereIn('restaurant_id', [6, 8, 13])
      ->delete();

    RestaurantFood::where('deleted', 0)
      ->update([
        'restaurant_id' => 0,
      ]);
  }
}
