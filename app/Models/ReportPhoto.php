<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReportPhoto extends Model
{
  use HasFactory;

  public $table = 'report_photos';

  protected $fillable = [
    'report_id',
    'restaurant_food_scan_id',
    'food_id',
    'status',
    'point',
    'reporting',
    'note',
  ];

  public function get_type()
  {
    return 'report_photo';
  }

  public function get_rfs()
  {
    return RestaurantFoodScan::find($this->restaurant_food_scan_id);
  }
}
