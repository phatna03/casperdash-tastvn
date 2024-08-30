<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KasRestaurant extends Model
{
  use HasFactory;

  public $table = 'kas_restaurants';

  protected $fillable = [
    'restaurant_parent_id',
    'restaurant_id',
    'restaurant_code',
    'restaurant_name',
  ];
}
