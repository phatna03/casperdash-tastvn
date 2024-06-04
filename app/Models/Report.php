<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
  use HasFactory;

  public $table = 'reports';

  protected $fillable = [
    'name',
    'restaurant_parent_id',
    'date_from',
    'date_to',
    'total_photos',
    'total_points',
    'point',
    'status',
    'deleted',
  ];

  public function get_type()
  {
    return 'report';
  }


}
