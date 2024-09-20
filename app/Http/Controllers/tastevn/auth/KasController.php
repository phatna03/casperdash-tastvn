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
          'restaurant_id' => 999,
        ]);
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
      'restaurant' => 'required',
    ]);
    if ($validator->fails()) {
      return response()->json($validator->errors(), 422);
    }

    $temps = array_filter(explode('/', $values['date']));
    $date = $temps[2] . '-' . $temps[1] . '-' . $temps[0];

    $restaurant_parent = RestaurantParent::find((int)$values['restaurant']);

    $datas = $restaurant_parent->kas_checker_by_date($date);

    return response()->json([
      'status' => true,
      'date' => $date,

      'total_orders' => $datas['total_orders'],
      'total_photos' => $datas['total_photos'],
    ]);
  }

  public function date_check_month(Request $request)
  {
    $values = $request->post();

    //required
    $validator = Validator::make($values, [
      'month' => 'required',
      'year' => 'required',
    ]);
    if ($validator->fails()) {
      return response()->json($validator->errors(), 422);
    }

    $datas = [];
    $year = $values['year'];
    $month = $values['month'];

    $restaurants = RestaurantParent::where('deleted', 0)
      ->orderBy('id', 'asc')
      ->get();

    foreach ($restaurants as $restaurant) {

      $select_bills = KasBill::query('kas_bills')
        ->distinct()
        ->select('kas_bills.date_create')
        ->selectRaw('COUNT(kas_bills.bill_id) as total_bills')
        ->leftJoin('kas_restaurants', 'kas_restaurants.id', '=', 'kas_bills.kas_restaurant_id')
        ->whereMonth('kas_bills.date_create', $month)
        ->whereYear('kas_bills.date_create', $year)
//        ->where('kas_bills.status', 'paid')
        ->where('kas_restaurants.restaurant_parent_id', $restaurant->id)
        ->orderBy('kas_bills.date_create', 'desc')
        ->groupBy('kas_bills.date_create')
        ->groupBy('kas_restaurants.restaurant_parent_id')
      ;
      $total_bills = $select_bills->get()
        ->toArray();

      $select_sensors = Restaurant::select('id')
        ->where('restaurant_parent_id', $restaurant->id)
        ->where('deleted', 0);

      $select_photos = RestaurantFoodScan::query('restaurant_food_scans')
        ->where('restaurant_food_scans.deleted', 0)
        ->whereIn('restaurant_food_scans.restaurant_id', $select_sensors)
        ->whereIn('restaurant_food_scans.status', ['checked', 'failed'])
        ->distinct()
        ->selectRaw('DATE(restaurant_food_scans.time_photo) as date_create')
        ->selectRaw('COUNT(restaurant_food_scans.id) as total_photos')
        ->whereMonth('restaurant_food_scans.time_photo', $month)
        ->whereYear('restaurant_food_scans.time_photo', $year)
        ->orderByRaw('DATE(restaurant_food_scans.time_photo)')
        ->groupByRaw('DATE(restaurant_food_scans.time_photo)')
      ;
      $total_photos = $select_photos->get()
        ->toArray();

      $items = [];
      $days = date('t', strtotime($year . '-' . $month . '-01'));
      for ($days; $days > 0; $days--) {
        $m = SysCore::str_format_hour($month);
        $d = SysCore::str_format_hour($days);

        $date = $year . '-' . $m . '-' . $d;
        $date_text = $d . '/' . $m . '/' . $year;

        if ($date > date('Y-m-d')) {
          continue;
        }

        $photo = 0;
        if (count($total_photos)) {
          foreach ($total_photos as $t1) {
            if ($t1['date_create'] == $date) {
              $photo = $t1['total_photos'];
              break;
            }
          }
        }

        $bill = 0;
        if (count($total_bills)) {
          foreach ($total_bills as $t1) {
            if ($t1['date_create'] == $date) {
              $bill = $t1['total_bills'];
              break;
            }
          }
        }

        $items[$date] = [
          'date' => $date,
          'date_text' => $date_text,
          'total_photos' => $photo,
          'total_bills' => $bill,
        ];
      }

      $datas[$restaurant->id] = $items;
    }

    $html = view('tastevn.htmls.kas_checker_month')
      ->with('restaurants', $restaurants)
      ->with('year', $year)
      ->with('month', $month)
      ->with('total_days', date('t', strtotime($year . '-' . $month . '-01')))
      ->with('datas', $datas)
      ->render();

    return response()->json([
      'status' => true,

      'html' => $html,
    ]);
  }

  public function date_check_restaurant(Request $request)
  {
    $values = $request->post();

    //required
    $validator = Validator::make($values, [
      'date' => 'required|string',
      'restaurant' => 'required',
    ]);
    if ($validator->fails()) {
      return response()->json($validator->errors(), 422);
    }

    $temps = array_filter(explode('/', $values['date']));
    $date = $temps[2] . '-' . $temps[1] . '-' . $temps[0];

    $restaurant_parent = RestaurantParent::find((int)$values['restaurant']);
    $kas_restaurant = KasRestaurant::where('restaurant_parent_id', (int)$values['restaurant'])
      ->first();

    $select_sensors = Restaurant::select('id')
      ->where('restaurant_parent_id', $restaurant_parent->id)
      ->where('deleted', 0);

    //stat
    $select = KasBillOrderItem::query('kas_bill_order_items')
      ->select('kas_items.item_code', 'kas_items.item_name', 'kas_items.food_id', 'kas_items.food_name')
      ->selectRaw('COUNT(kas_bill_order_items.id) as total_quantity_kas')
      ->leftJoin('kas_items', 'kas_bill_order_items.kas_item_id', '=', 'kas_items.id')
      ->leftJoin('kas_bill_orders', 'kas_bill_order_items.kas_bill_order_id', '=', 'kas_bill_orders.id')
      ->leftJoin('kas_bills', 'kas_bill_orders.kas_bill_id', '=', 'kas_bills.id')
      ->where('kas_bill_order_items.status', '<>', 'deleted')
      ->where('kas_bills.kas_restaurant_id', $kas_restaurant->id)
      ->where('kas_bills.date_create', $date)
      ->where('kas_bills.status', 'paid')
      ->where('kas_items.food_id', '>', 0)
      ->groupBy('kas_items.item_code', 'kas_items.item_name', 'kas_items.food_id', 'kas_items.food_name')
      ->orderByRaw('total_quantity_kas desc')
      ->orderByRaw('TRIM(LOWER(kas_items.food_name))')
      ->orderBy('kas_items.food_id', 'desc');
    $rows = $select->get()->toArray();

    $temps = $rows;
    $table1 = $rows;
    $table2 = [];
    if (count($rows)) {
      $food_ids = array_column($rows, 'food_id');

      $select = RestaurantFoodScan::query('restaurant_food_scans')
        ->select('restaurant_food_scans.food_id', 'foods.name')
        ->selectRaw('COUNT(restaurant_food_scans.id) as total_quantity_web')
        ->leftJoin('restaurants', 'restaurant_food_scans.restaurant_id', '=', 'restaurants.id')
        ->leftJoin('foods', 'restaurant_food_scans.food_id', '=', 'foods.id')
        ->where('restaurant_food_scans.deleted', 0)
        ->whereIn('restaurant_food_scans.status', ['checked', 'failed'])
        ->whereDate('restaurant_food_scans.time_photo', $date)
        ->whereIn('restaurant_food_scans.restaurant_id', $select_sensors)
        ->where('restaurant_food_scans.food_id', '>', 0)
        ->whereIn('restaurant_food_scans.food_id', $food_ids)
        ->groupBy('restaurant_food_scans.food_id', 'foods.name')
        ->orderByRaw('total_quantity_web desc')
        ->orderByRaw('TRIM(LOWER(foods.name))')
        ->orderBy('restaurant_food_scans.food_id', 'desc');
      $photos = $select->get()->toArray();

      if (count($photos)) {
        $temps = [];

        foreach ($photos as $photo) {
          foreach ($rows as $row) {

            if ($row['food_id'] == $photo['food_id']) {

              $temps[] = [
                'item_code' => $row['item_code'],
                'item_name' => $row['item_name'],
                'food_id' => $row['food_id'],
                'food_name' => $row['food_name'],
                'total_quantity_kas' => $row['total_quantity_kas'],
                'total_quantity_web' => $photo['total_quantity_web'],
              ];
            } else {

              $temps[] = [
                'item_code' => $row['item_code'],
                'item_name' => $row['item_name'],
                'food_id' => $row['food_id'],
                'food_name' => $row['food_name'],
                'total_quantity_kas' => $row['total_quantity_kas'],
                'total_quantity_web' => 0,
              ];
            }
          }
        }
      }
    }

    //photo
    $select2 = RestaurantFoodScan::query()
      ->distinct()
      ->selectRaw('HOUR(time_photo) as hour')
      ->where('deleted', 0)
      ->whereIn('status', ['checked', 'failed'])
      ->whereDate('time_photo', $date)
      ->whereIn('restaurant_id', $select_sensors)
      ->orderBy('hour', 'asc');

    //bill
    $select1 = KasBill::query('kas_bills')
      ->distinct()
      ->selectRaw('HOUR(time_create) as hour')
      ->leftJoin('kas_restaurants', 'kas_restaurants.id', '=', 'kas_bills.kas_restaurant_id')
      ->where('kas_restaurants.restaurant_parent_id', $restaurant_parent->id)
      ->where('kas_bills.date_create', $date)
      ->orderBy('hour', 'asc');

    $html = view('tastevn.htmls.kas_checker_restaurant_date')
      ->with('restaurant', $restaurant_parent)
      ->with('date', $date)
      ->with('stats', $temps)
      ->with('hour1s', $select1->get())
      ->with('hour2s', $select2->get())
      ->render();

    return response()->json([
      'status' => true,

      'restaurant' => $restaurant_parent->get_info(),
      'html' => $html,
    ]);
  }

  public function date_check_restaurant_photo(Request $request)
  {
    $values = $request->post();

    //required
    $validator = Validator::make($values, [
      'date' => 'required|string',
      'restaurant' => 'required',
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

    $select = RestaurantFoodScan::query()
      ->distinct()
      ->select('id')
      ->where('deleted', 0)
      ->whereIn('status', ['checked', 'failed'])
      ->whereDate('time_photo', $date)
      ->whereIn('restaurant_id', $select_sensors)
      ->orderBy('id', 'asc');

    $items = $select->get()->toArray();

    $total_items = count($items);
    $items = array_column($items, 'id');
    $itd = $items[0];
    $items = implode(';', $items);

    return response()->json([
      'status' => true,

      'items' => $items,
      'total_items' => $total_items,

      'itd' => $itd,
    ]);
  }

  public function date_check_restaurant_hour(Request $request)
  {
    $values = $request->post();

    //required
    $validator = Validator::make($values, [
      'date' => 'required|string',
      'hour' => 'required',
      'restaurant' => 'required',
    ]);
    if ($validator->fails()) {
      return response()->json($validator->errors(), 422);
    }

    $restaurant_parent = RestaurantParent::find((int)$values['restaurant']);
    $hour = $values['hour'];
    $date = $values['date'];
    $type = $values['type'];

    if ($type == 'bill') {

      $select = KasBill::query('kas_bills')
        ->distinct()
        ->select('kas_bills.id', 'kas_bills.bill_id', 'kas_bills.note',
          'kas_bills.status', 'kas_bills.time_create', 'kas_bills.time_payment'
        )
        ->leftJoin('kas_restaurants', 'kas_restaurants.id', '=', 'kas_bills.kas_restaurant_id')
        ->where('kas_restaurants.restaurant_parent_id', $restaurant_parent->id)
        ->where('kas_bills.date_create', $date)
//        ->where('kas_bills.status', 'paid')
        ->whereRaw('HOUR(kas_bills.time_create) = ' . (int)$hour)
        ->orderBy('kas_bills.bill_id', 'asc');
      $rows = $select->get();

      $items = [];
      if (count($rows)) {
        foreach ($rows as $row) {

          $items[] = [
            'bill_id' => $row->id,
            'bill_kas_id' => $row->bill_id,
            'bill_status' => $row->status,
            'bill_note' => $row->note,

            'bill_time_create' => $row->time_create,
            'bill_time_payment' => $row->time_payment,

            'orders' => $row->get_orders_info(),
          ];
        }
      }

      $html = view('tastevn.htmls.kas_checker_restaurant_date_bill')
        ->with('items', $items)
        ->render();

    } else {
      //photo
      $select_sensors = Restaurant::select('id')
        ->where('restaurant_parent_id', $restaurant_parent->id)
        ->where('deleted', 0);

      $select = RestaurantFoodScan::query('restaurant_food_scans')
        ->distinct()
        ->select('restaurant_food_scans.id', 'restaurant_food_scans.local_storage',
          'restaurant_food_scans.photo_name', 'restaurant_food_scans.photo_url', 'restaurant_food_scans.created_at',
          'restaurant_food_scans.time_photo', 'restaurants.name as restaurant_name',
        )
        ->leftJoin('restaurants', 'restaurant_food_scans.restaurant_id', '=', 'restaurants.id')
        ->where('restaurant_food_scans.deleted', 0)
        ->whereIn('restaurant_food_scans.status', ['checked', 'failed'])
        ->whereDate('restaurant_food_scans.time_photo', $date)
        ->whereIn('restaurant_food_scans.restaurant_id', $select_sensors)
        ->whereRaw('HOUR(restaurant_food_scans.time_photo) = ' . (int)$hour)
        ->orderBy('restaurant_food_scans.id', 'asc');

      $items = $select->get();

      $html = view('tastevn.htmls.kas_checker_restaurant_date_photo')
        ->with('items', $items)
        ->render();
    }

    return response()->json([
      'status' => true,

      'html' => $html,
    ]);
  }
}
