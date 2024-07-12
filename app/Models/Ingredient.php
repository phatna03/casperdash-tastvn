<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ingredient extends Model
{
  use HasFactory;

  public $table = 'ingredients';

  protected $fillable = [
    'name',
    'name_vi',
    'creator_id',
    'deleted',
  ];

  public function get_type()
  {
    return 'ingredient';
  }

  public function get_log()
  {
    return [
      'name' => $this->name,
      'name_vi' => $this->name_vi,
    ];
  }

  public function get_ingredient_type($food)
  {
    $type = 'additive';

    if ($food) {
      $row = FoodIngredient::where('food_id', $food->id)
        ->where('ingredient_id', $this->id)
        ->first();
      if ($row) {
        $type = $row->ingredient_type;
      }
    }

    return $type;
  }

  public function on_update_after()
  {
    $this->missing_by_scans();
  }

  public function missing_by_scans()
  {
    $rows = RestaurantFoodScanMissing::select("restaurant_food_scan_id")
      ->distinct()
      ->where("ingredient_id", $this->id)
      ->get();

    if (count($rows)) {
      foreach ($rows as $row) {
        $scan = RestaurantFoodScan::find($row->restaurant_food_scan_id);
        if ($scan) {
          $scan->rfs_ingredients_missing_text();
        }
      }
    }
  }
}
