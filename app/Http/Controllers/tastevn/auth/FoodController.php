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
use App\Models\Food;
use App\Models\RestaurantParent;
use App\Models\Ingredient;
use App\Models\Restaurant;

class FoodController extends Controller
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

  /**
   * Display a listing of the resource.
   */
  public function index(Request $request)
  {
    $invalid_roles = ['user'];
    if (in_array($this->_viewer->role, $invalid_roles)) {
      return redirect('error/404');
    }

    $pageConfigs = [
      'myLayout' => 'horizontal',
      'hasCustomizer' => false,
    ];

    $this->_viewer->add_log([
      'type' => 'view_listing_food',
    ]);

    return view('tastevn.pages.foods', ['pageConfigs' => $pageConfigs]);
  }

  public function create(Request $request)
  {
    //
  }

  /**
   * Store a newly created resource in storage.
   */
  public function store(Request $request)
  {
    $values = $request->all();

    //required
    $validator = Validator::make($values, [
      'name' => 'required|string',
    ]);
    if ($validator->fails()) {
      return response()->json($validator->errors(), 422);
    }

    //restore
    $row = Food::whereRaw('LOWER(name) LIKE ?', strtolower(trim($values['name'])))
      ->first();
    if ($row) {
      //existed
      return response()->json([
        'error' => 'Name existed'
      ], 422);
    }

    //ingredients
//    $ingredients = isset($values['ingredients']) && !empty($values['ingredients'])
//      ? (array)json_decode($values['ingredients'], true) : [];
//    if (!count($ingredients)) {
//      return response()->json([
//        'error' => 'Ingredients required'
//      ], 422);
//    }

    $row = Food::create([
      'name' => ucwords(strtolower(trim($values['name']))),
      'creator_id' => $this->_viewer->id,

      'live_group' => isset($values['live_group']) && (int)$values['live_group'] < 4 ? (int)$values['live_group'] : 3,
    ]);

//    $row->add_ingredients($ingredients);

    $this->_viewer->add_log([
      'type' => 'add_' . $row->get_type(),
      'item_id' => (int)$row->id,
      'item_type' => $row->get_type(),
    ]);

    //photo
//    $file_photo = $request->file('photo');
//    if (!empty($file_photo)) {
//      foreach ($file_photo as $file) {
//        $file_path = '/uploaded/food/';
//        $full_path = public_path($file_path);
//        //os
//        if (strtoupper(substr(PHP_OS, 0, 3)) == 'WIN') {
//          $full_path = str_replace('/', '\\', $full_path);
//        }
//        if (!file_exists($full_path)) {
//          mkdir($full_path, 0777, true);
//        }
//
//        $file_name = 'food_' . $row->id . '.' . $file->getClientOriginalExtension();
//        $file->move(public_path($file_path), $file_name);
//
//        $row->update([
//          'photo' => $file_path . $file_name
//        ]);
//      }
//    }

    return response()->json([
      'status' => true,
      'item' => $row->name,
    ], 200);
  }

  /**
   * Display the specified resource.
   */
  public function show(string $id)
  {
    //
  }

  /**
   * Show the form for editing the specified resource.
   */
  public function edit(string $id)
  {
    //
  }

  /**
   * Update the specified resource in storage.
   */
  public function update(Request $request)
  {
    $values = $request->all();

    //required
    $validator = Validator::make($values, [
      'item' => 'required',
      'name' => 'required|string',
    ]);
    if ($validator->fails()) {
      return response()->json($validator->errors(), 422);
    }

    //invalid
    $row = Food::find((int)$values['item']);
    if (!$row) {
      return response()->json([
        'error' => 'Invalid item'
      ], 422);
    }
    //restore
    $row1 = Food::whereRaw('LOWER(name) LIKE ?', strtolower(trim($values['name'])))
      ->first();
    if ($row1) {
//      if ($row1->deleted) {
//        return response()->json([
//          'type' => 'can_restored',
//          'error' => 'Item deleted'
//        ], 422);
//      }
      //existed
      if ($row1->id != $row->id) {
        return response()->json([
          'error' => 'Name existed'
        ], 422);
      }
    }

    //ingredients
//    $ingredients = isset($values['ingredients']) && !empty($values['ingredients'])
//      ? (array)json_decode($values['ingredients'], true) : [];
//    if (!count($ingredients)) {
//      return response()->json([
//        'error' => 'Ingredients required'
//      ], 422);
//    }

    $diffs['before'] = $row->get_log();

    $row->update([
      'name' => ucwords(strtolower(trim($values['name']))),

      'live_group' => isset($values['live_group']) && (int)$values['live_group'] < 4 ? (int)$values['live_group'] : 3,
    ]);

//    $row->update_ingredients($ingredients);

    //photo
//    $file_photo = $request->file('photo');
//    if (!empty($file_photo)) {
//      foreach ($file_photo as $file) {
//        $file_path = '/uploaded/food/';
//        $full_path = public_path($file_path);
//        //os
//        if (strtoupper(substr(PHP_OS, 0, 3)) == 'WIN') {
//          $full_path = str_replace('/', '\\', $full_path);
//        }
//        if (!file_exists($full_path)) {
//          mkdir($full_path, 0777, true);
//        }
//
//        $file_name = 'food_' . $row->id . '.' . $file->getClientOriginalExtension();
//        $file->move(public_path($file_path), $file_name);
//
//        $row->update([
//          'photo' => $file_path . $file_name
//        ]);
//      }
//    }

    $row = Food::find($row->id);
    $diffs['after'] = $row->get_log();
    if (json_encode($diffs['before']) !== json_encode($diffs['after'])) {
      $this->_viewer->add_log([
        'type' => 'edit_' . $row->get_type(),
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

  public function update_ingredient(Request $request)
  {
    $values = $request->post();

    //required
    $validator = Validator::make($values, [
      'item' => 'required',
      'restaurant_parent_id' => 'required',
    ]);
    if ($validator->fails()) {
      return response()->json($validator->errors(), 422);
    }

    //invalid
    $row = Food::find((int)$values['item']);
    if (!$row) {
      return response()->json([
        'error' => 'Invalid item'
      ], 422);
    }

    //restaurant_parent_id
    $restaurant_parent_id = (int)$values['restaurant_parent_id'];
    if (!$restaurant_parent_id) {
      return response()->json([
        'error' => 'Invalid restaurant'
      ], 422);
    }

    $restaurant_parent = RestaurantParent::find($restaurant_parent_id);

    //ingredients
    $ingredients = isset($values['ingredients']) && !empty($values['ingredients'])
      ? (array)json_decode($values['ingredients'], true) : [];
    if (!count($ingredients)) {
      return response()->json([
        'error' => 'Ingredients required'
      ], 422);
    }

    $diffs['before'] = $row->get_log_ingredient([
      'restaurant_parent_id' => $restaurant_parent_id,
    ]);

    $row->update_ingredients([
      'ingredients' => $ingredients,
      'restaurant_parent_id' => $restaurant_parent_id,
    ]);

    $row = Food::find($row->id);
    $diffs['after'] = $row->get_log_ingredient([
      'restaurant_parent_id' => $restaurant_parent_id,
    ]);
    if (json_encode($diffs['before']) !== json_encode($diffs['after'])) {

      //serve
      $items = [];
      $items[] = [
        'food_id' => $row->id,
      ];

      $this->_viewer->add_log([
        'type' => 'edit_' . $row->get_type() . '_ingredient',
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

  public function update_recipe(Request $request)
  {
    $values = $request->post();

    //required
    $validator = Validator::make($values, [
      'item' => 'required',
      'restaurant_parent_id' => 'required',
    ]);
    if ($validator->fails()) {
      return response()->json($validator->errors(), 422);
    }

    //invalid
    $row = Food::find((int)$values['item']);
    if (!$row) {
      return response()->json([
        'error' => 'Invalid item'
      ], 422);
    }

    //restaurant_parent_id
    $restaurant_parent_id = (int)$values['restaurant_parent_id'];
    if (!$restaurant_parent_id) {
      return response()->json([
        'error' => 'Invalid restaurant'
      ], 422);
    }

    //ingredients
    $ingredients = isset($values['ingredients']) && !empty($values['ingredients'])
      ? (array)json_decode($values['ingredients'], true) : [];
    if (!count($ingredients)) {
      return response()->json([
        'error' => 'Ingredients required'
      ], 422);
    }

    $diffs['before'] = $row->get_log_recipe([
      'restaurant_parent_id' => $restaurant_parent_id,
    ]);

    $row->update_ingredients_recipe([
      'ingredients' => $ingredients,
      'restaurant_parent_id' => $restaurant_parent_id,
    ]);

    $row = Food::find($row->id);
    $diffs['after'] = $row->get_log_recipe([
      'restaurant_parent_id' => $restaurant_parent_id,
    ]);
    if (json_encode($diffs['before']) !== json_encode($diffs['after'])) {
      $this->_viewer->add_log([
        'type' => 'edit_' . $row->get_type() . '_recipe',
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

  /**
   * Remove the specified resource from storage.
   */
  public function destroy(string $id)
  {
    //
  }

  public function delete(Request $request)
  {
    //
  }

  public function restore(Request $request)
  {
    //
  }

  public function selectize(Request $request)
  {
    $values = $request->all();

    return response()->json([
      'items' => $this->selectize_items($values)
    ]);
  }

  protected function selectize_items($pars = [])
  {
    $keyword = isset($pars['keyword']) && !empty($pars['keyword']) ? $pars['keyword'] : NULL;

    $restaurant_parent_id = isset($pars['restaurant_parent_id']) ? (int)$pars['restaurant_parent_id'] : 0;
    $restaurant_parent = RestaurantParent::find($restaurant_parent_id);

    $items = [];

    if ($restaurant_parent) {
      $items = $restaurant_parent->get_foods([
        'keyword' => $keyword,
        'select_data' => 'food_only',
      ]);
    } else {
      $select = Food::select('id', 'name')
        ->orderByRaw('TRIM(LOWER(name))');

      if (!empty($keyword)) {
        $select->where('name', 'LIKE', "%{$keyword}%");
      }

      $items = $select->get();
    }

    return count($items) ? $items->toArray() : [];
  }

  public function ingredient_html(Request $request)
  {
    return response()->json([
      'html' => view('tastevn.htmls.item_ingredient_input')->render(),
    ]);
  }

  public function recipe_html(Request $request)
  {
    return response()->json([
      'html' => view('tastevn.htmls.item_ingredient_recipe_input')->render(),
    ]);
  }

  public function get(Request $request)
  {
    $values = $request->all();

    $validator = Validator::make($values, [
      'item' => 'required',
    ]);
    if ($validator->fails()) {
      return response()->json($validator->errors(), 422);
    }
    //invalid
    $row = Food::find((int)$values['item']);
    if (!$row) {
      return response()->json([
        'error' => 'Invalid item'
      ], 422);
    }

    $this->_viewer->add_log([
      'type' => 'view_item_' . $row->get_type(),
      'item_id' => (int)$row->id,
      'item_type' => $row->get_type(),
    ]);

    return response()->json([
      'item' => $row,
    ]);
  }

  public function get_info(Request $request)
  {
    $values = $request->all();

    $validator = Validator::make($values, [
      'item' => 'required',
      'restaurant_parent_id' => 'required',
    ]);
    if ($validator->fails()) {
      return response()->json($validator->errors(), 422);
    }
    //invalid
    $row = Food::find((int)$values['item']);
    if (!$row) {
      return response()->json([
        'error' => 'Invalid item'
      ], 422);
    }

    $restaurant_parent_id = isset($values['restaurant_parent_id']) ? (int)$values['restaurant_parent_id'] : 0;

    $food_photo = $row->get_photo([
      'restaurant_parent_id' => $restaurant_parent_id,
    ]);

    //info
    $html_ingredient = view('tastevn.htmls.item_food_info_ingredient')
      ->with('ingredients', $row->get_ingredients([
        'restaurant_parent_id' => $restaurant_parent_id
      ]))
      ->render();

    $html_recipe = view('tastevn.htmls.item_food_info_recipe')
      ->with('ingredients', $row->get_recipes([
        'restaurant_parent_id' => $restaurant_parent_id
      ]))
      ->render();

    return response()->json([
      'item' => $row,
      'food_photo' => $food_photo,

      'html_ingredient' => $html_ingredient,
      'html_recipe' => $html_recipe,
    ]);
  }

  public function get_ingredient(Request $request)
  {
    $values = $request->post();

    $validator = Validator::make($values, [
      'item' => 'required',
      'restaurant_parent_id' => 'required',
    ]);
    if ($validator->fails()) {
      return response()->json($validator->errors(), 422);
    }
    //invalid
    $row = Food::find((int)$values['item']);
    $restaurant_parent_id = (int)$values['restaurant_parent_id'];
    if (!$row || !$restaurant_parent_id) {
      return response()->json([
        'error' => 'Invalid item'
      ], 422);
    }

    //edit
    $html = view('tastevn.htmls.item_ingredient_input')
      ->with('ingredients', $row->get_ingredients([
        'restaurant_parent_id' => $restaurant_parent_id
      ]))
      ->render();

    return response()->json([
      'item' => $row,
      'html' => $html,
    ]);
  }

  public function get_recipe(Request $request)
  {
    $values = $request->post();

    $validator = Validator::make($values, [
      'item' => 'required',
      'restaurant_parent_id' => 'required',
    ]);
    if ($validator->fails()) {
      return response()->json($validator->errors(), 422);
    }
    //invalid
    $row = Food::find((int)$values['item']);
    $restaurant_parent_id = (int)$values['restaurant_parent_id'];
    if (!$row || !$restaurant_parent_id) {
      return response()->json([
        'error' => 'Invalid item'
      ], 422);
    }

    //edit
    $html = view('tastevn.htmls.item_ingredient_recipe_input')
      ->with('ingredients', $row->get_recipes([
        'restaurant_parent_id' => $restaurant_parent_id
      ]))
      ->render();

    return response()->json([
      'item' => $row,
      'html' => $html,
    ]);
  }

  public function import(Request $request)
  {
    $values = $request->post();
    $restaurant_parent_id = isset($values['restaurant_parent_id']) ? (int)$values['restaurant_parent_id'] : 0;

    $datas = (new ImportData())->toArray($request->file('excel'));
    if (!count($datas) || !count($datas[0]) || !$restaurant_parent_id) {
      return response()->json([
        'error' => 'Invalid data'
      ], 404);
    }

    $faileds = [];
    $temp_count = 0;
    $temps = [];
    $food_count = 0;

    DB::beginTransaction();
    try {

      foreach ($datas[0] as $k => $data) {

        $col1 = trim($data[0]);
        $col2 = trim($data[1]);
        $col3 = isset($data[2]) && !empty(trim($data[2])) ? (int)trim($data[2]) : 1;

        if (!(!empty($col1) || (!empty($col2) && !empty($col3)))) {
          continue;
        }

        if (!empty($col1)) {

          $temp_count++;
          $temps['food_' . $temp_count]['food'] = $col1;

        } elseif (!empty($col2) && !empty($col3)) {

          $temps['food_' . $temp_count]['ingredient'][] = [
            'quantity' => $col3,
            'ingredient' => $col2,
          ];

        }
      }

      if (count($temps)) {
        foreach ($temps as $temp) {

          $row = Food::whereRaw('LOWER(name) LIKE ?', strtolower($temp['food']))
            ->first();

          $existed = $row ? count($row->get_ingredients([
            'restaurant_parent_id' => $restaurant_parent_id
          ])) : 0;
          if (!isset($temp['ingredient']) || !count($temp['ingredient']) || $existed) {
            $faileds[] = $temp;
            continue;
          }

          $food_count++;

          if (!$row) {
            $row = Food::create([
              'name' => ucwords(strtolower($temp['food'])),
              'creator_id' => $this->_viewer->id,
            ]);
          }

          $ingredients = [];
          foreach ($temp['ingredient'] as $ing) {
            $ingredient = Ingredient::whereRaw('LOWER(name) LIKE ?', strtolower($ing['ingredient']))
              ->first();
            if (!$ingredient) {
              $ingredient = Ingredient::create([
                'name' => strtolower($ing['ingredient'])
              ]);
            }

            $ingredients[] = [
              'id' => $ingredient->id,
              'quantity' => $ing['quantity'],
              'core' => 0,
              'color' => null,
            ];
          }

          $row->add_ingredients([
            'restaurant_parent_id' => $restaurant_parent_id,
            'ingredients' => $ingredients,
          ]);

          $row->add_recipes([
            'restaurant_parent_id' => $restaurant_parent_id,
            'ingredients' => $ingredients,
          ]);

          $this->_viewer->add_log([
            'type' => 'import_' . $row->get_type(),
            'item_id' => (int)$row->id,
            'item_type' => $row->get_type(),
          ]);
        }
      }

      DB::commit();

    } catch (\Exception $e) {
      DB::rollback();

      return response()->json([
        'error' => 'Error transaction! Please try again later.', //$e->getMessage()
      ], 422);
    }

    if ($food_count) {
      return response()->json([
        'status' => true,
        'message' => 'import food= ' . $food_count,
      ], 200);
    }

    return response()->json([
      'error' => 'Invalid data or dishes existed',
    ], 422);
  }

  public function import_recipe(Request $request)
  {
    $values = $request->post();
    $restaurant_parent_id = isset($values['restaurant_parent_id']) ? (int)$values['restaurant_parent_id'] : 0;

    $datas = (new ImportData())->toArray($request->file('excel'));
    if (!count($datas) || !count($datas[0]) || !$restaurant_parent_id) {
      return response()->json([
        'error' => 'Invalid data'
      ], 404);
    }

    $faileds = [];
    $temp_count = 0;
    $temps = [];
    $food_count = 0;

    DB::beginTransaction();
    try {

      foreach ($datas[0] as $k => $data) {

        $col1 = trim($data[0]);
        $col2 = trim($data[1]);
        $col3 = isset($data[2]) && !empty(trim($data[2])) ? (int)trim($data[2]) : 1;

        if (!(!empty($col1) || (!empty($col2) && !empty($col3)))) {
          continue;
        }

        if (!empty($col1)) {

          $temp_count++;
          $temps['food_' . $temp_count]['food'] = $col1;

        } elseif (!empty($col2) && !empty($col3)) {

          $temps['food_' . $temp_count]['ingredient'][] = [
            'quantity' => $col3,
            'ingredient' => $col2,
          ];

        }
      }

      if (count($temps)) {
        foreach ($temps as $temp) {

          $row = Food::whereRaw('LOWER(name) LIKE ?', strtolower($temp['food']))
            ->first();

          $existed = $row ? count($row->get_recipes([
            'restaurant_parent_id' => $restaurant_parent_id
          ])) : 0;
          if (!isset($temp['ingredient']) || !count($temp['ingredient']) || $existed) {
            $faileds[] = $temp;
            continue;
          }

          $food_count++;

          if (!$row) {
            $row = Food::create([
              'name' => ucwords(strtolower($temp['food'])),
              'creator_id' => $this->_viewer->id,
            ]);
          }


          $ingredients = [];
          foreach ($temp['ingredient'] as $ing) {
            $ingredient = Ingredient::whereRaw('LOWER(name) LIKE ?', strtolower($ing['ingredient']))
              ->first();
            if (!$ingredient) {
              $ingredient = Ingredient::create([
                'name' => strtolower($ing['ingredient'])
              ]);
            }

            $ingredients[] = [
              'id' => $ingredient->id,
              'quantity' => $ing['quantity'],
            ];
          }

          $row->add_recipes([
            'restaurant_parent_id' => $restaurant_parent_id,
            'ingredients' => $ingredients,
          ]);

          $this->_viewer->add_log([
            'type' => 'import_recipe_' . $row->get_type(),
            'item_id' => (int)$row->id,
            'item_type' => $row->get_type(),
          ]);
        }
      }

      DB::commit();

    } catch (\Exception $e) {
      DB::rollback();

      return response()->json([
//        'error' => 'Error transaction! Please try again later.',
        'error' => $e->getMessage(),
      ], 422);
    }

    if ($food_count) {
      return response()->json([
        'status' => true,
        'message' => 'import food= ' . $food_count,
      ], 200);
    }

    return response()->json([
      'error' => 'Invalid data or dishes existed',
    ], 422);
  }
}
