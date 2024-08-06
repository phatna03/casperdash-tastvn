<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

use Aws\S3\S3Client;
use App\Api\SysApp;
use App\Api\SysRobo;

class Restaurant extends Model
{
  use HasFactory;

  public $table = 'restaurants';

  protected $fillable = [
    'restaurant_parent_id',
    'name',
    's3_bucket_name',
    's3_bucket_address',
    's3_checking',
    'count_foods',
    'creator_id',

    'rbf_scan', //temp off
    'img_1024', //temp off
    'deleted',
  ];

  public function get_type()
  {
    return 'restaurant';
  }

  public function get_log()
  {
    return [
      'restaurant_parent_id' => $this->restaurant_parent_id,
      'name' => $this->name,
      's3_bucket_name' => $this->s3_bucket_name,
      's3_bucket_address' => $this->s3_bucket_address,
      'rbf_scan' => $this->rbf_scan,
    ];
  }

  public function creator()
  {
    return $this->belongsTo('App\Models\User', 'creator_id');
  }

  public function on_create_after($pars = [])
  {

  }

  public function on_update_after($pars = [])
  {
//    $this->access_by_users();
  }

  public function on_delete_after($pars = [])
  {
//    RestaurantAccess::where('restaurant_id', $this->id)
//      ->delete();
//
//    $this->access_by_users();
  }

  public function on_restore_after($pars = [])
  {

  }

  public function access_by_users()
  {
    $users = User::where('access_full', 0)
      ->where(function ($q) {
        $q->whereRaw('LOWER(access_ids) LIKE ?', "%{$this->id}%")
          ->orWhereRaw('LOWER(access_ids) LIKE ?', "%{$this->id}")
          ->orWhereRaw('LOWER(access_ids) LIKE ?', "{$this->id}%");
      })
      ->get();
    if (count($users)) {
      foreach ($users as $user) {
        $user->access_restaurants();
      }
    }
  }

  public function get_parent()
  {
    return RestaurantParent::find($this->restaurant_parent_id);
  }

  public function get_users()
  {
    $tblUser = app(User::class)->getTable();
    $tblRestaurantAccess = app(RestaurantAccess::class)->getTable();

    $select = User::query($tblUser)
      ->select("$tblUser.*")
      ->distinct()
      ->leftJoin("$tblRestaurantAccess", "$tblRestaurantAccess.user_id", "=", "$tblUser.id")
      ->where("$tblUser.deleted", 0)
      ->where("$tblUser.status", "active")
      ->where(function ($q) use ($tblUser, $tblRestaurantAccess) {
        $q->where("$tblUser.access_full", 1)
          ->orWhere("$tblRestaurantAccess.restaurant_parent_id", $this->restaurant_parent_id);
      });

    return $select->get();
  }

  public function get_stats($type, $times = NULL)
  {
    $data = [];
    $sys_app = new SysApp();

    $search_time_from = NULL;
    $search_time_to = NULL;

    if (!empty($times)) {
      $search_time_from = $sys_app->parse_date_range($times)['time_from'];
      $search_time_to = $sys_app->parse_date_range($times)['time_to'];
    }

    $status_valid = ['checked', 'failed'];
    $restaurant_parent = $this->get_parent();

    //group = super-confidence
    $food_ids = $restaurant_parent->get_foods([
      'live_group' => 1,
      'select_data' => 'food_ids',
    ]);

    switch ($type) {
      case 'total':

        $total_found = RestaurantFoodScan::query("restaurant_food_scans")
          ->distinct()
          ->where('restaurant_food_scans.deleted', 0)
          ->where('restaurant_food_scans.restaurant_id', $this->id)
          ->whereIn('restaurant_food_scans.status', $status_valid)
          ->whereIn('restaurant_food_scans.food_id', $food_ids)
        ;

        $today_found = RestaurantFoodScan::query("restaurant_food_scans")
          ->distinct()
          ->where('restaurant_food_scans.deleted', 0)
          ->where('restaurant_food_scans.restaurant_id', $this->id)
          ->whereIn('restaurant_food_scans.status', $status_valid)
          ->whereDate('restaurant_food_scans.time_photo', date('Y-m-d'))
          ->whereIn('restaurant_food_scans.food_id', $food_ids)
        ;

        //food category
        $error_food_category = RestaurantFoodScan::query("restaurant_food_scans")
          ->distinct()
          ->where('restaurant_food_scans.deleted', 0)
          ->where('restaurant_food_scans.restaurant_id', $this->id)
          ->whereIn('restaurant_food_scans.status', $status_valid)
          ->where('restaurant_food_scans.food_category_id', '>', 0)
          ->where('restaurant_food_scans.missing_ids', '<>', NULL)
          ->whereIn('restaurant_food_scans.food_id', $food_ids)
        ;

        //food
        $error_food = RestaurantFoodScan::query("restaurant_food_scans")
          ->distinct()
          ->where('restaurant_food_scans.deleted', 0)
          ->where('restaurant_food_scans.restaurant_id', $this->id)
          ->whereIn('restaurant_food_scans.status', $status_valid)
          ->where('restaurant_food_scans.food_id', '>', 0)
          ->where('restaurant_food_scans.missing_ids', '<>', NULL)
          ->whereIn('restaurant_food_scans.food_id', $food_ids)
        ;

        //ingredient missing
        $error_ingredient_missing = RestaurantFoodScanMissing::query("restaurant_food_scan_missings")
          ->leftJoin("restaurant_food_scans", "restaurant_food_scans.id", "=", "restaurant_food_scan_missings.restaurant_food_scan_id")
          ->where('restaurant_food_scans.deleted', 0)
          ->where('restaurant_food_scans.restaurant_id', $this->id)
          ->whereIn('restaurant_food_scans.status', $status_valid)
          ->where('restaurant_food_scans.missing_ids', '<>', NULL)
          ->where('restaurant_food_scans.food_id', '>', 0)
          ->whereIn('restaurant_food_scans.food_id', $food_ids)
        ;

        //time frames
        $error_time_frame = RestaurantFoodScan::query("restaurant_food_scans")
          ->where('restaurant_food_scans.deleted', 0)
          ->where('restaurant_food_scans.restaurant_id', $this->id)
          ->whereIn('restaurant_food_scans.status', $status_valid)
          ->where('restaurant_food_scans.food_id', '>', 0)
          ->where('restaurant_food_scans.missing_ids', '<>', NULL)
          ->whereIn('restaurant_food_scans.food_id', $food_ids)
        ;

        //search params
        if (!empty($search_time_from)) {
          $total_found->where('restaurant_food_scans.time_photo', '>=', $search_time_from);
          $error_food_category->where('restaurant_food_scans.time_photo', '>=', $search_time_from);
          $error_food->where('restaurant_food_scans.time_photo', '>=', $search_time_from);
          $error_ingredient_missing->where('restaurant_food_scans.time_photo', '>=', $search_time_from);
          $error_time_frame->where('restaurant_food_scans.time_photo', '>=', $search_time_from);
        }
        if (!empty($search_time_to)) {
          $total_found->where('restaurant_food_scans.time_photo', '<=', $search_time_to);
          $error_food_category->where('restaurant_food_scans.time_photo', '<=', $search_time_to);
          $error_food->where('restaurant_food_scans.time_photo', '<=', $search_time_to);
          $error_ingredient_missing->where('restaurant_food_scans.time_photo', '<=', $search_time_to);
          $error_time_frame->where('restaurant_food_scans.time_photo', '<=', $search_time_to);
        }

        $data['total_found'] = $total_found->count();
        $data['today_found'] = $today_found->count();

        //food category
        $error_food_category_list = clone $error_food_category;

        $error_food_category = $error_food_category->select('restaurant_food_scans.food_category_id')
          ->get();
        $error_food_category_list->select('restaurant_food_scans.food_category_id', 'food_categories.name as food_category_name')
          ->selectRaw('COUNT(restaurant_food_scans.id) as total_error')
          ->leftJoin("food_categories", "food_categories.id", "=", "restaurant_food_scans.food_category_id")
          ->groupBy(['restaurant_food_scans.food_category_id', 'food_categories.name'])
          ->orderBy('total_error', 'desc');

        $data['category_error'] = count($error_food_category);
        $data['category_error_list'] = $error_food_category_list->get();
        $data['category_error_percent'] = 0; //no

        //food
        $error_food_list = clone $error_food;

        $error_food_list->select('restaurant_food_scans.food_id', 'foods.name as food_name')
          ->selectRaw('COUNT(restaurant_food_scans.id) as total_error')
          ->leftJoin("foods", "foods.id", "=", "restaurant_food_scans.food_id")
          ->groupBy(['restaurant_food_scans.food_id', 'foods.name'])
          ->orderBy('total_error', 'desc');

        $data['food_error'] = count($error_food->get());
        $data['food_error_list'] = $error_food_list->get();
        $data['food_error_percent'] = $total_found->count() ?
          (int)(count($error_food->get()) / $total_found->count() * 100) : 0;

        //ingredient missing
        $error_ingredient_missing_list = clone $error_ingredient_missing;

        $error_ingredient_missing_list->select('restaurant_food_scan_missings.ingredient_id', 'ingredients.name as ingredient_name')
          ->selectRaw('SUM(restaurant_food_scan_missings.ingredient_quantity) as total_error')
          ->leftJoin("ingredients", "ingredients.id", "=", "restaurant_food_scan_missings.ingredient_id")
          ->groupBy(['restaurant_food_scan_missings.ingredient_id', 'ingredients.name'])
          ->orderBy('total_error', 'desc');

        $data['ingredient_missing'] = $error_ingredient_missing->sum('ingredient_quantity');
        $data['ingredient_missing_list'] = $error_ingredient_missing_list->get();
        $data['ingredient_missing_percent'] = 0; //no

        //time frames
        $error_time_frame_list = clone $error_time_frame;

        $error_time_frame_list->select(DB::raw('hour(restaurant_food_scans.time_photo) as hour_error'),
          DB::raw('COUNT(restaurant_food_scans.id) as total_error'))
          ->groupBy(DB::raw('hour(restaurant_food_scans.time_photo)'))
          ->orderBy('total_error', 'desc');

        $data['time_frame'] = count($error_time_frame_list->get());
        $data['time_frame_list'] = $error_time_frame_list->get();

        $data['sql1'] = $sys_app->parse_to_query($error_time_frame_list);
//        $data['search_time_from'] = $search_time_from;
//        $data['search_time_to'] = $search_time_to;

        break;
    }

    return $data;
  }

  public function get_stats_by_conditions($pars = [])
  {
    $data = [];
    $sys_app = new SysApp();

    $search_time_from = NULL;
    $search_time_to = NULL;

    $times = isset($pars['times']) ? $pars['times'] : NULL;
    $item_type = isset($pars['item_type']) ? $pars['item_type'] : 'food';
    $item_id = isset($pars['item_id']) ? (int)$pars['item_id'] : 0;


    if (!empty($times)) {
      $search_time_from = $sys_app->parse_date_range($times)['time_from'];
      $search_time_to = $sys_app->parse_date_range($times)['time_to'];
    }

    $status_valid = ['checked', 'failed'];
    $restaurant_parent = $this->get_parent();

    //group = super-confidence
    $food_ids = $restaurant_parent->get_foods([
      'live_group' => 1,
      'select_data' => 'food_ids',
    ]);

    //food category
    $error_food_category = RestaurantFoodScan::query("restaurant_food_scans")
      ->distinct()
      ->where('restaurant_food_scans.deleted', 0)
      ->where('restaurant_food_scans.restaurant_id', $this->id)
      ->whereIn('restaurant_food_scans.status', $status_valid)
      ->where('restaurant_food_scans.food_category_id', '>', 0)
      ->where('restaurant_food_scans.missing_ids', '<>', NULL)
      ->whereIn('restaurant_food_scans.food_id', $food_ids)
    ;

    //food
    $error_food = RestaurantFoodScan::query("restaurant_food_scans")
      ->distinct()
      ->where('restaurant_food_scans.deleted', 0)
      ->where('restaurant_food_scans.restaurant_id', $this->id)
      ->whereIn('restaurant_food_scans.status', $status_valid)
      ->where('restaurant_food_scans.food_id', '>', 0)
      ->where('restaurant_food_scans.missing_ids', '<>', NULL)
      ->whereIn('restaurant_food_scans.food_id', $food_ids)
    ;

    //ingredient missing
    $error_ingredient_missing = RestaurantFoodScanMissing::query("restaurant_food_scan_missings")
      ->leftJoin("restaurant_food_scans", "restaurant_food_scans.id", "=", "restaurant_food_scan_missings.restaurant_food_scan_id")
      ->where('restaurant_food_scans.deleted', 0)
      ->where('restaurant_food_scans.restaurant_id', $this->id)
      ->whereIn('restaurant_food_scans.status', $status_valid)
      ->where('restaurant_food_scans.missing_ids', '<>', NULL)
      ->where('restaurant_food_scans.food_id', '>', 0)
      ->whereIn('restaurant_food_scans.food_id', $food_ids)
    ;

    //time frames
    $error_time_frame = RestaurantFoodScan::query("restaurant_food_scans")
      ->where('restaurant_food_scans.deleted', 0)
      ->where('restaurant_food_scans.restaurant_id', $this->id)
      ->whereIn('restaurant_food_scans.status', $status_valid)
      ->where('restaurant_food_scans.food_id', '>', 0)
      ->where('restaurant_food_scans.missing_ids', '<>', NULL)
      ->whereIn('restaurant_food_scans.food_id', $food_ids)
    ;

    //search params
    if (!empty($search_time_from)) {
      $error_food_category->where('restaurant_food_scans.time_photo', '>=', $search_time_from);
      $error_food->where('restaurant_food_scans.time_photo', '>=', $search_time_from);
      $error_ingredient_missing->where('restaurant_food_scans.time_photo', '>=', $search_time_from);
      $error_time_frame->where('restaurant_food_scans.time_photo', '>=', $search_time_from);
    }
    if (!empty($search_time_to)) {
      $error_food_category->where('restaurant_food_scans.time_photo', '<=', $search_time_to);
      $error_food->where('restaurant_food_scans.time_photo', '<=', $search_time_to);
      $error_ingredient_missing->where('restaurant_food_scans.time_photo', '<=', $search_time_to);
      $error_time_frame->where('restaurant_food_scans.time_photo', '<=', $search_time_to);
    }

    //food category
    if ($item_type == 'food_category') {
      $error_food_category_list = clone $error_food_category;

      if ($item_id) {
        $error_food_category_list->select('restaurant_food_scans.id')
          ->leftJoin("food_categories", "food_categories.id", "=", "restaurant_food_scans.food_category_id")
          ->where('food_categories.id', $item_id);
      } else {
        $error_food_category_list->select('restaurant_food_scans.id');
      }

      $data = $error_food_category_list->get()->toArray();
    }

    //food
    if ($item_type == 'food') {
      $error_food_list = clone $error_food;

      if ($item_id) {
        $error_food_list->select('restaurant_food_scans.id')
          ->leftJoin("foods", "foods.id", "=", "restaurant_food_scans.food_id")
          ->where('restaurant_food_scans.food_id', $item_id);
      } else {
        $error_food_list->select('restaurant_food_scans.id');
      }

      $data = $error_food_list->get()->toArray();
    }

    //ingredient missing
    if ($item_type == 'ingredient') {
      $error_ingredient_missing_list = clone $error_ingredient_missing;

      if ($item_id) {
        $error_ingredient_missing_list->select('restaurant_food_scans.id')
          ->leftJoin("ingredients", "ingredients.id", "=", "restaurant_food_scan_missings.ingredient_id")
          ->where('ingredients.id', $item_id);
      } else {
        $error_ingredient_missing_list->select('restaurant_food_scans.id');
      }

      $data = $error_ingredient_missing_list->get()->toArray();
    }

    //time frames
    if ($item_type == 'hour') {
      $error_time_frame_list = clone $error_time_frame;

      if ($item_id) {
        $error_time_frame_list->select('restaurant_food_scans.id')
          ->where(DB::raw("HOUR(restaurant_food_scans.time_photo)"), $item_id);
      } else {
        $error_time_frame_list->select('restaurant_food_scans.id');
      }

      $data = $error_time_frame_list->get()->toArray();
    }

    return $data;
  }

  //photooo
  public function photo_save($pars = [])
  {
    $row = RestaurantFoodScan::where('restaurant_id', $this->id)
      ->where('photo_name', $pars['photo_name'])
      ->first();

    if (!$row) {
      $row = RestaurantFoodScan::create([
        'restaurant_id' => $this->id,

        'photo_url' => isset($pars['photo_url']) ? $pars['photo_url'] : NULL,
        'local_storage' => isset($pars['local_storage']) ? (int)$pars['local_storage'] : 0,

        'photo_name' => $pars['photo_name'],
        'photo_ext' => $pars['photo_ext'],
        'time_photo' => $pars['time_photo'],

        'status' => isset($pars['status']) ? $pars['status'] : 'new',
      ]);

      if ($row->status == 'new') {
        $row->photo_1024_create();
      }
    }

    return $row;
  }

}
