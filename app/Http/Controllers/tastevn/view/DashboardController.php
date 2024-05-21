<?php

namespace App\Http\Controllers\tastevn\view;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use Validator;
use App\Api\SysCore;

use App\Models\Food;
use App\Models\Restaurant;
use App\Models\RestaurantFood;
use App\Models\RestaurantParent;
use App\Models\RestaurantFoodScan;

class DashboardController extends Controller
{
  public function __construct()
  {
    $this->middleware(function ($request, $next) {
      return $next($request);
    });

    $this->middleware('auth');
  }

  public function index(Request $request)
  {
    $user = Auth::user();
    $invalid_roles = ['user'];
    if (in_array($user->role, $invalid_roles)) {
      return redirect('admin/photos');
    }

    $pageConfigs = [
      'myLayout' => 'horizontal',
      'hasCustomizer' => false,
    ];

    $user->add_log([
      'type' => 'view_dashboard',
    ]);

    return view('tastevn.pages.dashboard', ['pageConfigs' => $pageConfigs]);
  }

  public function sensor(Request $request)
  {
    $user = Auth::user();

    $pageConfigs = [
      'myLayout' => 'horizontal',
      'hasCustomizer' => false,
    ];

    $user->add_log([
      'type' => 'view_dashboard',
    ]);

    return view('tastevn.pages.dashboard_sensor', ['pageConfigs' => $pageConfigs]);
  }

  public function notification(Request $request)
  {
    $values = $request->all();
    $user = Auth::user();

    $page = isset($values['page']) && (int)$values['page'] > 1 ? (int)$values['page'] : 1;

    $notifications = Auth::user()->notifications()
      ->orderBy('id', 'desc')
      ->paginate(10, ['*'], 'page', $page);

    $pageConfigs = [
      'myLayout' => 'horizontal',
      'hasCustomizer' => false,

      'notifications' => $notifications,
      'totalPages' => $notifications->lastPage(),
      'currentPage' => $page,

      'vars' => $values,
    ];

    $user->add_log([
      'type' => 'view_listing_notification',
    ]);

    return view('tastevn.pages.notification', ['pageConfigs' => $pageConfigs]);
  }

  public function notification_read(Request $request)
  {
    $values = $request->post();

    if (isset($values['item'])) {
      Auth::user()
        ->unreadNotifications
        ->when($values['item'], function ($q) use ($values) {
          return $q->where('id', $values['item']);
        })
        ->markAsRead();
    }

    return response()->noContent();
  }

  public function notification_read_all(Request $request)
  {
    Auth::user()
      ->unreadNotifications
      ->markAsRead();

    return response()->noContent();
  }

  public function notification_latest(Request $request)
  {
    $notifications = Auth::user()->notifications()
      ->orderBy('id', 'desc')
      ->paginate(5, ['*'], 'page', 1);

    $html = '';
    if (count($notifications)) {
      $html = view('tastevn.htmls.item_notification_navbar')
        ->with('notifications', $notifications)
        ->render();
    }

    return response()->json([
      'html' => $html,
    ]);
  }

  public function notification_newest()
  {
    $user = Auth::user();
    $api_core = new SysCore();

    $items = [];
    $ids = [];

    $printer = false;
    $text_to_speech = false;
    $text_to_speak = '';
    $valid_types = [
      //force
      'App\Notifications\IngredientMissing'
    ];

    //user_setting
//    if ((int)$user->get_setting('missing_ingredient_receive')) {
//      $valid_types[] = 'App\Notifications\IngredientMissing';
//    }

    //speaker
    if ((int)$user->get_setting('missing_ingredient_alert_speaker')) {
      $text_to_speech = true;
    }

    //printer
    if ((int)$user->get_setting('missing_ingredient_alert_printer')) {
      $printer = true;
    }

    if (!empty($user->time_notification)) {

      $notifications = $user->notifications()
        ->whereIn('type', $valid_types)
        ->where('created_at', '>', $user->time_notification)
        ->orderBy('id', 'asc')
        ->limit(1)
        ->get();

      if (count($notifications)) {
        foreach ($notifications as $notification) {
          $row = RestaurantFoodScan::find($notification->restaurant_food_scan_id);
          if (!$row || empty($row->photo_url)) {
            continue;
          }

          $ingredients = array_filter(explode('&nbsp', $row->missing_texts));
          if (!count($ingredients)) {
            continue;
          }

          $items[] = [
            'itd' => $row->id,
            'photo_url' => $row->photo_url,
            'restaurant_name' => $row->get_restaurant()->name,
            'food_name' => $row->get_food()->name,
            'food_confidence' => $row->confidence,
            'ingredients' => $ingredients,
          ];

          $ids[] = $row->id;

          $user->update([
            'time_notification' => $notification->created_at->format('Y-m-d H:i:s')
          ]);

          if ($text_to_speech) {

            $text_ingredients_missing = '';
            foreach ($row->get_ingredients_missing() as $ing) {
              $text_ingredients_missing .= $ing['ingredient_quantity'] . ' ' . $ing['name'] . ', ';
            }

            $text_to_speak = '[alert]'
              . $row->get_restaurant()->name . ' occurred at '
              . date('H:i')
              . ", Ingredients Missing, "
              . $text_ingredients_missing;

            $api_core->s3_polly([
              'text_to_speak' => $text_to_speak,
            ]);
          }
        }

      } else {

        $user->update([
          'time_notification' => date('Y-m-d H:i:s')
        ]);
      }

    } else {

      $user->update([
        'time_notification' => date('Y-m-d H:i:s')
      ]);
    }

    return response()->json([
      'items' => $items,
      'ids' => $ids,
      'role' => $user->role,
      'speaker' => $text_to_speech && !empty($text_to_speak),
      'speaker_text' => $text_to_speak,
      'printer' => $printer,
    ]);
  }

  public function notification_dashboard(Request $request)
  {
    $values = $request->post();

    $restaurant_id = isset($values['restaurant_id']) ? (int)$values['restaurant_id'] : 0;
    $restaurant = Restaurant::find($restaurant_id);
    if (!$restaurant) {
      return response()->json([
        'status' => false,
        'error' => 'Invalid restaurant sensor'
      ]);
    }

    $live_sensors = [5, 6];
    if (!in_array($restaurant->id, $live_sensors)) {
      return response()->json([
        'status' => false,
        'error' => 'Invalid restaurant sensor live run'
      ]);
    }

    //custom table notifications - add column - restaurant_id + food_id + object_type + object_id
    $live_group_ids = Food::select('id')
      ->where('deleted', 0)
      ->where('live_group', 1);

    $user = Auth::user();
    $api_core = new SysCore();

    $items = [];
    $ids = [];

    $printer = false;
    $text_to_speech = false;
    $text_to_speak = '';
    $valid_types = [
      //force
      'App\Notifications\IngredientMissing'
    ];

    //user_setting
//    if ((int)$user->get_setting('missing_ingredient_receive')) {
//      $valid_types[] = 'App\Notifications\IngredientMissing';
//    }

    //speaker
    if ((int)$user->get_setting('missing_ingredient_alert_speaker')) {
      $text_to_speech = true;
    }

    //printer
    if ((int)$user->get_setting('missing_ingredient_alert_printer')) {
      $printer = true;
    }

    if (!empty($user->time_notification)) {

      $select = $user->notifications()
        ->whereIn('type', $valid_types)
        ->where('restaurant_id', $restaurant->id)
        ->whereIn('food_id', $live_group_ids)
        ->where('created_at', '>', $user->time_notification)
        ->orderBy('id', 'desc')
        ->limit(1);

      $notifications = $select->get();
      if (count($notifications)) {
        foreach ($notifications as $notification) {
          $row = RestaurantFoodScan::find($notification->restaurant_food_scan_id);
          if (!$row || empty($row->photo_url)) {
            continue;
          }

          $ingredients = array_filter(explode('&nbsp', $row->missing_texts));
          if (!count($ingredients)) {
            continue;
          }

          if ($row->get_food()->live_group > 1) {
            continue;
          }

          $items[] = [
            'itd' => $row->id,
            'photo_url' => $row->photo_url,
            'restaurant_name' => $row->get_restaurant()->name,
            'food_name' => $row->get_food()->name,
            'food_confidence' => $row->confidence,
            'ingredients' => $ingredients,
          ];

          $ids[] = $row->id;

          $user->update([
            'time_notification' => date('Y-m-d H:i:s')
          ]);

          if ($text_to_speech) {

            $text_ingredients_missing = '';
            foreach ($row->get_ingredients_missing() as $ing) {
              $text_ingredients_missing .= $ing['ingredient_quantity'] . ' ' . $ing['name'] . ', ';
            }

            $text_to_speak = '[alert]'
              . $row->get_restaurant()->name . ' occurred at '
              . date('H:i')
              . ", Ingredients Missing, "
              . $text_ingredients_missing;

            $api_core->s3_polly([
              'text_to_speak' => $text_to_speak,
            ]);
          }
        }

      }
      else {

        $user->update([
          'time_notification' => date('Y-m-d H:i:s')
        ]);
      }

    }
    else {

      $user->update([
        'time_notification' => date('Y-m-d H:i:s')
      ]);
    }

    return response()->json([
      'status' => true,
      'items' => $items,
      'ids' => $ids,
//      'role' => $user->role,
      'speaker' => $text_to_speech && !empty($text_to_speak),
      'speaker_text' => $text_to_speak,
      'printer' => $printer,
    ]);
  }

  public function sensor_kitchen(Request $request)
  {
    $values = $request->post();

    $user = Auth::user();
    $api_core = new SysCore();

    $restaurant_id = isset($values['restaurant_id']) ? (int)$values['restaurant_id'] : 0;
    $restaurant = Restaurant::find($restaurant_id);
    if (!$restaurant) {
      return response()->json([
        'status' => false,
        'error' => 'Invalid restaurant sensor'
      ]);
    }

    $itd = isset($values['itd']) ? (int)$values['itd'] : 0;
    $row = RestaurantFoodScan::find($itd);

    if (!$row) {
      $row = RestaurantFoodScan::where('deleted', 0)
        ->where('restaurant_id', $restaurant->id)
        ->whereIn('status', ['checked', 'failed', 'edited'])
        ->orderBy('id', 'desc')
        ->limit(1)
        ->first();
    }

    $food = $row->get_food();
    $ingredients = [];

    if (!empty($row->missing_texts)) {
      $datas = array_filter(explode('&nbsp', $row->missing_texts));
      if (count($datas)) {
        foreach ($datas as $dt) {
          if (!empty($dt) && !empty(trim($dt))) {
            $ingredients[] = trim($dt);
          }
        }
      }
    }

    $item = [
      'itd' => $row->id,
      'photo_url' => $row->photo_url,
      'food_name' => $food ? $food->name : '',
      'food_photo' => $food ? $food->get_photo_standard($restaurant) : '',
      'ingredients_missing' => $ingredients,

      'status' => $row->status,

      'view_food_id' => $food ? $food->id : '',
      'view_restaurant_parent_id' => $restaurant ? $restaurant->restaurant_parent_id : '',
    ];

    return response()->json([
      'status' => true,
      'item' => $item,
    ]);
  }

  public function restaurant_food_get(Request $request)
  {
    $values = $request->post();

    $validator = Validator::make($values, [
      'item' => 'required',
    ]);
    if ($validator->fails()) {
      return response()->json($validator->errors(), 422);
    }

    $restaurant_parent_id = isset($values['item']) ? (int)$values['item'] : 0;

    $restaurant_ids = Restaurant::select('id')
      ->where('deleted', 0)
      ->where('restaurant_parent_id', $restaurant_parent_id);

    $rows = RestaurantFood::query("restaurant_foods")
      ->distinct()
      ->select('foods.id', 'foods.name')
      ->leftJoin('foods', 'foods.id', '=', 'restaurant_foods.food_id')
      ->whereIn('restaurant_foods.restaurant_id', $restaurant_ids)
      ->where('foods.deleted', 0)
      ->where('restaurant_foods.deleted', 0)
      ->orderByRaw('TRIM(LOWER(foods.name))')
      ->get();

    $items = [];
    $count = 0;

    if (count($rows)) {
      foreach ($rows as $row) {

        $count++;

        $items[] = [
          'id' => $row->id,
          'name' => $count . '. ' . $row->name,
        ];
      }
    }

    return response()->json([
      'status' => true,
      'items' => $items,
    ]);
  }

  public function food_get_info(Request $request)
  {
    $values = $request->post();
    $user = Auth::user();

    $validator = Validator::make($values, [
      'item' => 'required',
    ]);
    if ($validator->fails()) {
      return response()->json($validator->errors(), 422);
    }

    $restaurant_parent_id = isset($values['restaurant_parent_id']) ? (int)$values['restaurant_parent_id'] : 0;
    $restaurant_parent = RestaurantParent::find($restaurant_parent_id);

    //invalid
    $row = Food::findOrFail((int)$values['item']);
    if (!$row || !$restaurant_parent) {
      return response()->json([
        'error' => 'Invalid item'
      ], 422);
    }

    $restaurant_ids = Restaurant::where('deleted', 0)
      ->select('id')
      ->where('restaurant_parent_id', $restaurant_parent_id);

    $restaurant_food = RestaurantFood::where('deleted', 0)
      ->whereIn('restaurant_id', $restaurant_ids)
      ->where('food_id', $row->id)
      ->where('photo', '<>', NULL)
      ->orderBy('updated_at', 'desc')
      ->limit(1)
      ->first();
    $food_photo = $restaurant_food ? $restaurant_food->photo : url('custom/img/no_photo.png');

    //info
    $html_info = view('tastevn.htmls.item_food_dashboard')
      ->with('recipes', $row->get_recipes([
        'restaurant_parent_id' => $restaurant_parent_id,
      ]))
      ->render();

    return response()->json([
      'food_name' => '[' . $restaurant_parent->name . '] ' . $row->name,
      'food_photo' => $food_photo,

      'html_info' => $html_info,
    ]);
  }


}
