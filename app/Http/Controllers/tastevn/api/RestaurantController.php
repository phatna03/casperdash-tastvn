<?php

namespace App\Http\Controllers\tastevn\api;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

use Validator;
use App\Api\SysCore;
use App\Excel\ImportData;

use App\Models\RestaurantParent;
use App\Models\Food;
use App\Models\FoodCategory;
use App\Models\Restaurant;
use App\Models\RestaurantFood;

class RestaurantController extends Controller
{
  protected $_api_core = null;

  public function __construct()
  {
    $this->_api_core = new SysCore();

    $this->middleware(function ($request, $next) {
      return $next($request);
    });

    $this->middleware('auth');
  }

  public function index(Request $request)
  {
    $user = Auth::user();
    $invalid_roles = ['user', 'moderator'];
    if (in_array($user->role, $invalid_roles)) {
      return redirect('admin/photos');
    }

    $pageConfigs = [
      'myLayout' => 'horizontal',
      'hasCustomizer' => false,
    ];

    $user->add_log([
      'type' => 'view_listing_restaurant',
    ]);

    return view('tastevn.pages.restaurant_parents', ['pageConfigs' => $pageConfigs]);
  }

  public function store(Request $request)
  {
    $values = $request->post();
    $user = Auth::user();
    //required
    $validator = Validator::make($values, [
      'name' => 'required|string',
    ]);
    if ($validator->fails()) {
      return response()->json($validator->errors(), 422);
    }
    //restore
    $row = RestaurantParent::whereRaw('LOWER(name) LIKE ?', strtolower(trim($values['name'])))
      ->first();
    if ($row) {
      if ($row->deleted) {
        return response()->json([
          'type' => 'can_restored',
          'error' => 'Item deleted'
        ], 422);
      }
      //existed
      return response()->json([
        'error' => 'Name existed'
      ], 422);
    }

    $row = RestaurantParent::create([
      'name' => ucwords(trim($values['name'])),
      'creator_id' => $user->id,
    ]);

    $row->on_create_after();

    $user->add_log([
      'type' => 'add_' . $row->get_type(),
      'restaurant_parent_id' => (int)$row->id,
      'item_id' => (int)$row->id,
      'item_type' => $row->get_type(),
    ]);

    return response()->json([
      'status' => true,
      'item' => $row->name,
    ], 200);
  }

  public function update(Request $request)
  {
    $values = $request->post();
    $user = Auth::user();
    //required
    $validator = Validator::make($values, [
      'item' => 'required',
      'name' => 'required|string',
    ]);
    if ($validator->fails()) {
      return response()->json($validator->errors(), 422);
    }
    //invalid
    $row = RestaurantParent::findOrFail((int)$values['item']);
    if (!$row) {
      return response()->json([
        'error' => 'Invalid item'
      ], 422);
    }
    //restore
    $row1 = RestaurantParent::whereRaw('LOWER(name) LIKE ?', strtolower(trim($values['name'])))
      ->first();
    if ($row1) {
      if ($row1->deleted) {
        return response()->json([
          'type' => 'can_restored',
          'error' => 'Item deleted'
        ], 422);
      }
      //existed
      if ($row1->id != $row->id) {
        return response()->json([
          'error' => 'Name existed'
        ], 422);
      }
    }

    $diffs['before'] = $row->get_log();

    $row->update([
      'name' => ucwords(trim($values['name'])),
    ]);

    $row->on_update_after();

    //re-count
    $this->_api_core->sys_stats_count();

    $row = RestaurantParent::find($row->id);
    $diffs['after'] = $row->get_log();
    if (json_encode($diffs['before']) !== json_encode($diffs['after'])) {
      $user->add_log([
        'type' => 'edit_' . $row->get_type(),
        'restaurant_parent_id' => (int)$row->id,
        'item_id' => (int)$row->id,
        'item_type' => $row->get_type(),
        'params' => json_encode($diffs),
      ]);
    }

    return response()->json([
      'status' => true,
      'item' => $row->name,
    ], 200);
  }

  public function delete(Request $request)
  {
    $values = $request->post();
    $user = Auth::user();
    //required
    $validator = Validator::make($values, [
      'item' => 'required',
    ]);
    if ($validator->fails()) {
      return response()->json($validator->errors(), 422);
    }

    $row = RestaurantParent::findOrFail((int)$values['item']);
    if (!$row) {
      return response()->json([
        'error' => 'Invalid item'
      ], 422);
    }

    $sensors = $row->get_sensors();
    if (count($sensors)) {
      return response()->json([
        'error' => 'Please delete all active related sensors'
      ], 422);
    }

    $row->update([
      'deleted' => $user->id,
    ]);

    $row->on_delete_after();

    $user->add_log([
      'type' => 'delete_' . $row->get_type(),
      'restaurant_parent_id' => (int)$row->id,
      'item_id' => (int)$row->id,
      'item_type' => $row->get_type(),
    ]);

    return response()->json([
      'status' => true,
      'item' => $row->name,
    ], 200);
  }

  public function restore(Request $request)
  {
    $values = $request->post();
    $user = Auth::user();
    //required
    $validator = Validator::make($values, [
      'item' => 'required',
    ]);
    if ($validator->fails()) {
      return response()->json($validator->errors(), 422);
    }

    $row = RestaurantParent::whereRaw('LOWER(name) LIKE ?', strtolower(trim($values['item'])))
      ->first();
    if (!$row) {
      return response()->json([
        'error' => 'Invalid item'
      ], 422);
    }

    $row->update([
      'deleted' => 0,
    ]);

    $row->on_restore_after();

    $user->add_log([
      'type' => 'restore_' . $row->get_type(),
      'restaurant_parent_id' => (int)$row->id,
      'item_id' => (int)$row->id,
      'item_type' => $row->get_type(),
    ]);

    return response()->json([
      'status' => true,
      'item' => $row->name,
    ], 200);
  }

  public function selectize(Request $request)
  {
    $values = $request->post();
    $keyword = isset($values['keyword']) && !empty($values['keyword']) ? $values['keyword'] : NULL;

    $select = RestaurantParent::select('id', 'name')
      ->where('deleted', 0);
    if (!empty($keyword)) {
      $select->where('name', 'LIKE', "%{$keyword}%");
    }

    return response()->json([
      'items' => $select->get()->toArray()
    ]);
  }

  public function info(Request $request)
  {
    $values = $request->post();
    $user = Auth::user();
    //required
    $validator = Validator::make($values, [
      'item' => 'required',
    ]);
    if ($validator->fails()) {
      return response()->json($validator->errors(), 422);
    }
    //invalid
    $row = RestaurantParent::findOrFail((int)$values['item']);
    if (!$row) {
      return response()->json([
        'error' => 'Invalid item'
      ], 422);
    }

    $foods = $row->get_food_datas();

    $html = view('tastevn.htmls.info.item_restaurant_parent')
      ->with('foods', $foods)
      ->render();

    return response()->json([
      'restaurant' => $row,
      'html' => $html
    ], 200);
  }

  public function food_import(Request $request)
  {
    $values = $request->post();
    $user = Auth::user();

    $restaurant_parent_id = isset($values['restaurant_parent_id']) ? (int)$values['restaurant_parent_id'] : 0;
    $restaurant_parent = RestaurantParent::find($restaurant_parent_id);

    $datas = (new ImportData())->toArray($request->file('excel'));
    if (!count($datas) || !count($datas[0]) || !$restaurant_parent) {
      return response()->json([
        'error' => 'Invalid data'
      ], 404);
    }

    $sensors = $restaurant_parent->get_sensors();
    if (!count($sensors)) {
      return response()->json([
        'error' => 'Invalid sensor'
      ], 404);
    }

    $items = [];
    $temps = [];

    DB::beginTransaction();
    try {

      //excel data
      foreach ($datas[0] as $k => $data) {

        $col1 = trim($data[0]);
        $col2 = isset($data[1]) && !empty(trim($data[1])) ? trim($data[1]) : NULL;
        $col3 = isset($data[2]) && !empty(trim($data[2])) ? trim($data[2]) : NULL;

        if (empty($col1)) {
          continue;
        }

        $col1 = str_replace('&', '-', $col1);

        $temps[] = [
          'food' => $col1,
          'category' => $col2,
          'photo' => $col3,
        ];
      }

      //init item
      if (count($temps)) {
        foreach ($temps as $temp) {

          $food = Food::whereRaw('LOWER(name) LIKE ?', strtolower($temp['food']))
            ->first();
          if (!$food) {
            continue;
          }

          $food_category = NULL;
          if (!empty($temp['category'])) {
            $food_category = FoodCategory::whereRaw('LOWER(name) LIKE ?', strtolower($temp['category']))
              ->first();
            if (!$food_category) {
              $food_category = FoodCategory::create([
                'name' => ucwords($temp['category']),
                'creator_id' => $user->id,
              ]);
            }
          }

          $items[] = [
            'food_id' => $food->id,
            'live_group' => $food->live_group,
            'food_category_id' => $food_category ? $food_category->id : 0,
            'photo' => !empty($temp['photo']) && @getimagesize($temp['photo']) ? $temp['photo'] : NULL,
          ];

        }
      }

      //import
      if (count($items)) {
        foreach ($sensors as $sensor) {
          $sensor->import_foods($items);
        }

        //re-count
        $this->_api_core->sys_stats_count();

//      $user->add_log([
//        'type' => 'import_food_to_' . $restaurant_parent->get_type(),
//        'item_id' => (int)$restaurant_parent->id,
//        'item_type' => $restaurant_parent->get_type(),
//      ]);
      }

      DB::commit();

    } catch (\Exception $e) {
      DB::rollback();

      return response()->json([
        'error' => 'Error transaction! Please try again later.', //$e->getMessage()
      ], 422);
    }

    if (count($items)) {
      return response()->json([
        'status' => true,
        'message' => 'import food= ' . count($items),
      ], 200);
    }

    return response()->json([
      'error' => 'Invalid data or dishes existed',
    ], 422);
  }

  public function food_remove(Request $request)
  {
    $values = $request->post();
    $user = Auth::user();

    $restaurant_parent_id = isset($values['restaurant_parent_id']) ? (int)$values['restaurant_parent_id'] : 0;
    $restaurant_parent = RestaurantParent::find($restaurant_parent_id);

    $food_id = isset($values['food_id']) ? (int)$values['food_id'] : 0;
    $food = Food::find($food_id);

    if (!$restaurant_parent || !$food) {
      return response()->json([
        'error' => 'Invalid data'
      ], 404);
    }

    RestaurantFood::where('food_id', $food->id)
      ->whereIn('restaurant_id', function ($q) use ($restaurant_parent) {
        $q->select('id')
          ->from('restaurants')
          ->where('restaurant_parent_id', $restaurant_parent->id);
      })->update([
        'deleted' => $user->id,
      ]);

    return response()->json([
      'status' => true,
    ], 200);
  }

  public function food_group(Request $request)
  {
    $values = $request->post();
    $user = Auth::user();

    $live_group = isset($values['live_group']) && (int)$values['live_group'] && (int)$values['live_group'] < 4
      ? (int)$values['live_group'] : 3;

    $restaurant_parent_id = isset($values['restaurant_parent_id']) ? (int)$values['restaurant_parent_id'] : 0;
    $restaurant_parent = RestaurantParent::find($restaurant_parent_id);

    $food_id = isset($values['food_id']) ? (int)$values['food_id'] : 0;
    $food = Food::find($food_id);

    if (!$restaurant_parent || !$food) {
      return response()->json([
        'error' => 'Invalid data'
      ], 404);
    }

    RestaurantFood::where('food_id', $food->id)
      ->whereIn('restaurant_id', function ($q) use ($restaurant_parent) {
        $q->select('id')
          ->from('restaurants')
          ->where('restaurant_parent_id', $restaurant_parent->id);
      })->update([
        'live_group' => $live_group,
      ]);

    return response()->json([
      'status' => true,
    ], 200);
  }

}
