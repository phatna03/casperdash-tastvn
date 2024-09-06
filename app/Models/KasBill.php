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

  public function get_orders($pars = [])
  {
    $select = KasBillOrder::where('kas_bill_id', $this->id)
      ->orderBy('id', 'asc');

    $deleted = count($pars) && isset($pars['deleted']) && (int)$pars['deleted'] ? 1 : 0;
    if ($deleted) {
      $select->where('status', 'deleted');
    } else {
      $select->where('status', '<>', 'deleted');
    }

    return $select->get();
  }

  public function get_orders_info()
  {
    $items = [];

    $orders = $this->get_orders();
    if (count($orders)) {
      foreach ($orders as $order) {
        $order_items = KasBillOrderItem::query('kas_bill_order_items')
          ->select('kas_bill_order_items.quantity', 'kas_bill_order_items.note as item_note',
            'kas_items.item_id', 'kas_items.item_name', 'kas_items.food_id', 'kas_items.food_name',
          )
          ->leftJoin('kas_items', 'kas_bill_order_items.kas_item_id', '=', 'kas_items.id')
          ->where('kas_bill_order_items.kas_bill_order_id', $order->id)
          ->where('kas_bill_order_items.status', '<>', 'deleted')
          ->get();

        $items[] = [
          'order_id' => $order->id,
          'order_kas_id' => $order->order_id,
          'order_note' => $order->note,

          'order_items' => $order_items,
        ];
      }
    }

    return $items;
  }
}
