<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FoodIngredient extends Model
{
  use HasFactory;

  public $table = 'food_ingredients';

  protected $fillable = [
    'food_id',
    'restaurant_parent_id',
    'ingredient_type',
    'ingredient_id',
    'ingredient_quantity',
    'ingredient_color',
    'confidence',
    'deleted',
  ];

  public function get_ingredient()
  {
    return Ingredient::find($this->ingredient_id);
  }
}
