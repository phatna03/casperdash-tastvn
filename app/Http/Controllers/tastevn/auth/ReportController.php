<?php

namespace App\Http\Controllers\tastevn\auth;
use App\Http\Controllers\Controller;
use App\Models\Food;
use App\Models\ReportPhoto;
use App\Models\RestaurantFoodScan;
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

    $pageConfigs = [
      'myLayout' => 'horizontal',
      'hasCustomizer' => false,

      'item' => $row,

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

    $html = view('tastevn.htmls.item_report_photo_not_found')
      ->with('rfs', $photo->get_rfs())
      ->with('comments', $photo->get_rfs()->get_comments())
      ->render();

    return response()->json([
      'status' => true,
      'item' => $row,
      'rfs_id' => $photo->get_rfs()->id,
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
    $note = isset($values['note']) && !empty($values['note']) ? $values['note'] : NULL;

    $food = Food::find($food);
    if (!$food) {
      return response()->json([
        'error' => 'Invalid data'
      ], 422);
    }

    $photo = ReportPhoto::where('report_id', $row->id)
      ->where('restaurant_food_scan_id', $rfs->id)
      ->where('reporting', 0)
      ->first();
    if ($photo) {
      $photo->update([
        'food_id' => $food->id,
        'point' => $point,
        'note' => $note,
      ]);
    }

    return response()->json([
      'status' => true,
      'item' => $row,
    ], 200);
  }
}
