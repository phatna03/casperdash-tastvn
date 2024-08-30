<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KasStaff extends Model
{
  use HasFactory;

  public $table = 'kas_staffs';

  protected $fillable = [
    'kas_restaurant_id',
    'employee_id',
    'employee_code',
    'employee_name',
  ];
}
