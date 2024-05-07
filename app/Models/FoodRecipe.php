<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FoodRecipe extends Model
{
  use HasFactory;

  public $table = 'food_recipes';

  protected $fillable = [
    'food_id',
    'restaurant_parent_id',
    'ingredient_id',
    'ingredient_quantity',
    'deleted',
  ];

}
