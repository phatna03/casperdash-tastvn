<?php

namespace App\Http\Controllers\tastevn\auth;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
//lib
use App\Api\SysApp;
use App\Models\RestaurantFoodScan;

class PhotoController extends Controller
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
    $pageConfigs = [
      'myLayout' => 'horizontal',
      'hasCustomizer' => false,

    ];

    $this->_viewer->add_log([
      'type' => 'view_listing_photo',
    ]);

    return view('tastevn.pages.photos', ['pageConfigs' => $pageConfigs]);
  }

  public function get(Request $request)
  {
    $values = $request->all();
//    echo '<pre>';var_dump($values);die;
    $existed = isset($values['existed']) ? (array)$values['existed'] : [];
    $restaurants = isset($values['restaurants']) ? (array)$values['restaurants'] : [];
    $time_upload = isset($values['time_upload']) && !empty($values['time_upload']) ? $values['time_upload'] : NULL;

    $select = RestaurantFoodScan::query('restaurant_food_scans')
      ->select('restaurant_food_scans.id',
        'restaurant_food_scans.photo_url', 'restaurant_food_scans.photo_name', 'restaurant_food_scans.local_storage',
        'restaurant_food_scans.time_photo', 'restaurants.name as restaurant_name')
      ->leftJoin('restaurants', 'restaurant_food_scans.restaurant_id', '=', 'restaurants.id')
      ->orderBy('restaurant_food_scans.time_photo', 'desc')
      ->orderBy('restaurant_food_scans.id', 'desc')
      ->limit(24)
    ;

    //dev
    if ($this->_viewer->is_dev()) {

    } else {
      $select->where('restaurants.deleted', 0)
        ->where('restaurant_food_scans.deleted', 0)
        ->whereIn('restaurant_food_scans.status', [
          'checked', 'edited', 'failed',
        ])
      ;
    }

    if (count($existed)) {
      $select->whereNotIn("restaurant_food_scans.id", $existed);
    }
    if (count($restaurants)) {
      $select->whereIn("restaurant_food_scans.restaurant_id", $restaurants);
    }
    if (!empty($time_upload)) {
      $times = $this->_sys_app->parse_date_range($time_upload);
      if (!empty($times['time_from'])) {
        $select->where('restaurant_food_scans.time_photo', '>=', $times['time_from']);
      }
      if (!empty($times['time_to'])) {
        $select->where('restaurant_food_scans.time_photo', '<=', $times['time_to']);
      }
    }

    $aaa = $this->_sys_app->parse_to_query($select);

    $html = view('tastevn.htmls.item_photo')
      ->with('items', $select->get())
      ->render();

    return response()->json([
      'html' => $html,
      'query' => $aaa,
    ]);
  }

  public function view(Request $request)
  {
    $values = $request->post();

    $row = RestaurantFoodScan::find((int)$values['item']);
    if ($row) {
      $this->_viewer->add_log([
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
