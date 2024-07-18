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
use App\Models\FoodRecipe;
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
//      return redirect('admin/photos');
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
      'model_name' => isset($values['model_name']) ? trim($values['model_name']) : '',
      'model_version' => isset($values['model_version']) ? trim($values['model_version']) : '',
      'model_scan' => isset($values['model_scan']) && (int)$values['model_scan'] ? 1 : 0,
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
      'model_name' => isset($values['model_name']) ? trim($values['model_name']) : '',
      'model_version' => isset($values['model_version']) ? trim($values['model_version']) : '',
      'model_scan' => isset($values['model_scan']) && (int)$values['model_scan'] ? 1 : 0,
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

    $foods_group_1 = $row->get_foods([
      'live_group' => 1,
    ]);

    $foods_group_2 = $row->get_foods([
      'live_group' => 2,
    ]);

    $foods_group_3 = $row->get_foods([
      'live_group' => 3,
    ]);

    $html = view('tastevn.htmls.item_restaurant_parent')
      ->with('restaurant_parent', $row)
      ->with('foods', count($foods) ? $foods->toArray() : [])
      ->with('foods_group_1', $foods_group_1)
      ->with('foods_group_2', $foods_group_2)
      ->with('foods_group_3', $foods_group_3)
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
      $items = $restaurant_parent->get_foods([
        'keyword' => $keyword,
        'select_data' => 'food_only',
      ]);
    }

    return response()->json([
      'items' => count($items) ? $items->toArray() : [],
    ]);
  }

  public function food_add(Request $request)
  {
    $values = $request->post();

    $restaurant_parent_id = isset($values['restaurant_parent_id']) ? (int)$values['restaurant_parent_id'] : 0;
    $restaurant_parent = RestaurantParent::find($restaurant_parent_id);
    if (!$restaurant_parent) {
      return response()->json([
        'error' => 'Invalid data'
      ], 404);
    }

    $food_name = isset($values['name']) && !empty($values['name']) ? $values['name'] : NULL;
    $food_category_name = isset($values['category_name']) && !empty($values['category_name']) ? $values['category_name'] : NULL;
    $row = NULL;

    $food = Food::where('deleted', 0)
      ->whereRaw('LOWER(name) LIKE ?', strtolower(trim($food_name)))
      ->first();
    if ($food) {
      $row = RestaurantFood::where('restaurant_parent_id', $restaurant_parent->id)
        ->where('food_id', $food->id)
        ->first();
      if ($row) {
        if (!$row->deleted) {
          return response()->json([
            'error' => 'Dish existed'
          ], 404);
        }

        $row->update([
          'deleted' => 0,
        ]);
      }
    }

    if (!$food) {
      $food = Food::create([
        'name' => ucwords(strtolower(trim($food_name))),
        'creator_id' => $this->_viewer->id,
      ]);

      $this->_viewer->add_log([
        'type' => 'add_' . $food->get_type(),
        'item_id' => (int)$food->id,
        'item_type' => $food->get_type(),
      ]);
    }

    if (!$row) {
      $row = RestaurantFood::create([
        'restaurant_parent_id' => $restaurant_parent->id,
        'food_id' => $food->id,
      ]);
    }

    //food_category
    $food_category = NULL;
    if (!empty($food_category_name)) {
      $food_category = FoodCategory::whereRaw('LOWER(name) LIKE ?', strtolower(trim($food_category_name)))
        ->first();
      if (!$food_category) {
        $food_category = FoodCategory::create([
          'name' => ucwords(strtolower(trim($food_category_name)))
        ]);

        $this->_viewer->add_log([
          'type' => 'add_' . $food_category->get_type(),
          'item_id' => (int)$food_category->id,
          'item_type' => $food_category->get_type(),
        ]);
      } else {
        if ($food_category->deleted) {
          $food_category->update([
            'deleted' => 0,
          ]);
        }
      }
    }

    $row->update([
      'food_category_id' => $food_category ? $food_category->id : 0,
    ]);

    //html
    $html = view('tastevn.htmls.item_restaurant_parent_food')
      ->with('restaurant_parent', $restaurant_parent)
      ->with('item', [
        'food_id' => $food->id,
        'food_category_id' => $food_category ? $food_category->id : 0,
        'food_category_name' => $food_category ? $food_category->name : '',
        'food_live_group' => 3,
        'food_model_name' => '',
        'food_model_version' => '',
        'food_confidence' => 70,
      ])
      ->render();

    //re-count
    $this->_sys_app->sys_stats_count();

    //table stats + total foods
    $foods = $restaurant_parent->get_foods();

    $foods_group_1 = $restaurant_parent->get_foods([
      'live_group' => 1,
    ]);

    $foods_group_2 = $restaurant_parent->get_foods([
      'live_group' => 2,
    ]);

    $foods_group_3 = $restaurant_parent->get_foods([
      'live_group' => 3,
    ]);

    return response()->json([
      'status' => true,

      'html' => $html,

      'count_foods' => count($foods),
      'count_foods_1' => count($foods_group_1),
      'count_foods_2' => count($foods_group_2),
      'count_foods_3' => count($foods_group_3),

    ], 200);
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
      ->where('restaurant_parent_id', $restaurant_parent->id)
      ->update([
        'deleted' => $this->_viewer->id,
      ]);

    //re-count
    $this->_sys_app->sys_stats_count();

    //table stats + total foods
    $foods = $restaurant_parent->get_foods();

    $foods_group_1 = $restaurant_parent->get_foods([
      'live_group' => 1,
    ]);

    $foods_group_2 = $restaurant_parent->get_foods([
      'live_group' => 2,
    ]);

    $foods_group_3 = $restaurant_parent->get_foods([
      'live_group' => 3,
    ]);

    return response()->json([
      'status' => true,

      'count_foods' => count($foods),
      'count_foods_1' => count($foods_group_1),
      'count_foods_2' => count($foods_group_2),
      'count_foods_3' => count($foods_group_3),
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
    $food_category_name = isset($values['category_name']) ? $values['category_name'] : NULL;
    $confidence = isset($values['confidence']) && (int)$values['confidence'] && (int)$values['confidence'] > 30
      ? (int)$values['confidence'] : 30;
    $model_name = isset($values['model_name']) ? $values['model_name'] : NULL;
    $model_version = isset($values['model_version']) ? $values['model_version'] : NULL;
    $live_group = isset($values['live_group']) && (int)$values['live_group'] && (int)$values['live_group'] < 4
      ? (int)$values['live_group'] : 3;

    switch ($type) {
      case 'live_group':
        RestaurantFood::where('food_id', $food->id)
          ->where('restaurant_parent_id', $restaurant_parent->id)
          ->update([
            'live_group' => $live_group,
          ]);
        break;

      case 'model_name':
        RestaurantFood::where('food_id', $food->id)
          ->where('restaurant_parent_id', $restaurant_parent->id)
          ->update([
            'model_name' => $model_name,
          ]);
        break;

      case 'model_version':
        RestaurantFood::where('food_id', $food->id)
          ->where('restaurant_parent_id', $restaurant_parent->id)
          ->update([
            'model_version' => $model_version,
          ]);
        break;

      case 'category_name':
        //food_category
        $food_category = NULL;
        if (!empty($food_category_name)) {
          $food_category = FoodCategory::whereRaw('LOWER(name) LIKE ?', strtolower(trim($food_category_name)))
            ->first();
          if (!$food_category) {
            $food_category = FoodCategory::create([
              'name' => ucwords(strtolower(trim($food_category_name)))
            ]);

            $this->_viewer->add_log([
              'type' => 'add_' . $food_category->get_type(),
              'item_id' => (int)$food_category->id,
              'item_type' => $food_category->get_type(),
            ]);
          } else {
            if ($food_category->deleted) {
              $food_category->update([
                'deleted' => 0,
              ]);
            }
          }
        }

        RestaurantFood::where('food_id', $food->id)
          ->where('restaurant_parent_id', $restaurant_parent->id)
          ->update([
            'food_category_id' => $food_category ? $food_category->id : 0,
          ]);

        break;

      case 'confidence':
        RestaurantFood::where('food_id', $food->id)
          ->where('restaurant_parent_id', $restaurant_parent->id)
          ->update([
            'confidence' => $confidence,
          ]);
        break;
    }

    //table stats + total foods
    $foods = $restaurant_parent->get_foods();

    $foods_group_1 = $restaurant_parent->get_foods([
      'live_group' => 1,
    ]);

    $foods_group_2 = $restaurant_parent->get_foods([
      'live_group' => 2,
    ]);

    $foods_group_3 = $restaurant_parent->get_foods([
      'live_group' => 3,
    ]);

    return response()->json([
      'status' => true,

      'type' => $type,

      'count_foods' => count($foods),
      'count_foods_1' => count($foods_group_1),
      'count_foods_2' => count($foods_group_2),
      'count_foods_3' => count($foods_group_3),

      'datas' => $this->restaurant_food_datas($restaurant_parent, $food)
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

        $row = RestaurantFood::where('restaurant_parent_id', $restaurant_parent->id)
          ->where('food_id', $food->id)
          ->first();
        if (!$row) {
          $row = RestaurantFood::create([
            'restaurant_parent_id' => $restaurant_parent->id,
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

    return response()->json([
      'status' => true,
    ], 200);
  }

  public function food_ingredient_get(Request $request)
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

    $html = '';

    $type = isset($values['type']) ? $values['type'] : 'recipe';
    switch ($type) {
      case 'recipe':

        $html = view('tastevn.htmls.item_ingredient_recipe_input')
          ->with('ingredients', $food->get_recipes([
            'restaurant_parent_id' => $restaurant_parent->id
          ]))
          ->render();

        break;

      case 'robot':

        $html = view('tastevn.htmls.item_ingredient_input')
          ->with('ingredients', $food->get_ingredients([
            'restaurant_parent_id' => $restaurant_parent->id
          ]))
          ->render();

        break;
    }

    return response()->json([
      'status' => true,
      'html' => $html,
    ], 200);
  }

  public function food_ingredient_update(Request $request)
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

    //ingredients
    $ingredients = isset($values['ingredients']) && !empty($values['ingredients']) ? (array)$values['ingredients'] : [];
    if (!count($ingredients)) {
      return response()->json([
        'error' => 'Ingredients required'
      ], 422);
    }

    $html = '';

    $type = isset($values['type']) ? $values['type'] : 'recipe';
    switch ($type) {
      case 'recipe':

        $food->update_ingredients_recipe([
          'ingredients' => $ingredients,
          'restaurant_parent_id' => $restaurant_parent_id,
        ]);

        $html = view('tastevn.htmls.item_restaurant_parent_food_recipe')
          ->with('items', $food->get_recipes([
            'restaurant_parent_id' => $restaurant_parent->id
          ]))
          ->render();

        break;

      case 'robot':

        $food->update_ingredients([
          'ingredients' => $ingredients,
          'restaurant_parent_id' => $restaurant_parent_id,
        ]);

        $html = view('tastevn.htmls.item_restaurant_parent_food_robot')
          ->with('items', $food->get_ingredients([
            'restaurant_parent_id' => $restaurant_parent->id
          ]))
          ->render();

        break;
    }

    return response()->json([
      'status' => true,

      'html' => $html,

      'datas' => $this->restaurant_food_datas($restaurant_parent, $food),
    ], 200);
  }

  public function foods(Request $request)
  {
    $invalid_roles = ['user', 'moderator'];
    if (in_array($this->_viewer->role, $invalid_roles)) {
      return redirect('admin/photos');
    }

    $restaurants = RestaurantParent::where('deleted', 0)
      ->orderBy('id', 'asc')
      ->get();

    $foods = Food::where('deleted', 0)
      ->orderByRaw('TRIM(LOWER(name))')
      ->get();

    $pageConfigs = [
      'myLayout' => 'horizontal',
      'hasCustomizer' => false,

      'restaurants' => $restaurants,
      'foods' => $foods,
    ];

//    $this->_viewer->add_log([
//      'type' => 'view_listing_restaurant_food',
//    ]);

    return view('tastevn.pages.restaurant_foods', ['pageConfigs' => $pageConfigs]);
  }

  public function food_serve(Request $request)
  {
    $values = $request->post();

    $restaurant_parent_id = isset($values['restaurant_parent_id']) ? (int)$values['restaurant_parent_id'] : 0;
    $restaurant_parent = RestaurantParent::find($restaurant_parent_id);
    if (!$restaurant_parent) {
      return response()->json([
        'error' => 'Invalid data'
      ], 404);
    }

    $datas = [];

    $foods = $restaurant_parent->get_foods();
    if (count($foods)) {
      foreach ($foods as $f) {
        $food = Food::find($f->food_id);

        $datas[] = $this->restaurant_food_datas($restaurant_parent, $food);
      }
    }

    return response()->json([
      'status' => true,

      'datas' => $datas,
    ], 200);
  }

  public function food_sync(Request $request)
  {
    $values = $request->post();

    //required
    $validator = Validator::make($values, [
      'restaurant_parent_id' => 'required',
      'food_id' => 'required',
      'type' => 'required',
    ]);
    if ($validator->fails()) {
      return response()->json($validator->errors(), 422);
    }

    $restaurant_parent_id = isset($values['restaurant_parent_id']) ? (int)$values['restaurant_parent_id'] : 0;
    $restaurant_parent = RestaurantParent::find($restaurant_parent_id);
    $food_id = isset($values['food_id']) ? (int)$values['food_id'] : 0;
    $food = Food::find($food_id);
    if (!$restaurant_parent || !$food) {
      return response()->json([
        'error' => 'Invalid data'
      ], 404);
    }

    $type = isset($values['type']) ? $values['type'] : 'recipe';
    $restaurants = isset($values['restaurants']) ? (array)$values['restaurants'] : [];
    if (!count($restaurants)) {
      return response()->json([
        'error' => 'Invalid restaurants',
        'restaurants' => $restaurants,
      ], 404);
    }

    foreach ($restaurants as $rid) {
      $restaurant2 = Restaurant::find((int)$rid);

      if (!$restaurant2 || $restaurant_parent->id == (int)$rid) {
        continue;
      }

      switch ($type) {
        case 'robot':

          $ingredients = FoodIngredient::where('deleted', 0)
            ->where('restaurant_parent_id', $restaurant_parent->id)
            ->where('food_id', $food->id)
            ->get()
            ->toArray();
          if (count($ingredients)) {

            FoodIngredient::where('restaurant_parent_id', $restaurant2->id)
              ->where('food_id', $food->id)
              ->delete();

            foreach ($ingredients as $ing) {
              $ing = (array)$ing;

              unset($ing['id']);
              unset($ing['created_at']);
              unset($ing['updated_at']);

              $ing['restaurant_parent_id'] = $restaurant2->id;

              FoodIngredient::create($ing);
            }

            //serve
            $restaurant1_food = RestaurantFood::where('restaurant_parent_id', $restaurant_parent->id)
              ->where('food_id', $food->id)
              ->limit(1)
              ->first();

            $restaurant2_food = RestaurantFood::where('restaurant_parent_id', $restaurant2->id)
              ->where('food_id', $food->id)
              ->limit(1)
              ->first();
            if (!$restaurant2_food) {
              $restaurant2_food = RestaurantFood::create([
                'restaurant_parent_id' => $restaurant2->id,
                'food_id' => $food->id,
                'creator_id' => $this->_viewer->id,
              ]);
            }

//            $restaurant2_food->update([
//              'food_category_id' => $restaurant1_food->food_category_id,
//              'confidence' => $restaurant1_food->confidence,
//              'photo' => $restaurant1_food->photo,
//              'local_storage' => $restaurant1_food->local_storage,
//              'live_group' => $restaurant1_food->live_group,
//              'model_name' => $restaurant1_food->model_name,
//              'model_version' => $restaurant1_food->model_version,
//            ]);
          }

          break;

        case 'recipe':

          $ingredients = FoodRecipe::where('deleted', 0)
            ->where('restaurant_parent_id', $restaurant_parent->id)
            ->where('food_id', $food->id)
            ->get()
            ->toArray();
          if (count($ingredients)) {

            FoodRecipe::where('restaurant_parent_id', $restaurant2->id)
              ->where('food_id', $food->id)
              ->delete();

            foreach ($ingredients as $ing) {
              $ing = (array)$ing;

              unset($ing['id']);
              unset($ing['created_at']);
              unset($ing['updated_at']);

              $ing['restaurant_parent_id'] = $restaurant2->id;

              FoodRecipe::create($ing);
            }
          }

          break;
      }
    }

    return response()->json([
      'status' => true,

      'restaurants' => $restaurants,
    ], 200);
  }

  protected function restaurant_food_datas(RestaurantParent $restaurant_parent, Food $food)
  {
    $food_photo = $food->get_photo([
      'restaurant_parent_id' => $restaurant_parent->id
    ]);
    $food_category = $food->get_category([
      'restaurant_parent_id' => $restaurant_parent->id
    ]);
    $food_live_group = $food->get_live_group([
      'restaurant_parent_id' => $restaurant_parent->id
    ]);
    $food_confidence = $food->get_food_confidence([
      'restaurant_parent_id' => $restaurant_parent->id
    ]);
    $food_ingredients = $food->get_ingredients([
      'restaurant_parent_id' => $restaurant_parent->id
    ]);

    $datas = [
      'food_id' => $food->id,
      'food_photo' => $food_photo,
      'food_category_id' => $food_category ? $food_category->id : 0,
      'food_category_name' => $food_category ? $food_category->name : '',
      'food_live_group' => $food_live_group,
      'food_confidence' => $food_confidence,
      'ingredients' => count($food_ingredients) ? $food_ingredients->toArray() : [],
    ];

    return $datas;
  }
}
