<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

use Aws\S3\S3Client;
use App\Api\SysApp;

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
    'rbf_scan',
    'count_foods',
    'creator_id',
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

  public function import_foods($datas = [])
  {
    $user = Auth::user();

    if (count($datas)) {

      //import only
      foreach ($datas as $data) {

        $row = RestaurantFood::where('restaurant_id', $this->id)
          ->where('food_id', (int)$data['food_id'])
          ->first();
        if (!$row) {
          $row = RestaurantFood::create([
            'restaurant_id' => $this->id,
            'food_id' => (int)$data['food_id'],
            'creator_id' => $user->id,
          ]);
        }

        $row->update([
          'food_category_id' => isset($data['food_category_id']) ? (int)$data['food_category_id'] : 0,
          'photo' => isset($data['photo']) ? $data['photo'] : NULL,
          'live_group' => isset($data['live_group']) ? (int)$data['live_group'] : 3,
          'deleted' => 0,
        ]);

      }
    }

  }

  public function count_foods()
  {
    $count = RestaurantFood::distinct()
      ->select('food_id')
      ->where('restaurant_id', $this->id)
      ->where('deleted', 0)
      ->count();

    $this->update([
      'count_foods' => $count,
    ]);

    return $count;
  }

  public function on_create_after($pars = [])
  {

  }

  public function on_update_after($pars = [])
  {
    $this->access_by_users();
  }

  public function on_delete_after($pars = [])
  {
    RestaurantAccess::where('restaurant_id', $this->id)
      ->delete();

    $this->access_by_users();
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
          ->orWhere("$tblRestaurantAccess.restaurant_id", $this->id);
      })
    ;

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

    switch ($type) {
      case 'total':

        $total_found = RestaurantFoodScan::query("restaurant_food_scans")
          ->distinct()
          ->where('restaurant_food_scans.deleted', 0)
          ->where('restaurant_food_scans.restaurant_id', $this->id)
          ->whereIn('restaurant_food_scans.status', $status_valid)
        ;
        $today_found = RestaurantFoodScan::query("restaurant_food_scans")
          ->distinct()
          ->where('restaurant_food_scans.deleted', 0)
          ->where('restaurant_food_scans.restaurant_id', $this->id)
          ->whereIn('restaurant_food_scans.status', $status_valid)
          ->whereDate('restaurant_food_scans.time_photo', date('Y-m-d'))
        ;

        //food category
        $error_food_category = RestaurantFoodScan::query("restaurant_food_scans")
          ->distinct()
          ->where('restaurant_food_scans.deleted', 0)
          ->where('restaurant_food_scans.restaurant_id', $this->id)
          ->whereIn('restaurant_food_scans.status', $status_valid)
          ->where('restaurant_food_scans.food_category_id', '>', 0)
          ->where('restaurant_food_scans.missing_ids', '<>', NULL)
        ;

        //food
        $error_food = RestaurantFoodScan::query("restaurant_food_scans")
          ->distinct()
          ->where('restaurant_food_scans.deleted', 0)
          ->where('restaurant_food_scans.restaurant_id', $this->id)
          ->whereIn('restaurant_food_scans.status', $status_valid)
          ->where('restaurant_food_scans.food_id', '>', 0)
          ->where('restaurant_food_scans.missing_ids', '<>', NULL)
        ;

        //ingredient missing
        $error_ingredient_missing = RestaurantFoodScanMissing::query("restaurant_food_scan_missings")
          ->leftJoin("restaurant_food_scans", "restaurant_food_scans.id", "=", "restaurant_food_scan_missings.restaurant_food_scan_id")
          ->where('restaurant_food_scans.deleted', 0)
          ->where('restaurant_food_scans.restaurant_id', $this->id)
          ->whereIn('restaurant_food_scans.status', $status_valid)
          ->where('restaurant_food_scans.missing_ids', '<>', NULL)
          ->where('restaurant_food_scans.food_id', '>', 0)
        ;

        //time frames
        $error_time_frame = RestaurantFoodScan::query("restaurant_food_scans")
          ->where('restaurant_food_scans.deleted', 0)
          ->where('restaurant_food_scans.restaurant_id', $this->id)
          ->whereIn('restaurant_food_scans.status', $status_valid)
          ->where('restaurant_food_scans.food_id', '>', 0)
          ->where('restaurant_food_scans.missing_ids', '<>', NULL)
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
          ->orderBy('total_error', 'desc')
        ;

        $data['time_frame'] = count($error_time_frame_list->get());
        $data['time_frame_list'] = $error_time_frame_list->get();

        $data['sql1'] = $sys_app->parse_to_query($error_time_frame_list);
//        $data['search_time_from'] = $search_time_from;
//        $data['search_time_to'] = $search_time_to;

        break;
    }

    return $data;
  }

  public function serve_food($food)
  {
    $rows = [];

    if ($food) {
      $rows = RestaurantFood::where('restaurant_id', $this->id)
        ->where('deleted', 0)
        ->where('food_id', $food->id)
        ->get();
    }

    return count($rows) ? true : false;
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
        'status' => 'new',

        'photo_url' => isset($pars['photo_url']) ? $pars['photo_url'] : NULL,
        'local_storage' => isset($pars['local_storage']) ? (int)$pars['local_storage'] : 0,

        'photo_name' => $pars['photo_name'],
        'photo_ext' => $pars['photo_ext'],
        'time_photo' => $pars['time_photo'],
      ]);
    }

    return $row;
  }
}
