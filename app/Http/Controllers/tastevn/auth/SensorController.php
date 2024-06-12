<?php

namespace App\Http\Controllers\tastevn\auth;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
//lib
use Validator;
use App\Api\SysApp;
use App\Api\SysRobo;
use App\Excel\ImportData;
//model
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
      'type' => 'view_listing_sensor',
    ]);

    return view('tastevn.pages.restaurants', ['pageConfigs' => $pageConfigs]);
  }

  public function store(Request $request)
  {
    $values = $request->post();
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
      'creator_id' => $this->_viewer->id,
    ]);

    $row->on_create_after();

    $this->_viewer->add_log([
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
    $row = Restaurant::find((int)$values['item']);
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
      $this->_viewer->add_log([
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
    //required
    $validator = Validator::make($values, [
      'item' => 'required',
    ]);
    if ($validator->fails()) {
      return response()->json($validator->errors(), 422);
    }

    $row = Restaurant::find((int)$values['item']);
    if (!$row) {
      return response()->json([
        'error' => 'Invalid item'
      ], 422);
    }

    $row->update([
      'deleted' => $this->_viewer->id,
    ]);

    $row->on_delete_after();

    $this->_viewer->add_log([
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

    $this->_viewer->add_log([
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
    $values = $request->post();
    $keyword = isset($values['keyword']) && !empty($values['keyword']) ? $values['keyword'] : NULL;

    $select = Restaurant::select('id', 'name');

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

  public function show(string $id, Request $request)
  {
    $values = $request->all();

    $invalid_roles = ['user'];
    if (in_array($this->_viewer->role, $invalid_roles)) {
      return redirect('error/404');
    }

    $row = Restaurant::find((int)$id);
    if (!$row || $row->deleted) {
      if ($this->_viewer->is_dev()) {

      } else {
        return redirect('error/404');
      }
    }

    if (!$this->_viewer->can_access_restaurant($row)) {
      return redirect('error/404');
    }

    //search
    $debug = isset($values['debug']) ? (int)$values['debug'] : 0;


    $pageConfigs = [
      'myLayout' => 'horizontal',
      'hasCustomizer' => false,

      'item' => $row,

      'debug' => $debug,
    ];

    $this->_viewer->add_log([
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

    //required
    $validator = Validator::make($values, [
      'item' => 'required',
    ]);
    if ($validator->fails()) {
      return response()->json($validator->errors(), 422);
    }

    $row = RestaurantFoodScan::find((int)$values['item']);
    if (!$row) {
      return response()->json([
        'error' => 'Invalid item'
      ], 422);
    }

    $row->update([
      'deleted' => $this->_viewer->id,
    ]);

    return response()->json([
      'status' => true,
      'item' => $row->id,
    ], 200);
  }

  public function food_scan_api(Request $request)
  {
    $values = $request->post();

    $validator = Validator::make($values, [
      'item' => 'required',
    ]);
    if ($validator->fails()) {
      return response()->json($validator->errors(), 422);
    }
    //invalid
    $row = RestaurantFoodScan::find((int)$values['item']);
    if (!$row) {
      return response()->json([
        'error' => 'Invalid item'
      ], 422);
    }

    $type = isset($values['type']) ? (int)$values['type'] : 1;

    if ($type == 1) {
      //re-predict
      $row->predict_food([
        'notification' => false,
      ]);
    } else {
      //re-check
      $img_url = $row->get_photo();
      if (App::environment() == 'local') {
        $img_url = "http://ai.block8910.com/sensors/58-5b-69-19-ad-83/SENSOR/1/2024-05-29/12/SENSOR_2024-05-29-12-13-05-742_145.jpg";
      }

      //step 2= photo scan
      $datas = SysRobo::photo_scan($img_url, [
        'confidence' => SysRobo::_SCAN_CONFIDENCE,
        'overlap' => SysRobo::_SCAN_OVERLAP,
      ]);

      $row->update([
        'time_scan' => date('Y-m-d H:i:s'),
        'status' => $datas['status'] ? 'scanned' : 'failed',
        'rbf_api' => $datas['status'] ? json_encode($datas['result']) : NULL,
      ]);

      //step 3= photo predict
      $row->predict_food([
        'notification' => false,
      ]);

    }

    return response()->json([
      'status' => true,
    ], 200);
  }

  public function food_scan_info(Request $request)
  {
    $values = $request->post();

    $validator = Validator::make($values, [
      'item' => 'required',
    ]);
    if ($validator->fails()) {
      return response()->json($validator->errors(), 422);
    }
    //invalid
    $row = RestaurantFoodScan::find((int)$values['item']);
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
    $rbf_predictions = [];

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

      $rbf_predictions = $apid['predictions'];

      $founds = SysRobo::ingredients_compact($apid['predictions']);
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

      if ($row->get_food()) {

        $food_name = $row->get_food()->name;
        $food_ingredients = $row->get_food()->get_ingredients([
          'restaurant_parent_id' => $restaurant->restaurant_parent_id,
        ]);
        $food_recipes = $row->get_food()->get_recipes([
          'restaurant_parent_id' => $restaurant->restaurant_parent_id,
        ]);

        $food_photo = $row->get_food()->get_photo([
          'restaurant_parent_id' => $restaurant->restaurant_parent_id,
        ]);

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

        'predictions' => $rbf_predictions,
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

    $this->_viewer->add_log([
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

    $validator = Validator::make($values, [
      'item' => 'required',
    ]);
    if ($validator->fails()) {
      return response()->json($validator->errors(), 422);
    }
    //invalid
    $row = Restaurant::find((int)$values['item']);
    if (!$row) {
      return response()->json([
        'error' => 'Invalid item'
      ], 422);
    }

    $food = Food::find((int)$values['food']);
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
      $times = $this->_sys_app->parse_date_range($time_scan);
      if (!empty($times['time_from'])) {
        $select->where('time_scan', '>=', $times['time_from']);
      }
      if (!empty($times['time_to'])) {
        $select->where('time_scan', '<=', $times['time_to']);
      }
    }
    if (!empty($time_upload)) {
      $times = $this->_sys_app->parse_date_range($time_upload);
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

    $validator = Validator::make($values, [
      'item' => 'required',
    ]);
    if ($validator->fails()) {
      return response()->json($validator->errors(), 422);
    }
    //invalid
    $row = RestaurantFoodScan::find((int)$values['item']);
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
      $this->_viewer->add_log([
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

    $validator = Validator::make($values, [
      'rfs' => 'required',
      'food' => 'required',
    ]);
    if ($validator->fails()) {
      return response()->json($validator->errors(), 422);
    }
    //invalid
    $rfs = RestaurantFoodScan::find((int)$values['rfs']);
    $food = Food::find((int)$values['food']);
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

  public function food_scan_get(Request $request)
  {
    $values = $request->post();

    $validator = Validator::make($values, [
      'item' => 'required',
    ]);
    if ($validator->fails()) {
      return response()->json($validator->errors(), 422);
    }
    //invalid
    $row = RestaurantFoodScan::find((int)$values['item']);
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
    $row = Restaurant::find((int)$values['item']);
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

  public function kitchen(string $id)
  {
    $row = Restaurant::find((int)$id);
    if (!$row || $row->deleted) {
      if ($this->_viewer->is_dev()) {

      } else {
        return redirect('error/404');
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
    $values = $request->post();

    $validator = Validator::make($values, [
      'item' => 'required',
    ]);
    if ($validator->fails()) {
      return response()->json($validator->errors(), 422);
    }
    //invalid
    $restaurant = Restaurant::find((int)$values['item']);
    if (!$restaurant) {
      return response()->json([
        'error' => 'Invalid item'
      ], 422);
    }

    $cur_date = date('Y-m-d');
    $cur_hour = (int)date('H');

    $row = NULL;

    $folder_setting = $this->_sys_app->parse_s3_bucket_address($restaurant->s3_bucket_address);
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

    if (!$row) {
      $row = RestaurantFoodScan::where('restaurant_id', $restaurant->id)
        ->where('deleted', 0)
        ->orderBy('id', 'desc')
        ->limit(1)
        ->first();
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
    $values = $request->post();

    $validator = Validator::make($values, [
      'item' => 'required',
      'restaurant_id' => 'required',
    ]);
    if ($validator->fails()) {
      return response()->json($validator->errors(), 422);
    }
    //invalid
    $row = RestaurantFoodScan::find((int)$values['item']);
    $restaurant = Restaurant::find((int)$values['restaurant_id']);
    if (!$restaurant || !$row) {
      return response()->json([
        'error' => 'Invalid item'
      ], 422);
    }

    $type = isset($values['type']) ? $values['type'] : NULL;

    switch ($type) {
      case 'api':

        if ($row->status == 'new') {

          $img_url = $row->get_photo();
          if (App::environment() == 'local') {
            $img_url = "http://ai.block8910.com/sensors/58-5b-69-19-ad-83/SENSOR/1/2024-05-29/12/SENSOR_2024-05-29-12-13-05-742_145.jpg";
          }

          //step 2= photo scan
          $datas = SysRobo::photo_scan($img_url, [
            'confidence' => SysRobo::_SCAN_CONFIDENCE,
            'overlap' => SysRobo::_SCAN_OVERLAP,
          ]);

          $row->update([
            'time_scan' => date('Y-m-d H:i:s'),
            'status' => $datas['status'] ? 'scanned' : 'failed',
            'rbf_api' => $datas['status'] ? json_encode($datas['result']) : NULL,
          ]);

          //step 3= photo predict
          $row->predict_food();
        }

        break;

      default:

        $datas = isset($values['datas']) ? (array)$values['datas'] : [];
        if (count($datas) && $row->status == 'new') {

          //photo result
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
    if ((int)$this->_viewer->get_setting('missing_ingredient_alert_speaker')) {
      $text_to_speech = true;
    }

    //printer
    $printer = false;
    if ((int)$this->_viewer->get_setting('missing_ingredient_alert_printer')) {
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
//            $text_ingredients_missing .= $ing['ingredient_quantity'] . ' ' . $ing['name'] . ', ';
            $text_ingredients_missing .= $ing['name'] . ', ';
          }

          $text_to_speak = '[Missing], '
            . $text_ingredients_missing
            . ', [Need to re-check]'
          ;

          $this->_sys_app->aws_s3_polly([
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
      $food_name = $food->name;
      $food_photo = $food->get_photo([
        'restaurant_parent_id' => $restaurant->restaurant_parent_id
      ]);

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
}
