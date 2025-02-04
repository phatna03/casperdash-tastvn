<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReportFood extends Model
{
  use HasFactory;

  public $table = 'report_foods';

  protected $fillable = [
    'report_id',
    'food_id',
  ];

  public function get_type()
  {
    return 'report_food';
  }


}
