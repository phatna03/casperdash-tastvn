<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RestaurantStatsDate extends Model
{
  use HasFactory;

  public $table = 'restaurant_stats_dates';

  protected $fillable = [
    'restaurant_parent_id',
    'date',
    'total_files',
    'total_photos',
    'photo_used',
    'photo_dup',
    'photo_test',
    'total_bills',
    'total_foods',
  ];
}
