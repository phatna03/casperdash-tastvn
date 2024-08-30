<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KasBillOrder extends Model
{
  use HasFactory;

  public $table = 'kas_bill_orders';

  protected $fillable = [
    'kas_bill_id', 'order_id', 'status',
    'note',

  ];
}
