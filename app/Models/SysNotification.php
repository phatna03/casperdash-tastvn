<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SysNotification extends Model
{
  use HasFactory;

  public $table = 'notifications';

  protected $fillable = [
    'restaurant_food_scan_id',
    'restaurant_id',
    'food_id',
    'object_type',
    'object_id',
    'data',
  ];

  public function get_type()
  {
    return 'notification';
  }

}
