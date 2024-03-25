<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RestaurantFoodScanText extends Model
{
  use HasFactory;

  public $table = 'restaurant_food_scan_texts';

  protected $fillable = [
    'restaurant_food_scan_id',
    'text_id',
  ];

}
