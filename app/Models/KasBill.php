<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KasBill extends Model
{
  use HasFactory;

  public $table = 'kas_bills';

  protected $fillable = [
    'kas_restaurant_id', 'kas_table_id', 'kas_staff_id',
    'bill_id', 'date_create', 'note',
    'time_create', 'time_payment', 'status',

  ];
}
