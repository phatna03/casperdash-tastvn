<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

use Aws\S3\S3Client;
use App\Api\SysCore;

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

  //optimze







  public function add_foods($food_ids = [], $category_id = 0)
  {
    if (count($food_ids)) {
      foreach ($food_ids as $food_id) {
        $food = Food::find((int)$food_id);
        if (!$food) {
          continue;
        }

        $row = RestaurantFood::where('restaurant_id', $this->id)
          ->where('food_id', $food->id)
          ->first();
        if ($row) {
          $row->update([
            'food_category_id' => $category_id,
            'deleted' => 0,
          ]);
        } else {
          $row = RestaurantFood::create([
            'restaurant_id' => $this->id,
            'food_id' => $food->id,
            'food_category_id' => $category_id,
            'creator_id' => Auth::user() ? Auth::user()->id : 0,
          ]);
        }

        RestaurantFoodScan::where('restaurant_id', $this->id)
          ->where('food_id', $food->id)
          ->update([
            'food_category_id' => $category_id,
          ]);
      }
    }

    $this->count_foods();
  }

  public function delete_food($food)
  {
    if ($food) {

      RestaurantFood::where('restaurant_id', $this->id)
        ->where('food_id', $food->id)
        ->update([
          'deleted' => Auth::user() ? Auth::user()->id : 0,
        ]);

      RestaurantFoodScan::where('restaurant_id', $this->id)
        ->where('food_id', $food->id)
        ->update([
          'food_category_id' => 0,
        ]);
    }

    $this->count_foods();
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

  public function get_food_category($food)
  {
    //check later
    return NULL;

    $row = RestaurantFood::select("food_category_id")
      ->where("restaurant_id", 15)
      ->where("food_id", 4)
      ->first();

    return $row && $row->food_category_id ? FoodCategory::find($row->food_category_id) : NULL;
  }

  public function get_stats($type, $times = NULL)
  {
    $data = [];
    $api_core = new SysCore();

    $search_time_from = NULL;
    $search_time_to = NULL;

    if (!empty($times)) {
      $search_time_from = $api_core->parse_date_range($times)['time_from'];
      $search_time_to = $api_core->parse_date_range($times)['time_to'];
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

        $data['sql1'] = $api_core->parse_to_query($error_time_frame_list);
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

  //v3
  public function photo_s3($pars = [])
  {
    $api_core = new SysCore();

    $s3_region = $pars['s3_region'];
    $s3_api_key = $pars['s3_api_key'];
    $s3_api_secret = $pars['s3_api_secret'];
    $s3_bucket = $pars['s3_bucket'];
    $s3_address = $pars['s3_address'];

    $scan_date = $pars['scan_date'];
    $scan_hour = (int)$pars['scan_hour']; //9 not 09

    $file_log = $pars['file_log'];

    //run
    $this->update([
      's3_checking' => 1,
    ]);

    //s3 call
    $s3_api = new S3Client([
      'version' => 'latest',
      'region' => $s3_region,
      'credentials' => array(
        'key' => $s3_api_key,
        'secret' => $s3_api_secret
      )
    ]);

    $s3_objects = $s3_api->ListObjects([
      'Bucket' => $s3_bucket,
      'Delimiter' => '/',
//      'Prefix' => '58-5b-69-19-ad-67/SENSOR/1/2023-11-30/11/',
      'Prefix' => "{$s3_address}/{$scan_date}/{$scan_hour}/",
    ]);

    //s3 data
    if ($s3_objects && isset($s3_objects['Contents']) && count($s3_objects['Contents'])) {

      //group
      $s3_contents = [];
      foreach ($s3_objects['Contents'] as $content) {
        $s3_contents[] = [
          'key' => $content['Key'],
//        'date' => $content['LastModified']->format('Y-m-d H:i:s'), //UTC=0
          'date' => date('Y-m-d H:i:s', strtotime($content['LastModified']->__toString())),
        ];
      }

      //sort
      $a1 = [];
      $a2 = [];
      foreach ($s3_contents as $key => $row) {
        $a1[$key] = $row['date'];
        $a2[$key] = $row['key'];
      }
      array_multisort($a1, SORT_DESC, $a2, SORT_DESC, $s3_contents);

      //check
      foreach ($s3_contents as $content) {

        $s3_photo_url = "https://s3.{$s3_region}.amazonaws.com/{$s3_bucket}/{$content['key']}";
        $s3_photo_ext = explode('.', $content['key']);

        //valid photo
        if (@getimagesize($s3_photo_url)) {

          $api_core::_DEBUG ? Storage::append($file_log, 'PHOTO_NAME_' . $content['key']) : $api_core->log_failed();
          $api_core::_DEBUG ? Storage::append($file_log, 'PHOTO_URL_' . $s3_photo_url) : $api_core->log_failed();

          $row = RestaurantFoodScan::where('restaurant_id', $this->id)
            ->where('photo_name', $content['key'])
            ->first();
          if ($row) {
            break; //only find latest
          }

          $api_core::_DEBUG ? Storage::append($file_log, 'PHOTO_URL_SAVED') : $api_core->log_failed();

          //step 1= photo get
          $row = $this->photo_save([
            'photo_url' => $s3_photo_url,
            'photo_name' => $content['key'],
            'photo_ext' => $s3_photo_ext[1],
            'time_photo' => $content['date'],
          ]);

          $this->photo_scan($row);
        }
      }
    }

    $this->update([
      's3_checking' => 0,
    ]);
  }

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

  public function photo_scan($rfs)
  {
    $api_core = new SysCore();

    $rbf_dataset = $api_core->get_setting('rbf_dataset_scan');
    $rbf_api_key = $api_core->get_setting('rbf_api_key');

    $rfs_result = [];

    if ($rfs && $this->rbf_scan && !empty($rbf_dataset) && !empty($rbf_api_key)) {

      // URL for Http Request
      $rbf_url = "https://detect.roboflow.com/" . $rbf_dataset
        . "?api_key=" . $rbf_api_key
        . "&confidence=30&overlap=60&max_objects=100"
        . "&image=" . urlencode($rfs->get_photo());

      // Setup + Send Http request
      $rbf_options = array(
        'http' => array(
          'header' => "Content-type: application/x-www-form-urlencoded\r\n",
          'method' => 'POST'
        ));

      $rbf_context = stream_context_create($rbf_options);
      $rbf_result = file_get_contents($rbf_url, false, $rbf_context);
      if (!empty($rbf_result)) {
        $rfs_result = (array)json_decode($rbf_result);
      }

      //step 2= photo scan
      $rfs->update([
        'time_scan' => date('Y-m-d H:i:s'),
        'status' => count($rfs_result) ? 'scanned' : 'failed',
        'rbf_api' => count($rfs_result) ? json_encode($rfs_result) : NULL,
      ]);

      //step 3= photo predict
      $rfs->predict_food();
    }
  }
}
