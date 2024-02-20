<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FoodCategory extends Model
{
  use HasFactory;

  public $table = 'food_categories';

  protected $fillable = [
    'name',
    'count_restaurants',
    'creator_id',
    'deleted',
  ];

}
