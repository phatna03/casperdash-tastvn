<?php

namespace App\Http\Controllers\tastevn\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

use Validator;
use App\Models\User;
use App\Models\Restaurant;
use App\Models\RestaurantAccess;
use App\Models\RestaurantFoodScan;
use App\Models\Food;
use App\Models\FoodIngredient;
use App\Models\Ingredient;

use App\Api\SysCore;
use App\Jobs\PhotoUpload;

class PhotoController extends Controller
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

    $pageConfigs = [
      'myLayout' => 'horizontal',
      'hasCustomizer' => false,

    ];

    $user->add_log([
      'type' => 'view_listing_photo',
    ]);

    return view('tastevn.pages.photos', ['pageConfigs' => $pageConfigs]);
  }

  public function get(Request $request)
  {
    $values = $request->all();
    $api_core = new SysCore();
//    echo '<pre>';var_dump($values);die;

    $existed = isset($values['existed']) ? (array)$values['existed'] : [];
    $restaurants = isset($values['restaurants']) ? (array)$values['restaurants'] : [];
    $time_upload = isset($values['time_upload']) && !empty($values['time_upload']) ? $values['time_upload'] : NULL;

    $select = RestaurantFoodScan::query('restaurant_food_scans')
      ->select('restaurant_food_scans.id', 'restaurant_food_scans.photo_url', 'restaurant_food_scans.time_photo', 'restaurants.name as restaurant_name')
      ->leftJoin('restaurants', 'restaurant_food_scans.restaurant_id', '=', 'restaurants.id')
      ->where('restaurant_food_scans.deleted', 0)
      ->orderBy('restaurant_food_scans.time_photo', 'desc')
      ->orderBy('restaurant_food_scans.id', 'desc')
      ->limit(24)
    ;

    if (count($existed)) {
      $select->whereNotIn("restaurant_food_scans.id", $existed);
    }
    if (count($restaurants)) {
      $select->whereIn("restaurant_food_scans.restaurant_id", $restaurants);
    }
    if (!empty($time_upload)) {
      $times = $api_core->parse_date_range($time_upload);
      if (!empty($times['time_from'])) {
        $select->where('restaurant_food_scans.time_photo', '>=', $times['time_from']);
      }
      if (!empty($times['time_to'])) {
        $select->where('restaurant_food_scans.time_photo', '<=', $times['time_to']);
      }
    }

    $html = view('tastevn.htmls.item_photo')
      ->with('items', $select->get())
      ->render();

    return response()->json([
      'html' => $html,
    ]);
  }

  public function view(Request $request)
  {
    $values = $request->post();
    $user = Auth::user();

    $row = RestaurantFoodScan::find((int)$values['item']);
    if ($row) {
      $user->add_log([
        'type' => 'view_item_photo',
        'restaurant_id' => (int)$row->restaurant_id,
        'item_id' => (int)$row->id,
        'item_type' => $row->get_type(),
      ]);
    }

    return response()->json([

    ]);
  }
}
