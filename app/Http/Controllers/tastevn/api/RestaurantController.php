<?php

namespace App\Http\Controllers\tastevn\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;
use Validator;

use App\Api\SysCore;

use Illuminate\Support\Facades\Notification;
use App\Notifications\IngredientMissing;

use App\Models\Restaurant;
use App\Models\RestaurantFoodScan;
use App\Models\Food;
use App\Models\Ingredient;
use App\Models\Text;
use App\Models\RestaurantFoodScanText;

class RestaurantController extends Controller
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
    $user = Auth::user();
    $invalid_roles = ['user'];
    if (in_array($user->role, $invalid_roles)) {
      return redirect('page_not_found');
    }

    $pageConfigs = [
      'myLayout' => 'horizontal',
      'hasCustomizer' => false,
    ];

    return view('tastevn.pages.dashboard', ['pageConfigs' => $pageConfigs]);
  }

  /**
   * Show the form for creating a new resource.
   */
  public function create()
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
    $row = Restaurant::whereRaw('LOWER(name) LIKE ?', strtolower(trim($values['name'])))
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

    $row = Restaurant::create([
      'name' => trim($values['name']),
      's3_bucket_name' => isset($values['s3_bucket_name']) ? trim($values['s3_bucket_name']) : '',
      's3_bucket_address' => isset($values['s3_bucket_address']) ? trim($values['s3_bucket_address']) : '',
      'creator_id' => $viewer->id,
    ]);

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
    $row = Restaurant::find((int)$id);
    if (!$row) {
      return redirect('page_not_found');
    }

    $user = Auth::user();
    $invalid_roles = ['user'];
    if (in_array($user->role, $invalid_roles)) {
      return redirect('page_not_found');
    }

    $user = Auth::user();
    if (!$user->can_access_restaurant($row)) {
      return redirect('page_not_found');
    }

    $pageConfigs = [
      'myLayout' => 'horizontal',
      'hasCustomizer' => false,

      'item' => $row,
    ];

    return view('tastevn.pages.restaurant_info', ['pageConfigs' => $pageConfigs]);
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
    $row = Restaurant::findOrFail((int)$values['item']);
    if (!$row) {
      return response()->json([
        'error' => 'Invalid item'
      ], 422);
    }
    //restore
    $row1 = Restaurant::whereRaw('LOWER(name) LIKE ?', strtolower(trim($values['name'])))
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

    $row->update([
      'name' => trim($values['name']),
      's3_bucket_name' => isset($values['s3_bucket_name']) ? trim($values['s3_bucket_name']) : '',
      's3_bucket_address' => isset($values['s3_bucket_address']) ? trim($values['s3_bucket_address']) : '',
    ]);

    $row->on_update_after();

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
    $values = $request->all();
    $viewer = Auth::user();
    //required
    $validator = Validator::make($values, [
      'item' => 'required',
    ]);
    if ($validator->fails()) {
      return response()->json($validator->errors(), 422);
    }

    $row = Restaurant::findOrFail((int)$values['item']);
    if (!$row) {
      return response()->json([
        'error' => 'Invalid item'
      ], 422);
    }

    $row->update([
      'deleted' => $viewer->id,
    ]);

    $row->on_delete_after();

    return response()->json([
      'status' => true,
      'item' => $row->name,
    ], 200);
  }

  public function restore(Request $request)
  {
    $values = $request->all();
    $viewer = Auth::user();
    //required
    $validator = Validator::make($values, [
      'item' => 'required',
    ]);
    if ($validator->fails()) {
      return response()->json($validator->errors(), 422);
    }

    $row = Restaurant::whereRaw('LOWER(name) LIKE ?', strtolower(trim($values['item'])))
      ->first();
    if (!$row) {
      return response()->json([
        'error' => 'Invalid item'
      ], 422);
    }

    $row->update([
      'deleted' => 0,
    ]);

    return response()->json([
      'status' => true,
      'item' => $row->name,
    ], 200);
  }

  public function selectize(Request $request)
  {
    $values = $request->all();
    $keyword = isset($values['keyword']) && !empty($values['keyword']) ? $values['keyword'] : NULL;

    $select = Restaurant::select('id', 'name')
      ->where('deleted', 0);
    if (!empty($keyword)) {
      $select->where('name', 'LIKE', "%{$keyword}%");
    }

    return response()->json([
      'items' => $select->get()->toArray()
    ]);
  }

  public function food_add(Request $request)
  {
    $values = $request->all();
//    echo '<pre>';var_dump($values);die;
    //required
    $validator = Validator::make($values, [
      'item' => 'required',
    ]);
    if ($validator->fails()) {
      return response()->json($validator->errors(), 422);
    }

    $row = Restaurant::findOrFail((int)$values['item']);
    if (!$row) {
      return response()->json([
        'error' => 'Invalid item'
      ], 422);
    }

    //foods
    $foods = isset($values['foods']) && count($values['foods']) ? $values['foods'] : [];
    if (!count($foods)) {
      return response()->json([
        'error' => 'Foods required'
      ], 422);
    }

    $category = isset($values['category']) && !empty($values['category']) ? (int)$values['category'] : 0;

    $row->add_foods($foods, $category);

    return response()->json([
      'status' => true,
      'item' => $row->name,
    ], 200);
  }

  public function food_delete(Request $request)
  {
    $values = $request->all();
//    echo '<pre>';var_dump($values);die;
    //required
    $validator = Validator::make($values, [
      'item' => 'required',
      'food' => 'required',
    ]);
    if ($validator->fails()) {
      return response()->json($validator->errors(), 422);
    }

    $row = Restaurant::findOrFail((int)$values['item']);
    $food = Food::findOrFail((int)$values['food']);
    if (!$row && !$food) {
      return response()->json([
        'error' => 'Invalid item'
      ], 422);
    }

    $row->delete_food($food);

    return response()->json([
      'status' => true,
      'item' => $row->name,
    ], 200);
  }

  public function food_scan(Request $request)
  {
    $values = $request->all();
//    echo '<pre>';var_dump($values);die;
    //required
    $validator = Validator::make($values, [
      'item' => 'required',
    ]);
    if ($validator->fails()) {
      return response()->json($validator->errors(), 422);
    }

    $row = Restaurant::findOrFail((int)$values['item']);
    if (!$row) {
      return response()->json([
        'error' => 'Invalid item'
      ], 422);
    }

    $imgs = RestaurantFoodScan::where('status', 'new')
      ->where('restaurant_id', $row->id)
      ->orderBy('id', 'asc')
      ->get();

    //scannnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnn
    $api_core = new SysCore();

//    if (!count($imgs)) {
//      $api_core->s3_get_photos([
//        'restaurant_id' => $row->id,
//        'scan_date' => isset($values['date']) && !empty($values['date']) ? $values['date'] : NULL,
//        'scan_hour' => isset($values['hour']) && !empty($values['hour']) ? $values['hour'] : NULL,
//      ]);
//    }
//
//    $api_core->rbf_scan_photos([
//      'restaurant_id' => $row->id,
//    ]);
//
//    $api_core->sys_predict_photos([
//      'restaurant_id' => $row->id,
//    ]);

    return response()->json([
      'status' => true,
//      'notify' => $notify ? $notify->id : 0,
    ], 200);
  }

  public function food_scan_info(Request $request)
  {
    $values = $request->all();
    $api_core = new SysCore();

    $validator = Validator::make($values, [
      'item' => 'required',
    ]);
    if ($validator->fails()) {
      return response()->json($validator->errors(), 422);
    }
    //invalid
    $row = RestaurantFoodScan::findOrFail((int)$values['item']);
    if (!$row) {
      return response()->json([
        'error' => 'Invalid item'
      ], 422);
    }

    $food_photo = url('custom/img/food_photo.jpg');

    $rbf_food_id = 0;
    $rbf_food_name = NULL;
    $rbf_food_confidence = 0;
    $rbf_ingredients_found = [];
    $rbf_ingredients_missing = [];

    $sys_food_id = 0;
    $sys_food_name = NULL;
    $sys_food_confidence = 0;
    $sys_ingredients_missing = [];

    $usr_food_id = 0;
    $usr_ingredients_missing = [];

    //data
    $apid = (array)json_decode($row->rbf_api, true);

    $founds = $api_core->sys_ingredients_found($apid['predictions']);
    $sys_food_predicts = $api_core->sys_predict_foods_by_ingredients($founds);
    $sys_food_predict = $api_core->sys_predict_foods_by_ingredients($founds, true);
    if (count($sys_food_predict)) {
      $food_predict = Food::find($sys_food_predict['food']);
      if ($food_predict) {
        $sys_ingredients_missing = $food_predict->missing_ingredients($founds);
      }
    }

    if ($row->get_food()) {

      $food_photo = $row->get_food()->get_photo();

      $rbf_food = Food::find($row->rbf_predict);
      if ($rbf_food) {
        $rbf_food_id = $rbf_food->id;
        $rbf_food_name = $rbf_food->name;
        $rbf_food_confidence = $row->rbf_confidence;

        $rbf_ingredients_missing = $rbf_food->missing_ingredients($founds);
      }

      $sys_food = Food::find($row->sys_predict);
      if ($sys_food) {
        $sys_food_id = $sys_food->id;
        $sys_food_name = $sys_food->name;
        $sys_food_confidence = $row->sys_confidence;

        $sys_ingredients_missing = $sys_food->missing_ingredients($founds);
      }

      $usr_food = Food::find($row->usr_predict);
      if ($usr_food) {
        $usr_food_id = $usr_food->id;

        $usr_ingredients_missing = $row->get_ingredients_missing();
      }
    }

    if (count($founds)) {
      foreach ($founds as $temp) {
        $ing = Ingredient::find((int)$temp['id']);
        if ($ing) {
          $rbf_ingredients_found[] = [
            'quantity' => $temp['quantity'],
            'title' => !empty($ing['name_vi']) ? $ing['name'] . ' - ' . $ing['name_vi'] : $ing['name'],
          ];
        }
      }
    }

    $data = [
      'food' => [
        'photo' => $food_photo,
      ],

      'rbf' => [
        'food_id' => $rbf_food_id,
        'food_name' => $rbf_food_name,
        'food_confidence' => $rbf_food_confidence,

        'ingredients_found' => $rbf_ingredients_found,
        'ingredients_missing' => $rbf_ingredients_missing,
      ],
      'sys' => [
        'food_id' => $sys_food_id,
        'food_name' => $sys_food_name,
        'food_confidence' => $sys_food_confidence,

        'foods' => $sys_food_predicts,
        'predict' => $sys_food_predict,
        'ingredients_missing' => $sys_ingredients_missing,
      ],
      'usr' => [
        'food_id' => $usr_food_id,

        'ingredients_missing' => $usr_ingredients_missing,
      ],
    ];

    //info
    $html_info = view('tastevn.htmls.item_food_scan_info')
      ->with('item', $row)
      ->with('data', $data)
      ->with('comments', $row->get_comments())
      ->with('texts', $row->get_texts(['text_only' => 1]))
      ->render();

    return response()->json([
      'item' => $row,
      'restaurant' => $row->get_restaurant(),
      'data' => $data,
      'html_info' => $html_info,

      'status' => true,
    ], 200);
  }

  public function food_scan_get(Request $request)
  {
    $values = $request->all();
    $api_core = new SysCore();

    $validator = Validator::make($values, [
      'item' => 'required',
    ]);
    if ($validator->fails()) {
      return response()->json($validator->errors(), 422);
    }
    //invalid
    $row = RestaurantFoodScan::findOrFail((int)$values['item']);
    if (!$row) {
      return response()->json([
        'error' => 'Invalid item'
      ], 422);
    }

    $ingredients_missing = [];
    if ($row->get_food()) {
      $ingredients_missing = $row->get_ingredients_missing();
    }

    $texts = Text::where('deleted', 0)
      ->orderByRaw('TRIM(LOWER(name))')
      ->get();

    $text_ids = []; //$row->get_texts(['id_only' => 1])

    //info
    $html_info = view('tastevn.htmls.item_food_scan_get')
      ->with('item', $row)
      ->with('ingredients', $ingredients_missing)
      ->with('texts', $texts)
      ->with('text_ids', $text_ids)
      ->render();

    return response()->json([
      'html_info' => $html_info,

      'status' => true,
    ], 200);
  }

  public function food_scan_update(Request $request)
  {
    $values = $request->all();

    $validator = Validator::make($values, [
      'item' => 'required',
    ]);
    if ($validator->fails()) {
      return response()->json($validator->errors(), 422);
    }
    //invalid
    $row = RestaurantFoodScan::findOrFail((int)$values['item']);
    if (!$row) {
      return response()->json([
        'error' => 'Invalid item'
      ], 422);
    }

    $noted = isset($values['note']) ? $values['note'] : NULL;
    $texts = isset($values['texts']) && count($values['texts']) ? (array)$values['texts'] : [];
    $ingredients_missing = isset($values['missings']) && count($values['missings']) ? (array)$values['missings'] : [];

    $item_old = $row->toArray();

    if (isset($values['food'])) {
      $food = Food::find((int)$values['food']);
      if ($food) {

        if ($food->id != $row->food_id) {
          $row->update([
            'food_id' => $food->id,
          ]);
        }

        $row->update([
          'usr_predict' => $food->id,
          'found_by' => 'usr',
          'status' => 'edited',
          'confidence' => 0,
          'food_category_id' => $row->get_restaurant()->get_food_category($food)
            ? $row->get_restaurant()->get_food_category($food)->id : 0,
        ]);

        $ingredients_missing = [];
        $ingredients = isset($values['missings']) ? (array)$values['missings'] : [];
        if (count($ingredients)) {
          foreach ($ingredients as $ing) {
            $ing = (array)$ing;
            $ingredient = Ingredient::find($ing['id']);

            $ingredients_missing[] = [
              'id' => $ing['id'],
              'quantity' => $ing['quantity'],
              'type' => $ing['type'],
              'name' => $ingredient->name,
              'name_vi' => $ingredient->name_vi,
            ];
          }
        }
        $row->add_ingredients_missing($ingredients_missing, false);

      }
    }

    $row = RestaurantFoodScan::find($row->id);
    $item_new = $row->toArray();

    if (!empty($row->usr_edited)) {
      $edited = (array)json_decode($row->usr_edited);
      $edited = [
        'before' => $edited['before'],
        'after' => $item_new,
      ];
    } else {
      $edited = [
        'before' => $item_old,
        'after' => $item_new,
      ];
    }

    $row->update([
      'note' => $noted,
      'usr_edited' => json_encode($edited),
    ]);

    RestaurantFoodScanText::where('restaurant_food_scan_id', $row->id)
      ->delete();
    if (count($texts)) {
      foreach ($texts as $text) {
        RestaurantFoodScanText::create([
          'restaurant_food_scan_id' => $row->id,
          'text_id' => (int)$text,
        ]);
      }
    }

    return response()->json([
      'item' => $row,

      'status' => true,
    ], 200);
  }

  public function food_scan_error(Request $request)
  {
    $values = $request->all();
    $api_core = new SysCore();

    $validator = Validator::make($values, [
      'item' => 'required',
    ]);
    if ($validator->fails()) {
      return response()->json($validator->errors(), 422);
    }
    //invalid
    $row = Restaurant::findOrFail((int)$values['item']);
    if (!$row) {
      return response()->json([
        'error' => 'Invalid item'
      ], 422);
    }

    $food = Food::findOrFail((int)$values['food']);
    if (!$food) {
      return response()->json([
        'error' => 'Invalid item'
      ], 422);
    }

    $time_upload = isset($values['time_upload']) && !empty($values['time_upload']) ? $values['time_upload'] : NULL;
    $time_scan = isset($values['time_scan']) && !empty($values['time_scan']) ? $values['time_scan'] : NULL;

    $select = RestaurantFoodScan::select('id', 'photo_url')
      ->distinct()
      ->where('restaurant_id', $row->id)
      ->where('food_id', $food->id)
      ->where('deleted', 0)
      ->where('missing_ids', '<>', NULL)
      ->where('missing_ids', '=', $values['missing_ids']);

    if (!empty($time_scan)) {
      $times = $api_core->parse_date_range($time_scan);
      if (!empty($times['time_from'])) {
        $select->where('time_scan', '>=', $times['time_from']);
      }
      if (!empty($times['time_to'])) {
        $select->where('time_scan', '<=', $times['time_to']);
      }
    }
    if (!empty($time_upload)) {
      $times = $api_core->parse_date_range($time_upload);
      if (!empty($times['time_from'])) {
        $select->where('time_photo', '>=', $times['time_from']);
      }
      if (!empty($times['time_to'])) {
        $select->where('time_photo', '<=', $times['time_to']);
      }
    }

    //info
    $html_info = view('tastevn.htmls.item_food_scan_error')
      ->with('restaurant', $row)
      ->with('food', $food)
      ->with('rows', $select->get())
      ->render();

    return response()->json([
      'restaurant' => $row,
      'html_info' => $html_info,

      'status' => true,
    ], 200);
  }

  public function food_scan_delete(Request $request)
  {
    $values = $request->all();
//    echo '<pre>';var_dump($values);die;
    //required
    $validator = Validator::make($values, [
      'item' => 'required',
    ]);
    if ($validator->fails()) {
      return response()->json($validator->errors(), 422);
    }

    $row = RestaurantFoodScan::findOrFail((int)$values['item']);
    if (!$row) {
      return response()->json([
        'error' => 'Invalid item'
      ], 422);
    }

    $row->update([
      'deleted' => Auth::user() ? Auth::user()->id : 0,
    ]);

    return response()->json([
      'status' => true,
      'item' => $row->id,
    ], 200);
  }

  public function stats(Request $request)
  {
    $values = $request->all();

    $validator = Validator::make($values, [
      'item' => 'required',
    ]);
    if ($validator->fails()) {
      return response()->json($validator->errors(), 422);
    }
    //invalid
    $row = Restaurant::findOrFail((int)$values['item']);
    if (!$row) {
      return response()->json([
        'error' => 'Invalid item'
      ], 422);
    }

    $type = isset($values['type']) ? $values['type'] : 'total';
    $times = isset($values['times']) ? $values['times'] : NULL;

    return response()->json([
      'stats' => $row->get_stats($type, $times),

      'status' => true,
    ], 200);
  }

}
