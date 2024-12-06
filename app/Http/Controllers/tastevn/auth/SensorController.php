<?php

namespace App\Http\Controllers\tastevn\auth;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
//lib
use Validator;
use App\Api\SysAws;
use App\Api\SysCore;
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

  public function __construct()
  {
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

      'debug' => $debug && $this->_viewer->is_super_admin(),
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
    $rfs = RestaurantFoodScan::find((int)$values['item']);
    if (!$rfs) {
      return response()->json([
        'error' => 'Invalid item'
      ], 422);
    }

    $type = isset($values['type']) ? (int)$values['type'] : 1;
    switch ($type) {
      case 1:

        $rfs->rfs_photo_predict([
          'notification' => false,
        ]);

        break;

      case 2:

        $rfs->rfs_photo_scan([
          'notification' => false,
        ]);

        break;

      case 3:
        $rfs->update([
          'status' => 'tested',
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
    $rfs = RestaurantFoodScan::find((int)$values['item']);
    if (!$rfs) {
      return response()->json([
        'error' => 'Invalid item'
      ], 422);
    }

    //model 1
    $api_result = (array)json_decode($rfs->rbf_api, true);
    $predictions = isset($api_result['result']) && isset($api_result['result']['predictions'])
      ? (array)$api_result['result']['predictions'] : [];
    if (!count($predictions)) {
      //old
      $predictions = isset($api_result['predictions']) && isset($api_result['predictions'])
        ? (array)$api_result['predictions'] : [];
    }

    $versions = (array)json_decode($rfs->rbf_version, true);

    //food
    $food_rbf = $rfs->get_food_rbf();
    $ingredients_missing = $rfs->get_ingredients_missing();
    $ingredients_found = $rfs->get_ingredients_found();
    $ingredients_recipe = $rfs->get_ingredients_recipe();

    $sensor = $rfs->get_restaurant();
    $restaurant = $rfs->get_restaurant()->get_parent();
    $food = $rfs->get_food();

    $comments = $rfs->get_comments();
    $texts = $rfs->get_texts([
      'text_name_only' => 1
    ]);

    $confidence_group = 3;
    if ($food_rbf) {
      $confidence_group = $food_rbf->get_live_group([
        'restaurant_parent_id' => $restaurant->id,
      ]);
    }

    //info
    $html_info = view('tastevn.htmls.item_food_scan_info')
      ->with('rfs', $rfs)

      ->with('restaurant', $restaurant)
      ->with('sensor', $sensor)
      ->with('food', $food)

      ->with('food_rbf', $food_rbf)
      ->with('food_rbf_confidence', $rfs->rbf_confidence)

      ->with('versions', $versions)
      ->with('predictions', $predictions)
      ->with('ingredients_missing', $ingredients_missing)
      ->with('ingredients_found', $ingredients_found)
      ->with('ingredients_recipe', $ingredients_recipe)

      ->with('confidence_group', $confidence_group)

      ->with('comments', $comments)
      ->with('texts', $texts)
      ->render();

    $this->_viewer->add_log([
      'type' => 'view_item_' . $rfs->get_type(),
      'restaurant_id' => (int)$rfs->restaurant_id,
      'item_id' => (int)$rfs->id,
      'item_type' => $rfs->get_type(),
    ]);

    return response()->json([
      'html_info' => $html_info,

      'rfs' => [
        'id' => $rfs->id,
      ],
      'sensor' => [
        'id' => $sensor->id,
        'name' => $sensor->name,
      ],

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
      $times = SysCore::arr_datetime_range($time_scan);
      if (!empty($times['time_from'])) {
        $select->where('time_scan', '>=', $times['time_from']);
      }
      if (!empty($times['time_to'])) {
        $select->where('time_scan', '<=', $times['time_to']);
      }
    }
    if (!empty($time_upload)) {
      $times = SysCore::arr_datetime_range($time_upload);
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
    $customer_requested = isset($values['customer_requested']) && !empty($values['customer_requested'])
      ? (int)$values['customer_requested'] : 0;
    $note_kitchen = isset($values['note_kitchen']) && !empty($values['note_kitchen'])
      ? (int)$values['note_kitchen'] : 0;
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
    if ($food_multi && $food_count > 1) {
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
//          'confidence' => 100,
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
        $row->rfs_ingredients_missing($food, $ingredients_missing, false);

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

    //note_kitchen
    if (!$note_kitchen) {
      $row->update([
        'note_kitchen' => 0,
      ]);
    }
    if (!$row->note_kitchen && $note_kitchen) {

      if ($row->get_food()) {
        RestaurantFoodScan::where('deleted', 0)
//          ->where('restaurant_id', $row->restaurant_id)
          ->where('food_id', $row->get_food()->id)
          ->update([
            'note_kitchen' => 0,
          ]);

        $row->update([
          'note_kitchen' => $this->_viewer->id,
        ]);
      }
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
      $rfs->rfs_ingredients_missing_text();
    } else {
      //refresh
      $rfs->rfs_photo_predict([
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

      'sse' => false, //$row->id == 9 || $row->id == 10 ? true : false,

      'debug' => $debug, // && $this->_viewer->is_super_admin(),
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
    $sensor = Restaurant::find((int)$values['item']);
    if (!$sensor) {
      return response()->json([
        'error' => 'Invalid item'
      ], 422);
    }

//    $rfs = NULL;
//    $type = isset($values['type']) ? $values['type'] : NULL;

    $rfs = RestaurantFoodScan::where('restaurant_id', $sensor->id)
      ->whereIn('status', ['new', 'scanned', 'checked', 'failed'])
      ->where('deleted', 0)
      ->orderBy('id', 'desc')
      ->limit(1)
      ->first();

    //tester
    if ($this->_viewer->is_dev()) {
//      $rfs = RestaurantFoodScan::find(84965);
    }

    $datas = $rfs ? $this->kitchen_food_datas($rfs) : [];
    return response()->json([
      'status' => $rfs ? $rfs->status : 'no_photo',

      'datas' => $datas,

      'file' => $rfs ? $rfs->photo_name : '',
      'file_url' => $rfs ? $rfs->get_photo() : '',
      'file_id' => $rfs ? $rfs->id : 0,
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
    $rfs = RestaurantFoodScan::find((int)$values['item']);
    $sensor = Restaurant::find((int)$values['restaurant_id']);
    if (!$sensor || !$rfs) {
      return response()->json([
        'error' => 'Invalid item'
      ], 422);
    }

    //scan & predict
//    if ($rfs->status == 'new') {
//      $rfs->rfs_photo_scan();
//    }

//    $rfs = RestaurantFoodScan::find($rfs->id);
    $datas = $rfs ? $this->kitchen_food_datas($rfs) : [];

    //speaker
    $text_to_speak = '';

    $ingredients_missing = isset($datas['ingredients_missing']) ? (array)$datas['ingredients_missing'] : [];
    if (count($ingredients_missing)) {

      $ingredients_missing_speak = '';
      foreach ($ingredients_missing as $ing) {
        $ingredients_missing_speak .= SysRobo::burger_ingredient_chicken_beef($ing['name']) . ', ';
      }

      $text_to_speak = '[Missing], '
        . $ingredients_missing_speak
        . ', [Need to re-check]'
      ;

      //aws check
      SysAws::s3_polly([
        'text_to_speak' => $text_to_speak,
        'text_rate' => 'slow',
      ]);
    }

    return response()->json([
      'status' => true,

      'speaker' => isset($datas['confidence']) && (int)$datas['confidence'] == 1 && !empty($text_to_speak),

      'datas' => $datas,
    ]);
  }

  protected function kitchen_food_datas(RestaurantFoodScan $row, $pars = [])
  {
    if (!$row) {
      return [];
    }

    $sensor = $row->get_restaurant();
    $restaurant = $row->get_restaurant()->get_parent();
    $food = $row->get_food() ? $row->get_food() : NULL;

    $ingredients_found = [];
    $ingredients_missing = [];

    $html_info = '';
    $food_id = 0;
    $food_name = '';
    $food_photo = '';
    $is_resolved = 0;
    $is_marked = 0;
    $live_group = 3;
    $main_note = '';

    $text_to_speak = '';

    if ($food) {

      $food_id = $food->id;
      $food_name = $food->name;
      $food_photo = $food->get_photo([
        'restaurant_parent_id' => $sensor->restaurant_parent_id
      ]);

      $is_resolved = $row->is_resolved;
      $is_marked = $row->is_marked;

      //info recipe
      $html_info = view('tastevn.htmls.item_food_dashboard')
        ->with('recipes', $food->get_recipes([
          'restaurant_parent_id' => $sensor->restaurant_parent_id,
        ]))
        ->render();

      //ingredient missing
      $ingredients_missing = $row->get_ingredients_missing();
      if (count($ingredients_missing)) {
        $temps = [];

        foreach ($ingredients_missing as $ing) {
          $temps[] = (array)$ing;
        }

        $ingredients_missing = $temps;
      }

      //ingredient found
      $ingredients = $row->get_ingredients_found();
      if (count($ingredients)) {
        foreach ($ingredients as $ing) {
          $ing = (array)$ing;

          $ingredients_found[] = $ing;
        }
      }

      //uat
      $live_group = $restaurant->get_food_live_group($food);
      switch ($live_group) {
        case 1:

          break;

        case 2:

          if ($row->confidence < 65 || !count($ingredients_found)) {
            $food_id = 0;
            $food_name = '';
            $food_photo = '';
            $html_info = '';
          }

          $is_resolved = 0;
          $is_marked = 0;

          if ($food_id && !count($ingredients_missing)) {

          } else {
//            if (count($ingredients_missing) > 2) {
              $ingredients_missing = [];
              $ingredients_found = [];
//            }
          }

          break;

        case 3:

//          if ($row->confidence < 95 || !count($ingredients_found)) {
            $food_id = 0;
            $food_name = '';
            $food_photo = '';
            $html_info = '';
//          }

          $is_resolved = 0;
          $is_marked = 0;

          $ingredients_missing = [];
          $ingredients_found = [];

          break;
      }

      //main note
      $main_note = RestaurantFoodScan::where('deleted', 0)
        ->where('food_id', $food->id)
        ->where('note_kitchen', '>', 0)
        ->where('note', '<>', NULL)
        ->limit(1)
        ->first();
      if ($main_note) {
        $main_note = $main_note->note;
      }

      //speaker
      if (count($ingredients_missing)) {

        $ingredients_missing_speak = '';
        foreach ($ingredients_missing as $ing) {
          $ing = (array)$ing;

          $ingredients_missing_speak .= SysRobo::burger_ingredient_chicken_beef($ing['name']) . ', ';
        }

        $text_to_speak = '[Missing], '
          . $ingredients_missing_speak
          . ', [Need to re-check]'
        ;

        //aws check
        SysAws::s3_polly([
          'text_to_speak' => $text_to_speak,
          'text_rate' => 'slow',
        ]);
      }
    }

    return [
      'food_id' => $food_id,
      'food_photo' => $food_photo,
      'food_name' => $food_name,
      'is_resolved' => $is_resolved,
      'is_marked' => $is_marked,

      'confidence' => $live_group,
      'main_note' => $main_note,

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

      'sensor_id' => $sensor ? $sensor->id : 0,
      'sensor_name' => $sensor ? $sensor->name : NULL,

      'restaurant_id' => $restaurant ? $restaurant->id : 0,
      'restaurant_name' => $restaurant ? $restaurant->name : NULL,

      'rfs_id' => $row->id,
      'rfs_note' => $row->note,
      'rfs_time' => date('d/m/Y H:i:s', strtotime($row->time_photo)),

      'speaker' => $live_group == 1 && !empty($text_to_speak) ? 1 : 0,
    ];
  }

  public function kitchens(Request $request)
  {
    $values = $request->all();
    $debug = isset($values['debug']) ? (int)$values['debug'] : 0;

    $pageConfigs = [
      'myLayout' => 'horizontal',
      'hasCustomizer' => false,

      'debug' => $debug && $this->_viewer->is_super_admin(),
    ];

    return view('tastevn.pages.dashboard_kitchen_admin', ['pageConfigs' => $pageConfigs]);
  }

  public function sse_stream_kitchen(string $id, Request $request)
  {
    $sensor = Restaurant::find((int)$id);

    header('Content-Type: text/event-stream');
    header('Cache-Control: no-cache');
    header('Connection: keep-alive');

    if ($sensor) {

      $rfs = RestaurantFoodScan::where('restaurant_id', $sensor->id)
        ->whereIn('status', ['new', 'scanned', 'checked', 'failed'])
        ->where('deleted', 0)
        ->orderBy('id', 'desc')
        ->limit(1)
        ->first();

      //tester
      if ($this->_viewer->is_dev()) {
//        $rfs = RestaurantFoodScan::find(79353);
      }

      $datas = $rfs ? $this->kitchen_food_datas($rfs) : [];

      $datas = array_merge($datas, [
        'status' => $rfs ? $rfs->status : 'no_photo',

        'file' => $rfs ? $rfs->photo_name : '',
        'file_url' => $rfs ? $rfs->get_photo() : '',
        'file_id' => $rfs ? $rfs->id : 0,
      ]);

      echo "data:" . json_encode($datas) . "\n\n";

    } else {
      echo "\n\n";
    }

    ob_flush();
    flush();
  }
}
