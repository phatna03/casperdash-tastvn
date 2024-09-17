<?php

namespace App\Api;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Storage;
//lib
use App\Models\Food;
use App\Models\KasBill;
use App\Models\KasBillOrder;
use App\Models\KasBillOrderItem;
use App\Models\KasItem;
use App\Models\KasRestaurant;
use App\Models\KasStaff;
use App\Models\KasTable;
use App\Models\KasWebhook;

class SysKas
{
  public static function bill_check($pars = [])
  {
    $rows = KasWebhook::where('type', 'cart_info')
      ->where('restaurant_id', 0)
      ->orderBy('id', 'asc')
      ->limit(100)
      ->get();

    if (count($rows)) {
      foreach ($rows as $row) {
        $datas = json_decode($row->params, true);

        if (count($datas)) {

          if (!isset($datas['bill_id'])) {
            continue;
          }

          $kas_restaurant = KasRestaurant::where('restaurant_id', $datas['restaurant_id'])
            ->first();
          if (!$kas_restaurant) {
            $kas_restaurant = KasRestaurant::create([
              'restaurant_id' => $datas['restaurant_id'],
              'restaurant_code' => $datas['restaurant_code'],
              'restaurant_name' => $datas['restaurant_name'],
            ]);
          }

          $kas_table = KasTable::where('kas_restaurant_id', $kas_restaurant->id)
            ->where('area_id', $datas['area_id'])
            ->where('table_id', $datas['table_id'])
            ->first();
          if (!$kas_table) {
            $kas_table = KasTable::create([
              'kas_restaurant_id' => $kas_restaurant->id,
              'area_id' => $datas['area_id'],
              'area_name' => $datas['area_name'],
              'table_id' => $datas['table_id'],
              'table_name' => $datas['table_name'],
            ]);
          }

          $kas_staff = KasStaff::where('employee_id', $datas['employee_id'])
            ->first();
          if (!$kas_staff) {
            $kas_staff = KasStaff::create([
              'employee_id' => $datas['employee_id'],
              'employee_code' => $datas['employee_code'],
              'employee_name' => $datas['employee_name'],
            ]);
          }

          //bill
          $date_create = date('Y-m-d', strtotime($datas['time_create']));

          $kas_bill = KasBill::where('kas_restaurant_id', $kas_restaurant->id)
            ->where('kas_table_id', $kas_table->id)
            ->where('bill_id', $datas['bill_id'])
            ->where('date_create', $date_create)
            ->first();
          if (!$kas_bill) {
            $kas_bill = KasBill::create([
              'kas_restaurant_id' => $kas_restaurant->id,
              'kas_table_id' => $kas_table->id,

              'bill_id' => $datas['bill_id'],
              'date_create' => $date_create,

              'kas_staff_id' => $kas_staff->id,
              'time_create' => $datas['time_create'],

              'note' => $datas['note'],
            ]);
          } else {

            $kas_bill->update([
              'time_payment' => $datas['time_payment'],
              'status' => $datas['status'],
              'note' => $datas['note'],
            ]);
          }

          //order
          $kas_bill_order = KasBillOrder::where('kas_bill_id', $kas_bill->id)
            ->where('order_id', $datas['order_id'])
            ->first();
          if (!$kas_bill_order) {
            $kas_bill_order = KasBillOrder::create([
              'kas_bill_id' => $kas_bill->id,

              'order_id' => $datas['order_id'],
              'note' => $datas['note'],
            ]);
          } else {

            $kas_bill_order->update([
              'time_payment' => $datas['time_payment'],
              'status' => $datas['status'],
              'note' => $datas['note'],
            ]);
          }

          //order item
          if (count($datas['items'])) {
            foreach ($datas['items'] as $itm) {
              //item
              $kas_item = KasItem::where('item_id', $itm['item_id'])
                ->first();
              if (!$kas_item) {
                $kas_item = KasItem::create([
                  'item_id' => $itm['item_id'],
                  'item_code' => $itm['item_code'],
                  'item_name' => $itm['item_name'],
                ]);
              }

              //add to order
              $kas_bill_order_item = KasBillOrderItem::where('kas_bill_order_id', $kas_bill_order->id)
                ->where('kas_item_id', $kas_item->id)
                ->first();
              if (!$kas_bill_order_item) {
                $kas_bill_order_item = KasBillOrderItem::create([
                  'kas_bill_order_id' => $kas_bill_order->id,
                  'kas_item_id' => $kas_item->id,

                  'quantity' => $itm['quantity'],
                  'status' => $itm['status'],
                  'note' => $itm['note']
                ]);
              } else {

                $kas_bill_order_item->update([
                  'quantity' => $itm['quantity'],
                  'status' => $itm['status'],
                  'note' => $itm['note'],
                ]);
              }

            }
          }

        }

        $row->update([
          'restaurant_id' => 100,
        ]);
      }
    }
  }

  public static function bill_food_sync()
  {
    $rows = KasItem::where('food_id', 0)
      ->where(function ($q) {
        $q->where('date_check', NULL)
          ->orWhere('date_check', '<>', date('Y-m-d'));
      })
      ->orderBy('id', 'asc')
      ->limit(100)
      ->get();

    var_dump(count($rows));

    if (count($rows)) {

      $foods = Food::where('deleted', 0)
        ->get();

      foreach ($rows as $row) {
        var_dump(SysCore::var_dump_break());
        var_dump($row->item_name);

        $row->update([
          'date_check' => date('Y-m-d')
        ]);

        $food1 = 0;
        foreach ($foods as $food) {
          if (mb_strtolower($row->item_name) == mb_strtolower($food->name)) {
            $food1 = $food;

            break;
          }
        }

        if ($food1) {
          var_dump('FOOD_1= ' . $food1->id . ' - ' . $food1->name);

          $row->update([
            'web_food_id' => $food1->id,
            'web_food_name' => $food1->name,

            'food_id' => $food1->id,
            'food_name' => $food1->name,
          ]);
        }
        else {
          $food2 = 0;
          foreach ($foods as $food) {
            $temps = array_filter(explode('-', $food->name));

            if (count($temps)) {
              foreach ($temps as $temp_text) {
                if ($food2) {
                  break;
                }

                if (mb_strtolower($row->item_name) == mb_strtolower($temp_text)) {
                  $food2 = $food;

                  break;
                }
              }
            }
          }

          if ($food2) {
            var_dump('FOOD_2= ' . $food2->id . ' - ' . $food2->name);

            $row->update([
              'web_food_id' => $food2->id,
              'web_food_name' => $food2->name,

              'food_id' => $food2->id,
              'food_name' => $food2->name,
            ]);
          }
        }
      }
    }

    $rows = KasItem::where('food_id', '>', 0)
      ->get();

    var_dump(count($rows));
  }
}
