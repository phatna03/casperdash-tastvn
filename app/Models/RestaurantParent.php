<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

use Aws\S3\S3Client;
use App\Api\SysCore;

class RestaurantParent extends Model
{
  use HasFactory;

  public $table = 'restaurant_parents';

  protected $fillable = [
    'name',
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
    $select = FoodIngredient::distinct()
      ->select('food_ingredients.food_id', 'foods.name')
      ->leftJoin('foods', 'foods.id', '=', 'food_ingredients.food_id')
      ->where('food_ingredients.restaurant_parent_id', $this->id)
      ->where('food_ingredients.deleted', 0)
      ->where('foods.deleted', 0)
      ->orderByRaw('TRIM(LOWER(foods.name))')
    ;

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
  //opt

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
        $count = $sensor->count_foods();
      }
    }

    $this->update([
      'count_foods' => $count,
    ]);
  }

  public function get_food_datas($pars = [])
  {
    //all sensors use same food list
    $select = RestaurantFood::query('restaurant_foods')
      ->distinct()
      ->select(
        'restaurant_foods.food_id', 'foods.name as food_name',
        'restaurant_foods.photo as food_photo', 'restaurant_foods.live_group as food_live_group',
        'restaurant_foods.food_category_id', 'food_categories.name as food_category_name'
      )
      ->where('restaurant_foods.deleted', 0)
      ->leftJoin('foods', 'foods.id', '=', 'restaurant_foods.food_id')
      ->leftJoin('food_categories', 'food_categories.id', '=', 'restaurant_foods.food_category_id')
      ->whereIn('restaurant_foods.restaurant_id', function ($q) {
        $q->select('id')
          ->from('restaurants')
          ->where('restaurant_parent_id', $this->id)
        ;
      })
      ->orderByRaw('TRIM(LOWER(foods.name))');

    return $select->get();
  }
}
