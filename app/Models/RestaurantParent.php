<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

use Aws\S3\S3Client;

class RestaurantParent extends Model
{
  use HasFactory;

  public $table = 'restaurant_parents';

  protected $fillable = [
    'name',
    'model_name',
    'model_version',
    'model_scan',
    'count_sensors',
    'count_foods',
    'creator_id',
    'deleted',
  ];

  public function get_type()
  {
    return 'restaurant_parent';
  }

  public function get_log()
  {
    return [
      'name' => $this->name,
      'model_name' => $this->model_name,
      'model_version' => $this->model_version,
      'model_scan' => $this->model_scan,
    ];
  }

  public function on_create_after($pars = [])
  {

  }

  public function on_update_after($pars = [])
  {

  }

  public function on_delete_after($pars = [])
  {

  }

  public function on_restore_after($pars = [])
  {

  }

  public function get_foods($pars = [])
  {
    $keyword = isset($pars['keyword']) && !empty($pars['keyword']) ? $pars['keyword'] : NULL;
    $select_data = isset($pars['select_data']) && !empty($pars['select_data']) ? $pars['select_data'] : NULL;
    $live_group = isset($pars['live_group']) && !empty($pars['live_group']) ? (int)$pars['live_group'] : 0;

    $select = RestaurantFood::query('restaurant_foods')
      ->distinct()
      ->where('restaurant_foods.restaurant_parent_id', $this->id)
      ->where('restaurant_foods.deleted', 0)
      ->where('foods.deleted', 0)
      ->leftJoin('foods', 'foods.id', '=', 'restaurant_foods.food_id')
      ->leftJoin('food_categories', 'food_categories.id', '=', 'restaurant_foods.food_category_id')
      ->orderByRaw('TRIM(LOWER(foods.name))');

    if (!empty($keyword)) {
      $select->where('foods.name', 'LIKE', "%{$keyword}%");
    }

    if ($live_group) {
      $select->where('restaurant_foods.live_group', $live_group);
    }

    switch ($select_data) {
      case 'food_only':
        $select->select('foods.id', 'foods.name');
        break;

      default:
        $select->select(
          'restaurant_foods.food_id', 'foods.name as food_name',
          'restaurant_foods.live_group as food_live_group',
          'restaurant_foods.model_name as food_model_name', 'restaurant_foods.model_version as food_model_version',
          'restaurant_foods.photo as food_photo', 'restaurant_foods.local_storage',
          'restaurant_foods.food_category_id', 'food_categories.name as food_category_name'
        );
    }

    return $select->get();
  }

  public function get_sensors($pars = [])
  {
    $deleted = isset($pars['deleted']) && (int)$pars['deleted'] ? (int)$pars['deleted'] : 0;
    $one_sensor = isset($pars['one_sensor']) && (int)$pars['one_sensor'] ? (int)$pars['one_sensor'] : 0;

    $select = Restaurant::where('restaurant_parent_id', $this->id);

    if ($deleted) {
      $select->where('deleted', '>', 0);
    } else {
      $select->where('deleted', 0);
    }

    if ($one_sensor) {
      $select->orderBy('id', 'asc')
        ->limit(1);

      return $select->first();
    }

    return $select->get();
  }

  public function food_serve(Food $food)
  {
    $row = RestaurantFood::where('deleted', 0)
      ->where('restaurant_parent_id', $this->id)
      ->where('food_id', $food->id)
      ->first();

    return $row ? true : false;
  }

  public function re_count($pars = [])
  {
    $this->count_sensors();
    $this->count_foods();
  }

  public function count_sensors()
  {
    $count = Restaurant::distinct()
      ->select('id')
      ->where('restaurant_parent_id', $this->id)
      ->where('deleted', 0)
      ->count();

    $this->update([
      'count_sensors' => $count,
    ]);
  }

  public function count_foods()
  {
    //all sensors use same food list
    $count = 0;

    $sensors = Restaurant::where('restaurant_parent_id', $this->id)
      ->get();
    if (count($sensors)) {
      foreach ($sensors as $sensor) {
        $count_foods = $sensor->count_foods();
        if (!$count) {
          $count = $count_foods;
        }
      }
    }

    $this->update([
      'count_foods' => $count,
    ]);
  }

  public function get_food_photo(Food $food)
  {
    $photo = url('custom/img/logo_' . $this->id . '.png');

    $row = RestaurantFood::where('deleted', 0)
      ->where('restaurant_parent_id', $this->id)
      ->where('food_id', $food->id)
      ->first();

    if ($row) {
      $photo = $row->photo;

      if ($row->local_storage) {
        $photo = url('photos/foods') . '/' . $row->photo;
      }
    }

    return $photo;
  }

  public function get_food_category(Food $food)
  {
    $food_category = NULL;

    $row = RestaurantFood::where('deleted', 0)
      ->where('restaurant_parent_id', $this->id)
      ->where('food_id', $food->id)
      ->first();

    if ($row) {
      $food_category = FoodCategory::find($row->food_category_id);
    }

    return $food_category;
  }

  public function get_food_live_group(Food $food)
  {
    $row = RestaurantFood::where('deleted', 0)
      ->where('restaurant_parent_id', $this->id)
      ->where('food_id', $food->id)
      ->first();

    return $row ? $row->live_group : 3;
  }

  public function get_food_model_name(Food $food)
  {
    $row = RestaurantFood::where('deleted', 0)
      ->where('restaurant_parent_id', $this->id)
      ->where('food_id', $food->id)
      ->first();

    return $row ? $row->model_name : NULL;
  }

  public function get_food_model_version(Food $food)
  {
    $row = RestaurantFood::where('deleted', 0)
      ->where('restaurant_parent_id', $this->id)
      ->where('food_id', $food->id)
      ->first();

    return $row ? $row->model_version : NULL;
  }
}
