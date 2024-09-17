<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KasItem extends Model
{
  use HasFactory;

  public $table = 'kas_items';

  protected $fillable = [
    'kas_restaurant_id',
    'item_id',
    'item_code',
    'item_name',

    'web_food_id',
    'web_food_name',

    'food_id',
    'food_name',

    'date_check',
  ];
}
