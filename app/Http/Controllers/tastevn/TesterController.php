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
    $date = date('Y-m-d');



//    $arr = $this->photo_duplicate([
//      //market test
//      'restaurant_id' => 5,
//
//      'date_from' => '2024-06-21',
//      'date_to' => '2024-06-22',
//
////      'rfs_id' => 43496,
//    ]);

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

  protected function notify_remove()
  {

  }
}
