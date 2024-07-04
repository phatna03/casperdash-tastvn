<?php

namespace App\Http\Controllers\tastevn\auth;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
//lib
use SebastianBergmann\Type\Exception;
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
//      return redirect('admin/photos');
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
      'rbf_scan' => 0, //isset($values['rbf_scan']) && (int)$values['rbf_scan'] ? 1 : 0,
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
      'rbf_scan' => 0, //isset($values['rbf_scan']) && (int)$values['rbf_scan'] ? 1 : 0,
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
//      return redirect('error/404');
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

    $food_datas = [];

    $datas = RestaurantFood::where('restaurant_parent_id', $row->restaurant_parent_id)
      ->where('deleted', 0)
      ->get();
    if (count($datas)) {
      foreach ($datas as $dts) {

        $food_category = FoodCategory::find($dts->food_category_id);

        $food_datas[] = [
          'food_category_id' => $food_category ? $food_category->id : 0,
          'food_category_name' => $food_category ? $food_category->name : '',
          'food_id' => $dts->food_id,
          'live_group' => $dts->live_group,
        ];
      }
    }

    $pageConfigs = [
      'myLayout' => 'horizontal',
      'hasCustomizer' => false,

      'item' => $row,
      'food_datas' => $food_datas,

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
      //step 2= photo scan
      $row->model_api_1([
        'confidence' => SysRobo::_SCAN_CONFIDENCE,
        'overlap' => SysRobo::_SCAN_OVERLAP,

        'api_recall' => true,
      ]);

      //step 3= photo predict
      $row->predict_food([
        'notification' => false,

        'api_recall' => true,
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

    //admin view

    //moderator view

    $food_photo = url('custom/img/logo_' . $restaurant->restaurant_parent_id . '.png');
    $food_ingredients = [];
    $food_recipes = [];
    $food_name = NULL;

    $rbf_food_id = 0;
    $rbf_food_name = NULL;
    $rbf_food_confidence = 0;
    $rbf_ingredients_found = [];
    $rbf_ingredients_missing = [];
    $rbf_predictions = [];
    $rbf_versions = !empty($row->rbf_version) ? (array)json_decode($row->rbf_version, true) : [];

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

    //model2
    if ($row->rbf_model) {
      $api2 = (array)json_decode($row->rbf_api_2, true);
      $rbf_predictions = count($api2) ? $api2['predictions'] : [];
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

    //resolved
    if ($row->is_resolved) {
      $rbf_ingredients_missing = [];
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
        'versions' => $rbf_versions,
        'model' => $row->rbf_model,
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

    $rbf_error = isset($values['rbf_error']) ? (int)$values['rbf_error'] : 0;
    $noted = isset($values['note']) ? $values['note'] : NULL;
    $texts = isset($values['texts']) && count($values['texts']) ? (array)$values['texts'] : [];
    $customer_requested = isset($values['customer_requested']) && !empty($values['customer_requested']) ? (int)$values['customer_requested'] : 0;
    $food_multi = isset($values['food_multi']) && !empty($values['food_multi']) ? (int)$values['food_multi'] : 0;
    $food_count = isset($values['food_count']) && !empty($values['food_count']) ? (int)$values['food_count'] : 0;

    $unknown = true;

    //customer_requested
    if (!$customer_requested) {
      $row->update([
        'customer_requested' => 0,
      ]);
    }
    if (!$row->customer_requested && $customer_requested) {
      $row->update([
        'customer_requested' => $this->_viewer->id,
      ]);
    }

    //count_foods
    if (!$food_multi) {
      $row->update([
        'count_foods' => 0,
      ]);
    }
    if ($food_multi && $food_count) {
      $row->update([
        'count_foods' => $food_count,
      ]);
    }

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

    //notify main note
    $notify_note = false;
    if ($row->note !== $noted) {
      $notify_note = true;
    }

    $row->update([
      'note' => $noted,
      'usr_edited' => json_encode($edited),
    ]);

    if ($rbf_error) {
      if ($row->rbf_error != $this->_viewer->id) {
        $row->update([
          'rbf_error' => $this->_viewer->id,
        ]);
      }
    } else {
      $row->update([
        'rbf_error' => 0,
      ]);
    }

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

    if ($notify_note) {
      $row->update_main_note($this->_viewer);
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

  public function food_scan_resolve(Request $request)
  {
    $values = $request->all();

    $validator = Validator::make($values, [
      'rfs' => 'required',
    ]);
    if ($validator->fails()) {
      return response()->json($validator->errors(), 422);
    }
    //invalid
    $rfs = RestaurantFoodScan::find((int)$values['rfs']);
    if (!$rfs) {
      return response()->json([
        'error' => 'Invalid item'
      ], 422);
    }

    $val = isset($values['val']) ? (int)$values['val'] : 0;

    $rfs->update([
      'is_resolved' => $val ? $this->_viewer->id : 0,
    ]);

    if ($val) {
      RestaurantFoodScanMissing::where('restaurant_food_scan_id', $rfs->id)
        ->delete();
      $rfs->update_ingredients_missing_text();
    } else {
      //refresh
      $rfs->predict_food([
        'notification' => false,
      ]);
    }

    return response()->json([
      'is_resolved' => $rfs->is_resolved,
    ]);
  }

  public function food_scan_mark(Request $request)
  {
    $values = $request->all();

    $validator = Validator::make($values, [
      'rfs' => 'required',
    ]);
    if ($validator->fails()) {
      return response()->json($validator->errors(), 422);
    }
    //invalid
    $rfs = RestaurantFoodScan::find((int)$values['rfs']);
    if (!$rfs) {
      return response()->json([
        'error' => 'Invalid item'
      ], 422);
    }

    $val = isset($values['val']) ? (int)$values['val'] : 0;

    $rfs->update([
      'is_marked' => $val ? $this->_viewer->id : 0,
    ]);

    return response()->json([
      'is_marked' => $rfs->is_marked,
    ]);
  }

  public function food_scan_view(Request $request)
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

    $item_type = isset($values['item_type']) ? $values['item_type'] : NULL;
    $item_id = isset($values['item_id']) ? (int)$values['item_id'] : 0;

    $ids = [];

    $ids = $row->get_stats_by_conditions([
      'times' => $times,
      'item_type' => $item_type,
      'item_id' => $item_id,
    ]);

    if (count($ids)) {
      $ids = array_column($ids, 'id');
    }

    return response()->json([
      'ids' => $ids,
      'ids_string' => count($ids) ? implode(';', $ids) : '',
      'itd' => count($ids) ? $ids[0] : 0,

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

  public function kitchen(string $id, Request $request)
  {
    $values = $request->all();
    $debug = isset($values['debug']) ? (int)$values['debug'] : 0;

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
      'debug' => $debug,
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

        //no 1024
        $temps = array_filter(explode('/', $file));
        $photo_name = $temps[count($temps) - 1];
        if (substr($photo_name, 0, 5) == '1024_') {
          continue;
        }

        //no duplicate
        $keyword = SysRobo::photo_name_query($file);

        DB::beginTransaction();

        try {
          //check exist
          $row = RestaurantFoodScan::where('restaurant_id', $restaurant->id)
            ->where('photo_name', $file)
            ->first();
          if (!$row) {

            $status = 'new';

            $rows = RestaurantFoodScan::where('photo_name', 'LIKE', $keyword)
              ->where('restaurant_id', $restaurant->id)
              ->get();
            if (count($rows)) {
              $status = 'duplicated';
            }

            $row = $restaurant->photo_save([
              'local_storage' => 1,
              'photo_url' => NULL,
              'photo_name' => $file,
              'photo_ext' => 'jpg',
              'time_photo' => date('Y-m-d H:i:s'),

              'status' => $status,
            ]);
          }

          DB::commit();

        } catch (Exception $e) {
          DB::rollBack();
        }

        //get 1 latest file
        break;
      }
    }

    if (!$row || ($row && $row->status == 'duplicated')) {
      $row = RestaurantFoodScan::where('restaurant_id', $restaurant->id)
        ->where('status', '<>', 'duplicated')
        ->where('deleted', 0)
        ->orderBy('id', 'desc')
        ->limit(1)
        ->first();
    }

    $datas = [];
    if ($row) {
      $datas = $this->kitchen_food_datas($row, [
        'kitchen' => true,
      ]);

      if (count($datas) && !$datas['confidence']) {

      }
    }

    return response()->json([
      'status' => $row ? $row->status : 'no_photo',

      'file' => $row ? $row->photo_name : '',
      'file_url' => $row ? $row->get_photo() : '',
      'file_id' => $row ? $row->id : 0,

      'datas' => $datas,
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

    $restaurant_parent = $restaurant->get_parent();

    $type = isset($values['type']) ? $values['type'] : NULL;
    //model2
    $model2 = false;
    if ($restaurant_parent && $restaurant_parent->model_scan
      && !empty($restaurant_parent->model_name) && !empty($restaurant_parent->model_version)
    ) {
      $model2 = true;
    }

    switch ($type) {
      case 'api':

        if ($row->status == 'new') {

          //step 2= photo scan
          //model2
          if ($model2) {
            $row->model_api_2([
              'dataset' => $restaurant_parent->model_name,
              'version' => $restaurant_parent->model_version,
            ]);
          }
          else {
            $row->model_api_1([
              'confidence' => SysRobo::_SCAN_CONFIDENCE,
              'overlap' => SysRobo::_SCAN_OVERLAP,
            ]);
          }

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

    $datas = [];
    if ($row) {
      $datas = $this->kitchen_food_datas($row, [
        'kitchen' => true,
      ]);

      if (count($datas) && !$datas['confidence']) {
        $notifys = [];
        $notify_ids = [];
        $text_to_speech = false;
        $text_to_speak = '';
        $printer = false;
      }
    }

    return response()->json([
      'status' => true,

      //data
      'food_id' => $row->get_food() ? $row->get_food()->id : 0,
      'datas' => $datas,
      //notify
      'notifys' => $notifys,
      'notify_ids' => $notify_ids,
      'speaker' => $text_to_speech && !empty($text_to_speak),
      'speaker_text' => $text_to_speak,
      'printer' => $printer,
    ]);
  }

  protected function kitchen_food_datas(RestaurantFoodScan $row, $pars = [])
  {
    if (!$row) {
      return [];
    }

    $restaurant = $row->get_restaurant();
    $restaurant_parent = $row->get_restaurant()->get_parent();
    $food = $row->get_food() ? $row->get_food() : NULL;

    $kitchen = isset($pars['kitchen']) ? (bool)$pars['kitchen'] : false;

    $ingredients_found = [];
    $ingredients_missing = [];

    $html_info = '';
    $food_id = 0;
    $food_name = '';
    $food_photo = '';
    $is_resolved = 0;
    $is_marked = 0;
    $live_group = 3;

    if ($food) {

      $food_id = $food->id;
      $food_name = $food->name;
      $food_photo = $food->get_photo([
        'restaurant_parent_id' => $restaurant->restaurant_parent_id
      ]);

      $is_resolved = $row->is_resolved;
      $is_marked = $row->is_marked;

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

      //uat
      $live_group = $restaurant_parent->get_food_live_group($food);
      switch ($live_group) {
        case 1:

          break;

        case 2:

          if ($row->confidence < 80 || !count($ingredients_found)) {
            $food_id = 0;
            $food_name = '';
            $food_photo = '';
            $html_info = '';
          }

          $is_resolved = 0;
          $is_marked = 0;

          if ($food_id && !count($ingredients_missing)) {

          } else {
            $ingredients_missing = [];
            $ingredients_found = [];
          }

          break;

        case 3:

          if ($row->confidence < 90 || !count($ingredients_found)) {
            $food_id = 0;
            $food_name = '';
            $food_photo = '';
            $html_info = '';
          }

          $is_resolved = 0;
          $is_marked = 0;

          $ingredients_missing = [];
          $ingredients_found = [];

          break;
      }
    }

    return [
      'food_id' => $food_id,
      'food_photo' => $food_photo,
      'food_name' => $food_name,
      'is_resolved' => $is_resolved,
      'is_marked' => $is_marked,

      'confidence' => $live_group,

      'html_info' => $html_info,

      'time_photo' => $row->time_photo,
      'time_scan' => $row->time_scan,
      'time_end' => $row->time_end,
      'total_times' => !empty($row->time_end)
        ? (int)date('s', strtotime($row->time_end) - strtotime($row->time_photo)) : 0,
      'total_robos' => $row->total_seconds,

      'localhost' => App::environment() == 'local' ? 1 : 0,

      'ingredients_missing' => $ingredients_missing,
      'ingredients_found' => $ingredients_found,
    ];
  }

}
