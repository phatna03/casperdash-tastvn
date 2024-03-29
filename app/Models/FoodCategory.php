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

  public function get_type()
  {
    return 'food_category';
  }

  public function get_log()
  {
    return [
      'name' => $this->name
    ];
  }
}
