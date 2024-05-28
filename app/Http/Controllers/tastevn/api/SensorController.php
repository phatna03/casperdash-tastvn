<?php

namespace App\Http\Controllers\tastevn\api;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

use Validator;
use App\Api\SysCore;
use App\Excel\ImportData;

use Illuminate\Support\Facades\Notification;
use App\Notifications\IngredientMissing;

use App\Models\Restaurant;
use App\Models\RestaurantParent;
use App\Models\RestaurantFood;
use App\Models\RestaurantFoodScan;
use App\Models\Food;
use App\Models\FoodCategory;
use App\Models\Ingredient;
use App\Models\Text;
use App\Models\RestaurantFoodScanText;
use App\Models\RestaurantFoodScanMissing;

class SensorController extends Controller
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
    $invalid_roles = ['user'];
    if (in_array($user->role, $invalid_roles)) {
      return redirect('admin/photos');
    }

    $pageConfigs = [
      'myLayout' => 'horizontal',
      'hasCustomizer' => false,
    ];

    $user->add_log([
      'type' => 'view_listing_sensor',
    ]);

    return view('tastevn.pages.restaurants', ['pageConfigs' => $pageConfigs]);
  }

  public function store(Request $request)
  {
    $values = $request->post();
    $user = Auth::user();
    //required
    $validator = Validator::make($values, [
      'name' => 'required|string',
      'restaurant_parent_id' => 'required',
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
      'restaurant_parent_id' => (int)$values['restaurant_parent_id'],
      'name' => ucwords(trim($values['name'])),
      's3_bucket_name' => isset($values['s3_bucket_name']) ? trim($values['s3_bucket_name']) : '',
      's3_bucket_address' => isset($values['s3_bucket_address']) ? trim($values['s3_bucket_address']) : '',
      'rbf_scan' => isset($values['rbf_scan']) && (int)$values['rbf_scan'] ? 1 : 0,
      'creator_id' => $user->id,
    ]);

    $row->on_create_after();

    $user->add_log([
      'type' => 'add_' . $row->get_type(),
      'restaurant_id' => (int)$row->id,
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
      'restaurant_parent_id' => 'required',
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

    $diffs['before'] = $row->get_log();

    $row->update([
      'restaurant_parent_id' => (int)$values['restaurant_parent_id'],
      'name' => trim($values['name']),
      's3_bucket_name' => isset($values['s3_bucket_name']) ? trim($values['s3_bucket_name']) : '',
      's3_bucket_address' => isset($values['s3_bucket_address']) ? trim($values['s3_bucket_address']) : '',
      'rbf_scan' => isset($values['rbf_scan']) && (int)$values['rbf_scan'] ? 1 : 0,
    ]);

    $row->on_update_after();

    $row = Restaurant::find($row->id);
    $diffs['after'] = $row->get_log();
    if (json_encode($diffs['before']) !== json_encode($diffs['after'])) {
      $user->add_log([
        'type' => 'edit_' . $row->get_type(),
        'restaurant_id' => (int)$row->id,
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

    $row = Restaurant::findOrFail((int)$values['item']);
    if (!$row) {
      return response()->json([
        'error' => 'Invalid item'
      ], 422);
    }

    $row->update([
      'deleted' => $user->id,
    ]);

    $row->on_delete_after();

    $user->add_log([
      'type' => 'delete_' . $row->get_type(),
      'restaurant_id' => (int)$row->id,
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

    $row->on_restore_after();

    $user->add_log([
      'type' => 'restore_' . $row->get_type(),
      'restaurant_id' => (int)$row->id,
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
    $user = Auth::user();
    $values = $request->post();
    $keyword = isset($values['keyword']) && !empty($values['keyword']) ? $values['keyword'] : NULL;

    $select = Restaurant::select('id', 'name');

    //tester
    if ($user && $user->id == 5) {

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

  public function show(string $id, Request $request)
  {
    $values = $request->all();
    $user = Auth::user();
    $invalid_roles = ['user'];
    if (in_array($user->role, $invalid_roles)) {
      return redirect('page_not_found');
    }

    $row = Restaurant::find((int)$id);
    if (!$row || $row->deleted) {
      if ($user->id == 5) {

      } else {
        return redirect('page_not_found');
      }
    }

    if (!$user->can_access_restaurant($row)) {
      return redirect('page_not_found');
    }

    //search
    $debug = isset($values['debug']) ? (int)$values['debug'] : 0;


    $pageConfigs = [
      'myLayout' => 'horizontal',
      'hasCustomizer' => false,

      'item' => $row,

      'debug' => $debug,
    ];

    $user->add_log([
      'type' => 'view_item_' . $row->get_type(),
      'restaurant_id' => (int)$row->id,
      'item_id' => (int)$row->id,
      'item_type' => $row->get_type(),
    ]);

    return view('tastevn.pages.restaurant_info', ['pageConfigs' => $pageConfigs]);
  }

  public function food_scan_delete(Request $request)
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

    $row = RestaurantFoodScan::findOrFail((int)$values['item']);
    if (!$row) {
      return response()->json([
        'error' => 'Invalid item'
      ], 422);
    }

    $row->update([
      'deleted' => $user->id,
    ]);

    return response()->json([
      'status' => true,
      'item' => $row->id,
    ], 200);
  }

  public function food_scan_api(Request $request)
  {
    $values = $request->post();
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

    $type = isset($values['type']) ? (int)$values['type'] : 1;

    if ($type == 1) {
      //re-predict
      $row->predict_reset();
      $row->predict_food([
        'notification' => false,
      ]);
    } else {
      //re-call
      $api_core->v3_photo_scan($row);
    }

    return response()->json([
      'status' => true,
    ], 200);
  }

  public function food_scan_info(Request $request)
  {
    $values = $request->post();
    $user = Auth::user();
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

    $restaurant = $row->get_restaurant();

    $food_photo = url('custom/img/no_photo.png');
    $food_ingredients = [];
    $food_recipes = [];
    $food_name = NULL;

    $rbf_food_id = 0;
    $rbf_food_name = NULL;
    $rbf_food_confidence = 0;
    $rbf_ingredients_found = [];
    $rbf_ingredients_missing = [];

    $sys_food_id = 0;
    $sys_food_name = NULL;
    $sys_food_confidence = 0;
    $sys_ingredients_missing = [];
    $sys_food_predict = [];
    $sys_food_predicts = [];

    $usr_food_id = 0;
    $usr_ingredients_missing = [];

    //data
    $apid = (array)json_decode($row->rbf_api, true);
    if (count($apid)) {

      $founds = $api_core->sys_ingredients_found($apid['predictions']);
      $sys_food_predicts = $api_core->sys_predict_foods_by_ingredients($founds);
      $sys_food_predict = $api_core->sys_predict_foods_by_ingredients($founds, true);
      if (count($sys_food_predict)) {
        $food_predict = Food::find($sys_food_predict['food']);
        if ($food_predict) {
          $sys_ingredients_missing = $food_predict->missing_ingredients([
            'restaurant_parent_id' => $restaurant->restaurant_parent_id,
            'ingredients' => $founds,
          ]);
        }
      }
      if ($row->get_food()) {

        $food_name = $row->get_food()->name;
        $food_ingredients = $row->get_food()->get_ingredients();
        $food_recipes = $row->get_food()->get_recipes();

        $restaurant_ids = Restaurant::where('deleted', 0)
          ->select('id')
          ->where('restaurant_parent_id', $restaurant->restaurant_parent_id);

        $restaurant_food = RestaurantFood::where('deleted', 0)
          ->whereIn('restaurant_id', $restaurant_ids)
          ->where('food_id', $row->get_food()->id)
          ->where('photo', '<>', NULL)
          ->orderBy('updated_at', 'desc')
          ->limit(1)
          ->first();
        $food_photo = $restaurant_food ? $restaurant_food->photo : $food_photo;

        $rbf_food = Food::find($row->rbf_predict);
        if ($rbf_food) {
          $rbf_food_id = $rbf_food->id;
          $rbf_food_name = $rbf_food->name;
          $rbf_food_confidence = $row->rbf_confidence;

          $rbf_ingredients_missing = $rbf_food->missing_ingredients([
            'restaurant_parent_id' => $restaurant->restaurant_parent_id,
            'ingredients' => $founds,
          ]);
        }

        $sys_food = Food::find($row->sys_predict);
        if ($sys_food) {
          $sys_food_id = $sys_food->id;
          $sys_food_name = $sys_food->name;
          $sys_food_confidence = $row->sys_confidence;

          $sys_ingredients_missing = $sys_food->missing_ingredients([
            'restaurant_parent_id' => $restaurant->restaurant_parent_id,
            'ingredients' => $founds,
          ]);
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
    }

    //v2
    $data2s = $this->kitchen_food_datas($row);
    if (count($data2s) && count($data2s['ingredients_found'])) {

      //rbf
      if ($data2s['food_id']) {
        $rbf_food_id = $data2s['food_id'];
        $food_photo = $data2s['food_photo'];

        $rbf_food_name = $row->get_food_rbf() ? $row->get_food_rbf()->name : $row->get_food()->name;
        $rbf_food_confidence = $row->rbf_confidence;
      }

      $rbf_ingredients_found = [];
      foreach ($data2s['ingredients_found'] as $ing) {
        $rbf_ingredients_found[] = [
          'quantity' => $ing['quantity'],
          'title' => $ing['name'],
        ];
      }

      if (count($data2s['ingredients_missing'])) {
        $rbf_ingredients_missing = $data2s['ingredients_missing'];
      }

      //usr
      $usr_food = Food::find($row->usr_predict);
      if ($usr_food) {
        $usr_food_id = $usr_food->id;

        $usr_ingredients_missing = $row->get_ingredients_missing();
      }
    }

    $data = [
      'food' => [
        'name' => $food_name,
        'photo' => $food_photo,
        'ingredients' => $food_ingredients,
        'recipes' => $food_recipes,
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
      ->with('texts', $row->get_texts(['text_name_only' => 1]))
      ->render();

    $user->add_log([
      'type' => 'view_item_' . $row->get_type(),
      'restaurant_id' => (int)$row->restaurant_id,
      'item_id' => (int)$row->id,
      'item_type' => $row->get_type(),
    ]);

    return response()->json([
      'item' => $row,
      'restaurant' => $restaurant,
      'data' => $data,
      'html_info' => $html_info,

      'status' => true,
    ], 200);
  }

  public function food_scan_error(Request $request)
  {
    $values = $request->post();
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

    $select = RestaurantFoodScan::select('id', 'photo_url', 'photo_name', 'local_storage')
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

  public function food_scan_update(Request $request)
  {
    $values = $request->post();
    $user = Auth::user();

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
    $unknown = true;

    $diffs['before'] = $row->get_log();

    $item_old = $row->toArray();

    if (isset($values['food'])) {
      $food = Food::find((int)$values['food']);
      if ($food) {
        $unknown = false;

        if ($food->id != $row->food_id) {
          $row->update([
            'food_id' => $food->id,
          ]);
        }

        $row->update([
          'usr_predict' => $food->id,
          'found_by' => 'usr',
          'status' => 'edited',
          'confidence' => 100,
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
        $row->add_ingredients_missing($food, $ingredients_missing, false);

      }
    }

    if ($unknown) {
      $row->update([
        'food_id' => 0,
        'usr_predict' => 0,
        'found_by' => 'usr',
        'status' => 'edited',
        'confidence' => 0,
        'food_category_id' => 0,
      ]);

      RestaurantFoodScanMissing::where('restaurant_food_scan_id', $row->id)
        ->delete();
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

    $row->update_text_notes();

    $row = RestaurantFoodScan::find($row->id);
    $diffs['after'] = $row->get_log();
    if (json_encode($diffs['before']) !== json_encode($diffs['after'])) {
      $user->add_log([
        'type' => 'edit_result',
        'restaurant_id' => (int)$row->restaurant_id,
        'item_id' => (int)$row->id,
        'item_type' => $row->get_type(),
        'params' => json_encode($diffs),
      ]);
    }

    return response()->json([
      'item' => $row,

      'status' => true,
    ], 200);
  }

  public function food_scan_get_food(Request $request)
  {
    $values = $request->all();
    $user = Auth::user();

    $validator = Validator::make($values, [
      'rfs' => 'required',
      'food' => 'required',
    ]);
    if ($validator->fails()) {
      return response()->json($validator->errors(), 422);
    }
    //invalid
    $rfs = RestaurantFoodScan::findOrFail((int)$values['rfs']);
    $food = Food::findOrFail((int)$values['food']);
    if (!$rfs || !$food) {
      return response()->json([
        'error' => 'Invalid item'
      ], 422);
    }

    //scan update
    $html = view('tastevn.htmls.item_ingredient_select')
      ->with('ingredients', $food->get_ingredients([
        'restaurant_parent_id' => $rfs->get_restaurant()->restaurant_parent_id
      ]))
      ->render();

    return response()->json([
      'html' => $html,
    ]);
  }

  public function kitchen(string $id)
  {
    $user = Auth::user();

    $row = Restaurant::find((int)$id);
    if (!$row || $row->deleted) {
      if ($user->id == 5) {

      } else {
        return redirect('page_not_found');
      }
    }

    $pageConfigs = [
      'myLayout' => 'horizontal',
      'hasCustomizer' => false,

      'item' => $row,
    ];

    return view('tastevn.pages.dashboard_kitchen', ['pageConfigs' => $pageConfigs]);
  }

  public function kitchen_checker(Request $request)
  {
    $api_core = new SysCore();
    $values = $request->post();

    $validator = Validator::make($values, [
      'item' => 'required',
    ]);
    if ($validator->fails()) {
      return response()->json($validator->errors(), 422);
    }
    //invalid
    $restaurant = Restaurant::findOrFail((int)$values['item']);
    if (!$restaurant) {
      return response()->json([
        'error' => 'Invalid item'
      ], 422);
    }

    $cur_date = date('Y-m-d');
    $cur_hour = (int)date('H');

    $row = NULL;

    $folder_setting = $api_core->parse_s3_bucket_address($restaurant->s3_bucket_address);
    $directory = $folder_setting . '/' . $cur_date . '/' . $cur_hour . '/';

    $files = Storage::disk('sensors')->files($directory);
    if (count($files)) {
      //desc
      $files = array_reverse($files);

      //step 1= photo check
      foreach ($files as $file) {
        $ext = array_filter(explode('.', $file));
        if (!count($ext) || $ext[count($ext) - 1] != 'jpg') {
          continue;
        }

        //check exist
        $row = RestaurantFoodScan::where('restaurant_id', $restaurant->id)
          ->where('photo_name', $file)
          ->first();
        if (!$row) {

          $row = $restaurant->photo_save([
            'local_storage' => 1,
            'photo_url' => NULL,
            'photo_name' => $file,
            'photo_ext' => 'jpg',
            'time_photo' => date('Y-m-d H:i:s'),
          ]);
        }

        //get 1 latest file
        break;
      }
    }

    return response()->json([
      'status' => $row ? $row->status : 'no_photo',

      'file' => $row ? $row->photo_name : '',
      'file_url' => $row ? $row->get_photo() : '',
      'file_id' => $row ? $row->id : 0,

      'datas' => $row ? $this->kitchen_food_datas($row) : [],
    ]);
  }

  public function kitchen_predict(Request $request)
  {
    $api_core = new SysCore();
    $values = $request->post();

    $user = Auth::user();

    $validator = Validator::make($values, [
      'item' => 'required',
      'restaurant_id' => 'required',
    ]);
    if ($validator->fails()) {
      return response()->json($validator->errors(), 422);
    }
    //invalid
    $row = RestaurantFoodScan::findOrFail((int)$values['item']);
    $restaurant = Restaurant::findOrFail((int)$values['restaurant_id']);
    if (!$restaurant || !$row) {
      return response()->json([
        'error' => 'Invalid item'
      ], 422);
    }

    $type = isset($values['type']) ? $values['type'] : NULL;

    switch ($type) {
      case 'api':

        if ($row->status == 'new') {
          $restaurant->photo_scan($row);
        }

        break;

      default:

        $datas = isset($values['datas']) ? (array)$values['datas'] : [];
        if (count($datas) && $row->status == 'new') {
          //step 2= photo scan
          $row->update([
            'time_scan' => date('Y-m-d H:i:s'),
            'status' => count($datas) ? 'scanned' : 'failed',
            'rbf_api_js' => count($datas) ? json_encode($datas) : NULL,
          ]);

          //step 3= photo predict
          $row->predict_food();
        }
    }

    //refresh
    $row = RestaurantFoodScan::find($row->id);

    //notify
    $notifys = [];
    $notify_ids = [];
    $valid_types = [
      //force
      'App\Notifications\IngredientMissing'
    ];

    //speaker
    $text_to_speak = '';
    $text_to_speech = false;
    if ((int)$user->get_setting('missing_ingredient_alert_speaker')) {
      $text_to_speech = true;
    }

    //printer
    $printer = false;
    if ((int)$user->get_setting('missing_ingredient_alert_printer')) {
      $printer = true;
    }

    //notify
    if (!empty($row->missing_texts)) {

      $ingredients = array_filter(explode('&nbsp', $row->missing_texts));
      if (count($ingredients)) {

        $notifys[] = [
          'itd' => $row->id,
          'photo_url' => $row->get_photo(),
          'restaurant_name' => $row->get_restaurant()->name,
          'food_name' => $row->get_food()->name,
          'food_confidence' => $row->confidence,
          'ingredients' => $ingredients,
        ];

        $notify_ids[] = $row->id;

        if ($text_to_speech) {

          $text_ingredients_missing = '';
          foreach ($row->get_ingredients_missing() as $ing) {
            $text_ingredients_missing .= $ing['ingredient_quantity'] . ' ' . $ing['name'] . ', ';
          }

          $text_to_speak = '[alert], '
            . $row->get_restaurant()->name
//            . ' occurred at ' . date('H:i')
//            . ", Predicted Dish, "
//            . ", " . $row->get_food()->name
            . ", Ingredients Missing, "
            . $text_ingredients_missing;

          $api_core->s3_polly([
            'text_to_speak' => $text_to_speak,
            'text_rate' => 'slow',
          ]);
        }
      }
    }

    return response()->json([
      'status' => true,

      //data
      'food_id' => $row->get_food() ? $row->get_food()->id : 0,
      'datas' => $this->kitchen_food_datas($row),
      //notify
      'notifys' => $notifys,
      'notify_ids' => $notify_ids,
      'speaker' => $text_to_speech && !empty($text_to_speak),
      'speaker_text' => $text_to_speak,
      'printer' => $printer,
    ]);
  }

  protected function kitchen_food_datas(RestaurantFoodScan $row)
  {
    if (!$row) {
      return [];
    }

    $restaurant = $row->get_restaurant();
    $food = $row->get_food() ? $row->get_food() : NULL;

    $result1s = (array)json_decode($row->rbf_api, true);
    $result2s = (array)json_decode($row->rbf_api_js, true);
    $predictions = count($result1s) ? (array)$result1s['predictions'] : [];
    if (!count($predictions) && count($result2s)) {
      $predictions = $result2s;
    }

    $ingredients_found = [];
    $ingredients_missing = [];

    $html_info = '';
    $food_id = 0;
    $food_name = '';
    $food_photo = '';

    if ($food) {

      $food_id = $food->id;
      $food_name = '[' . $restaurant->get_parent()->name . '] ' . $food->name;

      //photo standard
      $restaurant_ids = Restaurant::where('deleted', 0)
        ->select('id')
        ->where('restaurant_parent_id', $restaurant->restaurant_parent_id);

      $restaurant_food = RestaurantFood::where('deleted', 0)
        ->whereIn('restaurant_id', $restaurant_ids)
        ->where('food_id', $food->id)
        ->where('photo', '<>', NULL)
        ->orderBy('updated_at', 'desc')
        ->limit(1)
        ->first();
      $food_photo = $restaurant_food ? $restaurant_food->photo : url('custom/img/no_photo.png');

      //info recipe
      $html_info = view('tastevn.htmls.item_food_dashboard')
        ->with('recipes', $food->get_recipes([
          'restaurant_parent_id' => $restaurant->restaurant_parent_id,
        ]))
        ->render();

      //ingredient missing
      $ids = [];
      $temps = $row->get_ingredients_missing();
      if (count($temps)) {
        foreach ($temps as $ing) {
          $ingredients_missing[] = [
            'id' => $ing->id,
            'quantity' => $ing->ingredient_quantity,
            'name' => $ing->name,
            'name_vi' => $ing->name_vi,
            'type' => $ing->ingredient_type,
          ];

          $ids[] = $ing->id;
        }
      }

      //ingredient found
      $temps = $food->get_ingredients([
        'restaurant_parent_id' => $restaurant->restaurant_parent_id,
      ]);
      if (count($temps)) {
        foreach ($temps as $ing) {
          if (count($ids) && in_array($ing->id, $ids)) {

            if ($ing->ingredient_quantity > 1) {

              $quantity = 0;
              if (count($ingredients_missing)) {
                foreach ($ingredients_missing as $missing) {
                  if ($missing['id'] == $ing->id) {
                    $quantity = $missing['quantity'];
                    break;
                  }
                }
              }

              if ($ing->ingredient_quantity - $quantity) {
                $ingredients_found[] = [
                  'id' => $ing->id,
                  'quantity' => $ing->ingredient_quantity - $quantity,
                  'name' => $ing->name,
                  'name_vi' => $ing->name_vi,
                  'type' => $ing->ingredient_type,
                ];
              }
            }

            continue;
          }

          $ingredients_found[] = [
            'id' => $ing->id,
            'quantity' => $ing->ingredient_quantity,
            'name' => $ing->name,
            'name_vi' => $ing->name_vi,
            'type' => $ing->ingredient_type,
          ];
        }
      }

    }

    return [
      'food_id' => $food_id,
      'food_photo' => $food_photo,
      'food_name' => $food_name,

      'html_info' => $html_info,

      'ingredients_missing' => $ingredients_missing,
      'ingredients_found' => $ingredients_found,
    ];
  }

//  optimized


  public function selectize_parent(Request $request)
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

  public function food_import(Request $request)
  {
    $values = $request->post();
    $restaurant_id = isset($values['restaurant_id']) ? (int)$values['restaurant_id'] : 0;
    $restaurant = Restaurant::find($restaurant_id);

    $datas = (new ImportData())->toArray($request->file('excel'));
    if (!count($datas) || !count($datas[0]) || !$restaurant) {
      return response()->json([
        'error' => 'Invalid data'
      ], 404);
    }

    $user = Auth::user();
    $faileds = [];
    $temp_count = 0;
    $temps = [];
    $food_count = 0;

    DB::beginTransaction();
    try {

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
                'restaurant_id' => $restaurant->id,
                'creator_id' => $user->id,
              ]);
            }
          }

          $row = RestaurantFood::where('restaurant_id', $restaurant->id)
            ->where('food_id', $food->id)
            ->first();
          if (!$row) {
            $row = RestaurantFood::create([
              'restaurant_id' => $restaurant->id,
              'food_id' => $food->id,
              'creator_id' => $user->id,
            ]);
          }

          $food_count++;

          $row->update([
            'food_category_id' => $food_category ? $food_category->id : 0,
            'photo' => !empty($temp['photo']) && @getimagesize($temp['photo']) ? $temp['photo'] : NULL,
          ]);

//          $user->add_log([
//            'type' => 'import_food_to_restaurant' . $row->get_type(),
//            'item_id' => (int)$row->id,
//            'item_type' => $row->get_type(),
//          ]);
        }
      }

      $restaurant->count_foods();

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

  public function food_add(Request $request)
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

    //check later
    return response()->json([
      'error' => 'Invalid restaurant'
    ], 422);

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

    $user->add_log([
      'type' => 'add_restaurant_dish',
      'restaurant_id' => (int)$row->id,
      'params' => json_encode([
        'category' => $category,
        'foods' => $foods,
      ])
    ]);

    return response()->json([
      'status' => true,
      'item' => $row->name,
    ], 200);
  }

  public function food_delete(Request $request)
  {
    $values = $request->post();
    $user = Auth::user();
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

    $user->add_log([
      'type' => 'delete_restaurant_dish',
      'restaurant_id' => (int)$row->id,
      'params' => json_encode([
        'food' => $food->id,
      ])
    ]);

    return response()->json([
      'status' => true,
      'item' => $row->name,
    ], 200);
  }

  public function food_scan(Request $request)
  {
    $values = $request->post();
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

  public function food_scan_get(Request $request)
  {
    $values = $request->post();
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
      ->orderByRaw('TRIM(LOWER(name)) + 0')
      ->get();

    $text_ids = [];
    $arr = $row->get_texts(['text_id_only' => 1]);
    if (count($arr)) {
      $text_ids = $arr->toArray();
      $text_ids = array_map('current', $text_ids);
    }

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

  public function stats(Request $request)
  {
    $values = $request->post();

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
