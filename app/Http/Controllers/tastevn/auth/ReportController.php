<?php

namespace App\Http\Controllers\tastevn\auth;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
//lib
use Validator;
use App\Api\SysApp;
use App\Api\SysRobo;
//model
use App\Models\Report;
use App\Models\Food;
use App\Models\Ingredient;
use App\Models\ReportPhoto;
use App\Models\RestaurantFoodScan;
use App\Models\RestaurantFoodScanText;
use App\Models\Text;

class ReportController extends Controller
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
    $values = $request->all();

    $invalid_roles = ['user'];
    if (in_array($this->_viewer->role, $invalid_roles)) {
      return redirect('error/404');
    }

    $pageConfigs = [
      'myLayout' => 'horizontal',
      'hasCustomizer' => false,
    ];


    return view('tastevn.pages.reports', ['pageConfigs' => $pageConfigs]);
  }

  public function store(Request $request)
  {
    $values = $request->post();

    //required
    $validator = Validator::make($values, [
      'name' => 'required|string',
      'restaurant_parent_id' => 'required',
      'dates' => 'required',
    ]);
    if ($validator->fails()) {
      return response()->json($validator->errors(), 422);
    }

    $dates = $this->_sys_app->parse_date_range($values['dates']);

    $row = Report::create([
      'name' => trim($values['name']),
      'restaurant_parent_id' => (int)$values['restaurant_parent_id'],
      'date_from' => $dates['time_from'],
      'date_to' => $dates['time_to'],
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
      'dates' => 'required',
    ]);
    if ($validator->fails()) {
      return response()->json($validator->errors(), 422);
    }

    $row = Report::find((int)$values['item']);
    if (!$row) {
      return response()->json([
        'error' => 'Invalid item'
      ], 422);
    }

    $dates = $this->_sys_app->parse_date_range($values['dates']);

    $row->update([
      'name' => trim($values['name']),
      'restaurant_parent_id' => (int)$values['restaurant_parent_id'],
      'date_from' => $dates['time_from'],
      'date_to' => $dates['time_to'],
    ]);

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

    $row = Report::find((int)$values['item']);
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
      'item' => $row->name,
    ], 200);
  }

  public function show(string $id, Request $request)
  {
    $values = $request->all();

    $invalid_roles = ['user'];
    if (in_array($this->_viewer->role, $invalid_roles)) {
      return redirect('error/404');
    }

    $row = Report::find((int)$id);
    if (!$row || $row->deleted || !count($row->get_items())) {
      return redirect('error/404');
    }

    //search
    $debug = isset($values['debug']) ? (int)$values['debug'] : 0;

    $texts = Text::where('deleted', 0)
      ->orderByRaw('TRIM(LOWER(name)) + 0')
      ->get();

    $pageConfigs = [
      'myLayout' => 'horizontal',
      'hasCustomizer' => false,

      'item' => $row,
      'texts' => $texts,

      'debug' => $debug,
    ];

    return view('tastevn.pages.report_info', ['pageConfigs' => $pageConfigs]);
  }

  public function table(Request $request)
  {
    $values = $request->post();

    //required
    $validator = Validator::make($values, [
      'item' => 'required',
    ]);
    if ($validator->fails()) {
      return response()->json($validator->errors(), 422);
    }

    $row = Report::find((int)$values['item']);
    if (!$row) {
      return response()->json([
        'error' => 'Invalid item'
      ], 422);
    }

    $html = view('tastevn.htmls.item_report')
      ->with('items', $row->get_items())
      ->render();

    return response()->json([
      'status' => true,
      'html' => $html,
      'not_found' => $row->total_photos - $row->total_points,
    ], 200);
  }

  public function start(Request $request)
  {
    $values = $request->post();

    //required
    $validator = Validator::make($values, [
      'item' => 'required',
    ]);
    if ($validator->fails()) {
      return response()->json($validator->errors(), 422);
    }

    $row = Report::find((int)$values['item']);
    if (!$row) {
      return response()->json([
        'error' => 'Invalid item'
      ], 422);
    }

    $row->start();

    return response()->json([
      'status' => true,
      'item' => $row->name,
    ], 200);
  }

  public function photo_not_found(Request $request)
  {
    $values = $request->post();

    //required
    $validator = Validator::make($values, [
      'item' => 'required',
    ]);
    if ($validator->fails()) {
      return response()->json($validator->errors(), 422);
    }

    $row = Report::find((int)$values['item']);
    if (!$row) {
      return response()->json([
        'error' => 'Invalid item'
      ], 422);
    }

    $photo = ReportPhoto::where('report_id', $row->id)
      ->where('reporting', 0)
      ->orderBy('id', 'asc')
      ->limit(1)
      ->first();

    $rfs = $photo->get_rfs();

    $html = view('tastevn.htmls.item_report_photo_not_found')
      ->with('rfs', $rfs)
      ->with('comments', $rfs->get_comments())
      ->render();

    return response()->json([
      'status' => true,
      'item' => [
        'id' => $row->id,
        'point' => $row->point,
      ],
      'rfs' => [
        'id' => $rfs->id,
        'food_id' => $rfs->food_id,
        'note' => $rfs->note,
        'texts' => $rfs->get_texts(['text_name_only' => 1])
      ],
      'html' => $html,
    ], 200);
  }

  public function photo_update(Request $request)
  {
    $values = $request->post();

    //required
    $validator = Validator::make($values, [
      'item' => 'required',
      'rfs' => 'required',
    ]);
    if ($validator->fails()) {
      return response()->json($validator->errors(), 422);
    }

    $row = Report::find((int)$values['item']);
    $rfs = RestaurantFoodScan::find((int)$values['rfs']);
    if (!$row || !$rfs) {
      return response()->json([
        'error' => 'Invalid item'
      ], 422);
    }

    $food = isset($values['food']) ? (int)$values['food'] : 0;
    $point = isset($values['point']) ? (float)$values['point'] : 0;
    $noted = isset($values['note']) && !empty($values['note']) ? $values['note'] : NULL;
    $texts = isset($values['texts']) && count($values['texts']) ? (array)$values['texts'] : [];
    $missing = isset($values['missing']) ? (int)$values['missing'] : 0;
    $ingredients = isset($values['ingredients']) ? (array)$values['ingredients'] : [];

    $food = Food::find($food);
    if (!$food) {
      return response()->json([
        'error' => 'Invalid data'
      ], 422);
    }

    $item_old = $rfs->toArray();

    //food_scan_update
    if ($food->id != $rfs->food_id) {
      $rfs->update([
        'food_id' => $food->id,
      ]);
    }

    $rfs->update([
      'usr_predict' => $food->id,
      'found_by' => 'usr',
      'status' => 'edited',
      'confidence' => 100,
    ]);

    //ingredients_missing
    $ingredients_missing = [];
    if ($missing && count($ingredients)) {
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
    $rfs->add_ingredients_missing($food, $ingredients_missing, false);

    //texts
    RestaurantFoodScanText::where('restaurant_food_scan_id', $rfs->id)
      ->delete();
    if (count($texts)) {
      foreach ($texts as $text) {
        RestaurantFoodScanText::create([
          'restaurant_food_scan_id' => $rfs->id,
          'text_id' => (int)$text,
        ]);
      }
    }

    $rfs->update_text_notes();

    //edited
    $rfs = RestaurantFoodScan::find($rfs->id);
    $item_new = $rfs->toArray();

    $edited = [
      'before' => $item_old,
      'after' => $item_new,
    ];

    $rfs->update([
      'note' => $noted,
      'usr_edited' => json_encode($edited),
    ]);

    //report photo_update
    $photo = ReportPhoto::where('report_id', $row->id)
      ->where('restaurant_food_scan_id', $rfs->id)
      ->where('reporting', 0)
      ->first();
    if ($photo) {
      $photo->update([
        'food_id' => $food->id,
        'point' => $point,
//        'note' => $noted,
      ]);
    }

    return response()->json([
      'status' => true,
      'item' => $row,
    ], 200);
  }

  public function photo_food(Request $request)
  {
    $values = $request->post();

    //required
    $validator = Validator::make($values, [
      'item' => 'required',
      'food' => 'required',
    ]);
    if ($validator->fails()) {
      return response()->json($validator->errors(), 422);
    }

    $row = Report::find((int)$values['item']);
    $food = Food::find((int)$values['food']);
    if (!$row || !$food) {
      return response()->json([
        'error' => 'Invalid item'
      ], 422);
    }

    $html = view('tastevn.htmls.item_report_photo_food')
      ->with('ingredients', $food->get_ingredients([
        'restaurant_parent_id' => $row->restaurant_parent_id,
      ]))
      ->render();

    return response()->json([
      'status' => true,
      'html' => $html,
    ], 200);
  }
}
