<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RestaurantFoodScanMissing extends Model
{
  use HasFactory;

  public $table = 'restaurant_food_scan_missings';

  protected $fillable = [
    'restaurant_food_scan_id',
    'ingredient_id',
    'ingredient_quantity',
    'ingredient_type',
  ];

}
