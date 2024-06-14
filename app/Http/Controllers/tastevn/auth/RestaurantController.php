<?php

namespace App\Http\Controllers\tastevn\auth;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
//lib
use Validator;
use App\Api\SysApp;
use App\Excel\ImportData;
//model
use App\Models\RestaurantParent;
use App\Models\Food;
use App\Models\FoodIngredient;
use App\Models\FoodCategory;
use App\Models\Restaurant;
use App\Models\RestaurantFood;

class RestaurantController extends Controller
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
    $invalid_roles = ['user'];
    if (in_array($this->_viewer->role, $invalid_roles)) {
      return redirect('admin/photos');
    }

    $pageConfigs = [
      'myLayout' => 'horizontal',
      'hasCustomizer' => false,
    ];

    $this->_viewer->add_log([
      'type' => 'view_listing_restaurant',
    ]);

    return view('tastevn.pages.restaurant_parents', ['pageConfigs' => $pageConfigs]);
  }

  public function store(Request $request)
  {
    $values = $request->post();
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
      'creator_id' => $this->_viewer->id,
    ]);

    $row->on_create_after();

    $this->_viewer->add_log([
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
    //required
    $validator = Validator::make($values, [
      'item' => 'required',
      'name' => 'required|string',
    ]);
    if ($validator->fails()) {
      return response()->json($validator->errors(), 422);
    }
    //invalid
    $row = RestaurantParent::find((int)$values['item']);
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
    $this->_sys_app->sys_stats_count();

    $row = RestaurantParent::find($row->id);
    $diffs['after'] = $row->get_log();
    if (json_encode($diffs['before']) !== json_encode($diffs['after'])) {
      $this->_viewer->add_log([
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
    //required
    $validator = Validator::make($values, [
      'item' => 'required',
    ]);
    if ($validator->fails()) {
      return response()->json($validator->errors(), 422);
    }

    $row = RestaurantParent::find((int)$values['item']);
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
      'deleted' => $this->_viewer->id,
    ]);

    $row->on_delete_after();

    $this->_viewer->add_log([
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

    $this->_viewer->add_log([
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

    $select = RestaurantParent::select('id', 'name');

    //dev
    if ($this->_viewer->is_dev()) {

    } else {
      $select->where('deleted', 0);
    }

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
    //required
    $validator = Validator::make($values, [
      'item' => 'required',
    ]);
    if ($validator->fails()) {
      return response()->json($validator->errors(), 422);
    }
    //invalid
    $row = RestaurantParent::find((int)$values['item']);
    if (!$row) {
      return response()->json([
        'error' => 'Invalid item'
      ], 422);
    }

    $foods = $row->get_foods();

    $html = view('tastevn.htmls.item_restaurant_parent')
      ->with('restaurant_parent', $row)
      ->with('foods', $foods)
      ->render();

    return response()->json([
      'restaurant' => $row,
      'html' => $html
    ], 200);
  }

  public function food_get(Request $request)
  {
    $values = $request->post();
    $keyword = isset($values['keyword']) && !empty($values['keyword']) ? $values['keyword'] : NULL;
    $restaurant_parent_id = isset($values['restaurant_parent_id']) ? (int)$values['restaurant_parent_id'] : 0;
    $restaurant_parent = RestaurantParent::find($restaurant_parent_id);

    $items = [];

    if ($restaurant_parent) {

      $sensor = $restaurant_parent->get_sensors([
        'one_sensor' => 1,
      ]);

      if ($sensor) {

        $select = RestaurantFood::query('restaurant_foods')
          ->where('restaurant_foods.restaurant_id', $sensor->id)
          ->distinct()
          ->select('foods.id', 'foods.name',)
          ->where('restaurant_foods.deleted', 0)
          ->where('foods.deleted', 0)
          ->leftJoin('foods', 'foods.id', '=', 'restaurant_foods.food_id')
          ->leftJoin('food_categories', 'food_categories.id', '=', 'restaurant_foods.food_category_id')
          ->orderByRaw('TRIM(LOWER(foods.name))');

        if (!empty($keyword)) {
          $select->where('foods.name', 'LIKE', "%{$keyword}%");
        }

        $items = $select->get()->toArray();
      }
    }

    return response()->json([
      'items' => $items,
    ]);
  }

  public function food_import(Request $request)
  {
    $values = $request->post();

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
                'creator_id' => $this->_viewer->id,
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
        $this->_sys_app->sys_stats_count();

//      $this->_viewer->add_log([
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
        'deleted' => $this->_viewer->id,
      ]);

    //re-count
    $this->_sys_app->sys_stats_count();

    return response()->json([
      'status' => true,
    ], 200);
  }

  public function food_update(Request $request)
  {
    $values = $request->post();

    $restaurant_parent_id = isset($values['restaurant_parent_id']) ? (int)$values['restaurant_parent_id'] : 0;
    $restaurant_parent = RestaurantParent::find($restaurant_parent_id);

    $food_id = isset($values['food_id']) ? (int)$values['food_id'] : 0;
    $food = Food::find($food_id);

    if (!$restaurant_parent || !$food) {
      return response()->json([
        'error' => 'Invalid data'
      ], 404);
    }

    $type = isset($values['type']) ? $values['type'] : 'live_group';
    $model_name = isset($values['model_name']) ? $values['model_name'] : NULL;
    $model_version = isset($values['model_version']) ? $values['model_version'] : NULL;
    $live_group = isset($values['live_group']) && (int)$values['live_group'] && (int)$values['live_group'] < 4
      ? (int)$values['live_group'] : 3;

    switch ($type) {
      case 'live_group':
        RestaurantFood::where('food_id', $food->id)
          ->whereIn('restaurant_id', function ($q) use ($restaurant_parent) {
            $q->select('id')
              ->from('restaurants')
              ->where('restaurant_parent_id', $restaurant_parent->id);
          })->update([
            'live_group' => $live_group,
          ]);
        break;

      case 'model_name':
        RestaurantFood::where('food_id', $food->id)
          ->whereIn('restaurant_id', function ($q) use ($restaurant_parent) {
            $q->select('id')
              ->from('restaurants')
              ->where('restaurant_parent_id', $restaurant_parent->id);
          })->update([
            'model_name' => $model_name,
          ]);
        break;

      case 'model_version':
        RestaurantFood::where('food_id', $food->id)
          ->whereIn('restaurant_id', function ($q) use ($restaurant_parent) {
            $q->select('id')
              ->from('restaurants')
              ->where('restaurant_parent_id', $restaurant_parent->id);
          })->update([
            'model_version' => $model_version,
          ]);
        break;
    }

    return response()->json([
      'status' => true,
    ], 200);
  }

  public function food_core(Request $request)
  {
    $values = $request->post();

    //required
    $validator = Validator::make($values, [
      'item' => 'required',
    ]);
    if ($validator->fails()) {
      return response()->json($validator->errors(), 422);
    }

    $item = FoodIngredient::find((int)$values['item']);
    if (!$item) {
      return response()->json([
        'error' => 'Invalid data'
      ], 404);
    }

    $item->update([
      'ingredient_type' => $item->ingredient_type == 'core' ? 'additive' : 'core',
    ]);

    return response()->json([
      'status' => true,
    ], 200);
  }

  public function food_confidence(Request $request)
  {
    $values = $request->post();

    //required
    $validator = Validator::make($values, [
      'item' => 'required',
      'confidence' => 'required',
    ]);
    if ($validator->fails()) {
      return response()->json($validator->errors(), 422);
    }

    $item = FoodIngredient::find((int)$values['item']);
    if (!$item) {
      return response()->json([
        'error' => 'Invalid data'
      ], 404);
    }

    $item->update([
      'confidence' => (int)$values['confidence'],
    ]);

    return response()->json([
      'status' => true,
    ], 200);
  }

  public function food_photo(Request $request)
  {
    $values = $request->post();

    //required
    $validator = Validator::make($values, [
      'food_id' => 'required',
      'restaurant_parent_id' => 'required',
    ]);
    if ($validator->fails()) {
      return response()->json($validator->errors(), 422);
    }

    $food = Food::find((int)$values['food_id']);
    $restaurant_parent = RestaurantParent::find((int)$values['restaurant_parent_id']);
    if (!$food || !$restaurant_parent) {
      return response()->json([
        'error' => 'Invalid data'
      ], 404);
    }

    $file_photo = $request->file('photo');
    if (!empty($file_photo)) {
      foreach ($file_photo as $file) {
        $file_path = '/photos/foods/';
        $full_path = public_path($file_path);
        //os
        if (strtoupper(substr(PHP_OS, 0, 3)) == 'WIN') {
          $full_path = str_replace('/', '\\', $full_path);
        }
        if (!file_exists($full_path)) {
          mkdir($full_path, 0777, true);
        }

        $file_name = 'food_' . $restaurant_parent->id . '_' . $food->id . '.' . $file->getClientOriginalExtension();
        $file->move(public_path($file_path), $file_name);

        $sensors = $restaurant_parent->get_sensors();
        if (count($sensors)) {
          foreach ($sensors as $sensor) {
            $row = RestaurantFood::where('restaurant_id', $sensor->id)
              ->where('food_id', $food->id)
              ->first();
            if (!$row) {
              $row = RestaurantFood::create([
                'restaurant_id' => $sensor->id,
                'food_id' => $food->id,
                'creator_id' => $this->_viewer->id,
              ]);
            }
            $row->update([
              'photo' => $file_name,
              'local_storage' => 1,
              'deleted' => 0,
            ]);
          }
        }
      }
    }

    return response()->json([
      'status' => true,
    ], 200);
  }
}
