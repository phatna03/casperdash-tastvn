<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TastevnItem extends Model
{
  use HasFactory;

  public $table = 'tastevn_items';

  protected $fillable = [
    'restaurant_parent_id',
    'item_code',
    'item_name',

    'food_id',
    'food_name',
  ];
}
