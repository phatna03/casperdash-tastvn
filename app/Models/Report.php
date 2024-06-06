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
    'total_foods',
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

  public function get_log()
  {
    return [
      'name' => $this->name,
      'restaurant_parent_id' => $this->restaurant_parent_id,
      'date_from' => $this->date_from,
      'date_to' => $this->date_to,
    ];
  }

  public function start()
  {

  }


}
