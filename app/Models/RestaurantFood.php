<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RestaurantFood extends Model
{
  use HasFactory;

  public $table = 'restaurant_foods';

  protected $fillable = [
    'restaurant_parent_id',
    'restaurant_id',
    'food_id',
    'food_category_id',
    'confidence',
    'photo',
    'local_storage',
    'live_group',
    'model_name',
    'model_version',
    'creator_id',
    'deleted',
  ];

  public function get_restaurant()
  {
    return Restaurant::find($this->restaurant_id);
  }

  public function get_food()
  {
    return Food::find($this->food_id);
  }

  public function get_food_category()
  {
    return FoodCategory::find($this->food_category_id);
  }

  public function count_restaurants()
  {
    //food
    $row = Food::find($this->food_id);
    if ($row) {
      $count = RestaurantFood::where('deleted', 0)
        ->where('food_id', $row->id)
        ->count();

      $row->update([
        'count_restaurants' => $count,
      ]);
    }
    //food category
    $row = FoodCategory::find($this->food_category_id);
    if ($row) {
      $rows = RestaurantFood::distinct()
        ->select('food_id')
        ->where('deleted', 0)
        ->where('food_category_id', $row->id)
        ->get();

      $row->update([
        'count_restaurants' => count($rows),
      ]);
    }
  }
}
