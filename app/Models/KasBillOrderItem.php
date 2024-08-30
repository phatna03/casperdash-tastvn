<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KasBillOrderItem extends Model
{
  use HasFactory;

  public $table = 'kas_bill_order_items';

  protected $fillable = [
    'kas_bill_order_id', 'kas_item_id',
    'quantity', 'status', 'note',

  ];
}
