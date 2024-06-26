<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RestaurantAccess extends Model
{
//  use HasFactory;

  public $table = 'restaurant_access';

  protected $fillable = [
    'user_id',
    'restaurant_parent_id',
  ];
}
