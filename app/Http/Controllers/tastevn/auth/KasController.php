<?php

namespace App\Http\Controllers\tastevn\auth;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
//lib
use Validator;
use App\Api\SysApp;
use App\Api\SysCore;
use App\Excel\ImportData;
//model
use App\Models\Food;
use App\Models\RestaurantParent;
use App\Models\Ingredient;
use App\Models\Restaurant;
use App\Models\RestaurantFoodScan;
use App\Models\KasItem;
use App\Models\KasBill;
use App\Models\KasBillOrder;
use App\Models\KasBillOrderItem;
use App\Models\KasRestaurant;
use App\Models\KasStaff;
use App\Models\KasTable;
use App\Models\KasWebhook;

class KasController extends Controller
{
  protected $_viewer = null;
  protected $_sys_app = null;

  public function __construct()
  {
    $this->_sys_app = new SysApp();

    $this->middleware(function ($request, $next) {

      $this->_viewer = Auth::user();

      return $next($request);
    });

    $this->middleware('auth');
  }

  public function index(Request $request)
  {
    $invalid_roles = ['user', 'moderator'];
    if (in_array($this->_viewer->role, $invalid_roles)) {
      return redirect('error/404');
    }

    $pageConfigs = [
      'myLayout' => 'horizontal',
      'hasCustomizer' => false,
    ];

//    $this->_viewer->add_log([
//      'type' => 'view_listing_kas_food',
//    ]);

    return view('tastevn.pages.kas.foods', ['pageConfigs' => $pageConfigs]);
  }

  public function food_get(Request $request)
  {
    $rows = KasWebhook::where('type', 'cart_info')
      ->where('restaurant_id', 0)
      ->orderBy('id', 'asc')
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
          'restaurant_id' => 999,
        ]);
      }
    }

    $rows = KasItem::all();
    if (count($rows)) {

      $foods = Food::where('deleted', 0)
        ->get();

      foreach ($rows as $row) {

        $food1 = 0;
        foreach ($foods as $food) {
          if (mb_strtolower($row->item_name) == mb_strtolower($food->name)) {
            $food1 = $food;

            break;
          }
        }

        if ($food1) {
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

    return response()->json([
      'status' => true,
    ]);
  }

  public function food_item(Request $request)
  {
    $values = $request->post();

    //required
    $validator = Validator::make($values, [
      'item' => 'required|string',
      'food' => 'required|string',
    ]);
    if ($validator->fails()) {
      return response()->json($validator->errors(), 422);
    }

    $row = KasItem::find((int)$values['item']);
    $food = Food::find((int)$values['food']);
    if (!$row || !$food) {
      return response()->json([
        'error' => 'Invalid item'
      ], 422);
    }

    $row->update([
      'food_id' => $food->id,
      'food_name' => $food->name,
    ]);

    return response()->json([
      'status' => true,
    ]);
  }

  public function checker(Request $request)
  {
    $invalid_roles = ['user', 'moderator'];
    if (in_array($this->_viewer->role, $invalid_roles)) {
      return redirect('error/404');
    }

    $restaurants = RestaurantParent::where('deleted', 0)
      ->orderBy('id', 'asc')
      ->get();

    $pageConfigs = [
      'myLayout' => 'horizontal',
      'hasCustomizer' => false,

      'restaurants' => $restaurants,
    ];

//    $this->_viewer->add_log([
//      'type' => 'view_listing_kas_food',
//    ]);

    return view('tastevn.pages.kas.checker', ['pageConfigs' => $pageConfigs]);
  }

  public function date_check(Request $request)
  {
    $values = $request->post();

    //required
    $validator = Validator::make($values, [
      'date' => 'required|string',
      'restaurant' => 'required|string',
    ]);
    if ($validator->fails()) {
      return response()->json($validator->errors(), 422);
    }

    $temps = array_filter(explode('/', $values['date']));
    $date = $temps[2] . '-' . $temps[1] . '-' . $temps[0];

    $restaurant_parent = RestaurantParent::find((int)$values['restaurant']);
    $select_sensors = Restaurant::select('id')
      ->where('restaurant_parent_id', $restaurant_parent->id)
      ->where('deleted', 0);

//    $select = RestaurantFoodScan::query()
//      ->distinct()
//      ->selectRaw('HOUR(created_at) as hour')
//      ->where('deleted', 0)
//      ->whereDate('created_at', $date)
//      ->whereIn('restaurant_id', $select_sensors)
//      ->orderBy('hour', 'asc');

    $total_photos = RestaurantFoodScan::where('deleted', 0)
      ->whereDate('created_at', $date)
      ->whereIn('restaurant_id', $select_sensors)
      ->whereIn('status', ['checked', 'failed'])
      ->count();

    $total_orders = KasBill::query('kas_bills')
      ->leftJoin('kas_restaurants', 'kas_restaurants.id', '=', 'kas_bills.kas_restaurant_id')
      ->where('kas_restaurants.restaurant_parent_id', $restaurant_parent->id)
      ->where('kas_bills.date_create', $date)
      ->count();

    return response()->json([
      'status' => true,
      'date' => $date,
//      'query' => SysCore::str_db_query($select),
//      'items' => $select->get(),

      'total_orders' => $total_orders,
      'total_photos' => $total_photos,

    ]);
  }

}
