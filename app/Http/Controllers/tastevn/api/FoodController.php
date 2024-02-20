<?php

namespace App\Http\Controllers\tastevn\api;

use App\Http\Controllers\Controller;
use App\Models\RestaurantFood;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;
use Validator;
use App\Models\Food;

class FoodController extends Controller
{
  public function __construct()
  {
    $this->middleware(function ($request, $next) {
      return $next($request);
    });

    $this->middleware('auth');
  }

  /**
   * Display a listing of the resource.
   */
  public function index(Request $request)
  {
    $pageConfigs = [
      'myLayout' => 'horizontal',
      'hasCustomizer' => false,
    ];

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
    $viewer = Auth::user();
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
    $ingredients = isset($values['ingredients']) && !empty($values['ingredients'])
      ? (array)json_decode($values['ingredients'], true) : [];
    if (!count($ingredients)) {
      return response()->json([
        'error' => 'Ingredients required'
      ], 422);
    }

    $row = Food::create([
      'name' => trim($values['name']),
      'creator_id' => $viewer->id,
    ]);

    $row->add_ingredients($ingredients);

    //photo
    $file_photo = $request->file('photo');
    if (!empty($file_photo)) {
      foreach ($file_photo as $file) {
        $file_path = '/uploaded/food/';
        $full_path = public_path($file_path);
        //os
        if (strtoupper(substr(PHP_OS, 0, 3)) == 'WIN') {
          $full_path = str_replace('/', '\\', $full_path);
        }
        if (!file_exists($full_path)) {
          mkdir($full_path, 0777, true);
        }

        $file_name = 'food_' . $row->id . '.' . $file->getClientOriginalExtension();
        $file->move(public_path($file_path), $file_name);

        $row->update([
          'photo' => $file_path . $file_name
        ]);
      }
    }

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
    $viewer = Auth::user();
    //required
    $validator = Validator::make($values, [
      'item' => 'required',
      'name' => 'required|string',
    ]);
    if ($validator->fails()) {
      return response()->json($validator->errors(), 422);
    }

    //invalid
    $row = Food::findOrFail((int)$values['item']);
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
    $ingredients = isset($values['ingredients']) && !empty($values['ingredients'])
      ? (array)json_decode($values['ingredients'], true) : [];
    if (!count($ingredients)) {
      return response()->json([
        'error' => 'Ingredients required'
      ], 422);
    }

    $row->update([
      'name' => trim($values['name']),
    ]);

    $row->update_ingredients($ingredients);

    //photo
    $file_photo = $request->file('photo');
    if (!empty($file_photo)) {
      foreach ($file_photo as $file) {
        $file_path = '/uploaded/food/';
        $full_path = public_path($file_path);
        //os
        if (strtoupper(substr(PHP_OS, 0, 3)) == 'WIN') {
          $full_path = str_replace('/', '\\', $full_path);
        }
        if (!file_exists($full_path)) {
          mkdir($full_path, 0777, true);
        }

        $file_name = 'food_' . $row->id . '.' . $file->getClientOriginalExtension();
        $file->move(public_path($file_path), $file_name);

        $row->update([
          'photo' => $file_path . $file_name
        ]);
      }
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
    $select = Food::query("foods")
      ->select('foods.id', 'foods.name');

    $keyword = isset($pars['keyword']) && !empty($pars['keyword']) ? $pars['keyword'] : NULL;
    if (!empty($keyword)) {
      $select->where('foods.name', 'LIKE', "%{$keyword}%");
    }

    $restaurant_id = isset($pars['restaurant']) && !empty($pars['restaurant']) ? (int)$pars['restaurant'] : 0;
    if ($restaurant_id) {
      $ids = RestaurantFood::select('food_id')
        ->where('restaurant_id', $restaurant_id)
        ->where('deleted', 0);

      $select->whereNotIn('foods.id', $ids);
    }

    return $select->get()->toArray();
  }

  public function ingredient_html(Request $request)
  {
    return response()->json([
      'html' => view('tastevn.htmls.item_ingredient_input')->render(),
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
    $row = Food::findOrFail((int)$values['item']);
    if (!$row) {
      return response()->json([
        'error' => 'Invalid item'
      ], 422);
    }

    //info
    $html_info = view('tastevn.htmls.item_food_info')
      ->with('item', $row)
      ->with('ingredients', $row->get_ingredients())
      ->with('restaurants', $row->get_restaurants())
      ->render();

    //edit
    $html_edit = view('tastevn.htmls.item_ingredient_input')
      ->with('ingredients', $row->get_ingredients())
      ->render();

    //scan update
    $html_scan_update = view('tastevn.htmls.item_ingredient_select')
      ->with('ingredients', $row->get_ingredients())
      ->render();

    //selected
    $html_selected = view('tastevn.htmls.item_food_selected')
      ->with('ingredients', $row->get_ingredients())
      ->render();

    return response()->json([
      'item' => $row,
      'item_photo' => $row->get_photo(),

      'html_scan_update' => $html_scan_update,
      'html_info' => $html_info,
      'html_ingredients' => $html_edit,
      'html_selected' => $html_selected,
    ]);
  }
}
