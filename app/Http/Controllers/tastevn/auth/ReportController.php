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

    $type = isset($values['type']) ? $values['type'] : 'not_found';
    $food_id = isset($values['food_id']) ? (int)$values['food_id'] : 0;

    $date_from = $row->date_from;
    $date_to = $row->date_to;

    switch ($type) {

      case 'full':

        $food = Food::find($food_id);

        $photo = ReportPhoto::query('report_photos')
          ->leftJoin('restaurant_food_scans', 'restaurant_food_scans.id', '=', 'report_photos.restaurant_food_scan_id')
          ->where('report_photos.report_id', $row->id)
          ->where('report_photos.food_id', $food_id)
          ->where('report_photos.reporting', 1)
          ->where('restaurant_food_scans.missing_texts', NULL)
          ->where('restaurant_food_scans.status', 'checked')
          ->where('report_photos.status', 'passed')
//          ->where('restaurant_food_scans.time_photo', '>=', $date_from)
//          ->where('restaurant_food_scans.time_photo', '<=', $date_to)
          ->orderBy('report_photos.restaurant_food_scan_id', 'asc')
          ->limit(1)
          ->first();

        $rfs = $photo->get_rfs();

        $photo_ids = ReportPhoto::query('report_photos')
          ->select('restaurant_food_scan_id as id')
          ->leftJoin('restaurant_food_scans', 'restaurant_food_scans.id', '=', 'report_photos.restaurant_food_scan_id')
          ->where('report_photos.report_id', $row->id)
          ->where('report_photos.food_id', $food_id)
          ->where('report_photos.reporting', 1)
          ->where('restaurant_food_scans.missing_texts', NULL)
          ->where('restaurant_food_scans.status', 'checked')
          ->where('report_photos.status', 'passed')
//          ->where('restaurant_food_scans.time_photo', '>=', $date_from)
//          ->where('restaurant_food_scans.time_photo', '<=', $date_to)
          ->get();
        $photo_ids = count($photo_ids) ? array_column($photo_ids->toArray(), 'id') : [];

        break;

      case 'miss_right':

        $food = Food::find($food_id);

        $photo = ReportPhoto::query('report_photos')
          ->leftJoin('restaurant_food_scans', 'restaurant_food_scans.id', '=', 'report_photos.restaurant_food_scan_id')
          ->where('report_photos.report_id', $row->id)
          ->where('report_photos.food_id', $food_id)
          ->where('report_photos.reporting', 1)
          ->where('restaurant_food_scans.missing_ids', '<>', NULL)
          ->where('restaurant_food_scans.status', 'checked')
          ->where('report_photos.status', 'passed')
//          ->where('restaurant_food_scans.time_photo', '>=', $date_from)
//          ->where('restaurant_food_scans.time_photo', '<=', $date_to)
          ->orderBy('report_photos.restaurant_food_scan_id', 'asc')
          ->limit(1)
          ->first();

        $rfs = $photo->get_rfs();

        $photo_ids = ReportPhoto::query('report_photos')
          ->select('restaurant_food_scan_id as id')
          ->leftJoin('restaurant_food_scans', 'restaurant_food_scans.id', '=', 'report_photos.restaurant_food_scan_id')
          ->where('report_photos.report_id', $row->id)
          ->where('report_photos.food_id', $food_id)
          ->where('report_photos.reporting', 1)
          ->where('restaurant_food_scans.missing_ids', '<>', NULL)
          ->where('restaurant_food_scans.status', 'checked')
          ->where('report_photos.status', 'passed')
//          ->where('restaurant_food_scans.time_photo', '>=', $date_from)
//          ->where('restaurant_food_scans.time_photo', '<=', $date_to)
          ->get();
        $photo_ids = count($photo_ids) ? array_column($photo_ids->toArray(), 'id') : [];

        break;

      case 'miss_wrong':

        $food = Food::find($food_id);

        $photo = ReportPhoto::query('report_photos')
          ->leftJoin('restaurant_food_scans', 'restaurant_food_scans.id', '=', 'report_photos.restaurant_food_scan_id')
          ->where('report_photos.report_id', $row->id)
          ->where('report_photos.food_id', $food_id)
          ->where('report_photos.reporting', 1)
//          ->where('restaurant_food_scans.missing_ids', '<>', NULL)
          ->where('restaurant_food_scans.status', 'edited')
//          ->where('restaurant_food_scans.time_photo', '>=', $date_from)
//          ->where('restaurant_food_scans.time_photo', '<=', $date_to)
          ->orderBy('report_photos.restaurant_food_scan_id', 'asc')
          ->limit(1)
          ->first();

        $rfs = $photo->get_rfs();

        $photo_ids = ReportPhoto::query('report_photos')
          ->select('restaurant_food_scan_id as id')
          ->leftJoin('restaurant_food_scans', 'restaurant_food_scans.id', '=', 'report_photos.restaurant_food_scan_id')
          ->where('report_photos.report_id', $row->id)
          ->where('report_photos.food_id', $food_id)
          ->where('report_photos.reporting', 1)
//          ->where('restaurant_food_scans.missing_ids', '<>', NULL)
          ->where('restaurant_food_scans.status', 'edited')
//          ->where('restaurant_food_scans.time_photo', '>=', $date_from)
//          ->where('restaurant_food_scans.time_photo', '<=', $date_to)
          ->get();
        $photo_ids = count($photo_ids) ? array_column($photo_ids->toArray(), 'id') : [];

        break;

      case 'nf_wrong':

        $food = Food::find($food_id);

        $photo = ReportPhoto::query('report_photos')
          ->leftJoin('restaurant_food_scans', 'restaurant_food_scans.id', '=', 'report_photos.restaurant_food_scan_id')
          ->where('report_photos.report_id', $row->id)
          ->where('report_photos.food_id', $food_id)
          ->where('report_photos.reporting', 0)
          ->where('restaurant_food_scans.status', 'edited')
//          ->where('restaurant_food_scans.time_photo', '>=', $date_from)
//          ->where('restaurant_food_scans.time_photo', '<=', $date_to)
          ->orderBy('report_photos.restaurant_food_scan_id', 'asc')
          ->limit(1)
          ->first();

        $rfs = $photo->get_rfs();

        $photo_ids = ReportPhoto::query('report_photos')
          ->select('restaurant_food_scan_id as id')
          ->leftJoin('restaurant_food_scans', 'restaurant_food_scans.id', '=', 'report_photos.restaurant_food_scan_id')
          ->where('report_photos.report_id', $row->id)
          ->where('report_photos.food_id', $food_id)
          ->where('report_photos.reporting', 0)
          ->where('restaurant_food_scans.status', 'edited')
//          ->where('restaurant_food_scans.time_photo', '>=', $date_from)
//          ->where('restaurant_food_scans.time_photo', '<=', $date_to)
          ->get();
        $photo_ids = count($photo_ids) ? array_column($photo_ids->toArray(), 'id') : [];

        break;

      default:

        $photo = ReportPhoto::where('report_id', $row->id)
          ->where('reporting', 0)
          ->where('status', 'failed')
          ->where('food_id', 0)
          ->orderBy('restaurant_food_scan_id', 'asc')
          ->limit(1)
          ->first();

        $rfs = $photo->get_rfs();

        $photo_ids = ReportPhoto::select('restaurant_food_scan_id as id')
          ->where('report_id', $row->id)
          ->where('reporting', 0)
          ->where('status', 'failed')
          ->orderBy('restaurant_food_scan_id', 'asc')
          ->get();
        $photo_ids = count($photo_ids) ? array_column($photo_ids->toArray(), 'id') : [];
    }

    $point = $photo->point;
    if ($point <= 0) {
      $point = 0;
    } elseif ($point >= 1) {
      $point = 1;
    } else {
      $point = number_format($photo->point, 1, '.', '');
    }

    $rbf_predictions = [];
    $rbf_versions = !empty($rfs->rbf_version) ? (array)json_decode($rfs->rbf_version, true) : [];

    //predictions
    $apid = (array)json_decode($rfs->rbf_api, true);
    $rbf_predictions = isset($apid['result']) && isset($apid['result']['predictions'])
      ? (array)$apid['result']['predictions'] : [];
    if (!count($rbf_predictions)) {
      //old
      $rbf_predictions = isset($apid['predictions']) && isset($apid['predictions'])
        ? (array)$apid['predictions'] : [];
    }

    //model2
    if ($rfs->rbf_model) {
      $api2 = (array)json_decode($rfs->rbf_api_2, true);
      $rbf_predictions = count($api2) ? $api2['predictions'] : [];
    }

    $comments = [];
    $cmts = $rfs->get_comments();
    if (count($cmts)) {
      foreach ($cmts as $cmt) {
        $comments[] = [
          'user_name' => $cmt->owner->name,
          'user_noted' => $cmt->content,
          'created_at_1' => date('d/m/Y', strtotime($cmt->created_at)),
          'created_at_2' => date('H:i:s', strtotime($cmt->created_at)),
        ];
      }
    }

    $html = view('tastevn.htmls.item_report_photo_not_found')
      ->with('rfs', $rfs)
      ->with('predictions', $rbf_predictions)
      ->with('versions', $rbf_versions)
      ->with('model', $rfs->rbf_model)
      ->with('comments', $comments)
      ->render();

    $texts = $rfs->get_texts(['text_id_only' => 1]);
    $texts = count($texts) ? array_column($texts->toArray(), 'id') : [];

    //tester
    $ingredients = $rfs->get_ingredients_missing();
    $ingredients = count($ingredients) ? array_column($ingredients, 'id') : [];

    return response()->json([
      'status' => true,
      'ids' => $photo_ids,
      'photo' => [
        'id' => $photo->id,
        'point' => $point,
      ],
      'rfs' => [
        'id' => $rfs->id,
        'food_id' => $rfs->food_id,

        'note' => $rfs->note,
        'texts' => $texts,

        'comments' => $comments,

        'ingredients' => $ingredients,

        'rbf_error' => $rfs->rbf_error,
        'customer_requested' => $rfs->customer_requested,
        'note_kitchen' => $rfs->note_kitchen,
        'count_foods' => $rfs->count_foods,
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

    $item_old = $rfs->toArray();

    $food = isset($values['food']) ? (int)$values['food'] : 0;
    $point = isset($values['point']) ? (float)$values['point'] : 0;
    $noted = isset($values['note']) && !empty($values['note']) ? $values['note'] : NULL;
    $texts = isset($values['texts']) && count($values['texts']) ? (array)$values['texts'] : [];
    $missing = isset($values['missing']) ? (int)$values['missing'] : 0;
    $ingredients = isset($values['ingredients']) ? (array)$values['ingredients'] : [];
    $type = isset($values['type']) && !empty($values['type']) ? $values['type'] : 'not_found';

    $rbf_error = isset($values['rbf_error']) ? (int)$values['rbf_error'] : 0;
    $customer_requested = isset($values['customer_requested']) && !empty($values['customer_requested'])
      ? (int)$values['customer_requested'] : 0;
    $note_kitchen = isset($values['note_kitchen']) && !empty($values['note_kitchen'])
      ? (int)$values['note_kitchen'] : 0;
    $food_multi = isset($values['food_multi']) && !empty($values['food_multi']) ? (int)$values['food_multi'] : 0;
    $food_count = isset($values['food_count']) && !empty($values['food_count']) ? (int)$values['food_count'] : 0;

    //food
    $food = Food::find($food);
    if ($food) {
      //food_scan_update
      $rfs->update([
        'usr_predict' => $food->id,
        'found_by' => 'usr',
        'status' => 'edited',
//        'confidence' => 100,
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

        $rfs->rfs_ingredients_missing($food, $ingredients_missing, false);
      }
    }
    else {
      //no missing
      $rfs->rfs_ingredients_missing($food, [], false);
    }

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

    //notify main note
    $notify_note = false;
    if ($rfs->note !== $noted) {
      $notify_note = true;
    }

    $rfs->update([
      'food_id' => $food ? $food->id : 0,
      'note' => $noted,
    ]);

    if ($rbf_error) {
      if ($rfs->rbf_error != $this->_viewer->id) {
        $rfs->update([
          'rbf_error' => $this->_viewer->id,
        ]);
      }
    } else {
      $rfs->update([
        'rbf_error' => 0,
      ]);
    }

    //customer_requested
    if (!$customer_requested) {
      $rfs->update([
        'customer_requested' => 0,
      ]);
    }
    if (!$rfs->customer_requested && $customer_requested) {
      $rfs->update([
        'customer_requested' => $this->_viewer->id,
      ]);
    }

    //count_foods
    if (!$food_multi) {
      $rfs->update([
        'count_foods' => 0,
      ]);
    }
    if ($food_multi && $food_count > 1) {
      $rfs->update([
        'count_foods' => $food_count,
      ]);
    }

    //note_kitchen
    if (!$note_kitchen) {
      $rfs->update([
        'note_kitchen' => 0,
      ]);
    }
    if (!$rfs->note_kitchen && $note_kitchen) {

      if ($rfs->get_food()) {
        RestaurantFoodScan::where('deleted', 0)
//          ->where('restaurant_id', $rfs->restaurant_id)
          ->where('food_id', $rfs->get_food()->id)
          ->update([
            'note_kitchen' => 0,
          ]);

        $rfs->update([
          'note_kitchen' => $this->_viewer->id,
        ]);
      }
    }

    //report photo_update
    $photo = ReportPhoto::where('report_id', $row->id)
      ->where('restaurant_food_scan_id', $rfs->id)
//      ->where('reporting', 0)
      ->first();
    if ($photo) {
      $photo->update([
        'food_id' => $food ? $food->id : 0,
        'point' => $point,
        'status' => 'edited',
//        'note' => $noted,
      ]);
    }

    $row->re_count();

    if ($notify_note) {
      $rfs->update_main_note($this->_viewer);
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

  public function photo_clear(Request $request)
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



    $row->re_count();

    return response()->json([
      'status' => true,
      'item' => $row,
    ], 200);
  }

  public function photo_rfs(Request $request)
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

    $rbf_predictions = [];
    $rbf_versions = !empty($rfs->rbf_version) ? (array)json_decode($rfs->rbf_version, true) : [];

    //predictions
    $apid = (array)json_decode($rfs->rbf_api, true);
    $rbf_predictions = isset($apid['result']) && isset($apid['result']['predictions'])
      ? (array)$apid['result']['predictions'] : [];
    if (!count($rbf_predictions)) {
      //old
      $rbf_predictions = isset($apid['predictions']) && isset($apid['predictions'])
        ? (array)$apid['predictions'] : [];
    }

    //model2
    if ($rfs->rbf_model) {
      $api2 = (array)json_decode($rfs->rbf_api_2, true);
      $rbf_predictions = count($api2) ? $api2['predictions'] : [];
    }

    $photo = ReportPhoto::where('report_id', $row->id)
      ->where('restaurant_food_scan_id', $rfs->id)
      ->limit(1)
      ->first();
    if (!$photo) {
      return response()->json([
        'error' => 'Invalid data'
      ], 422);
    }

    $comments = [];
    $cmts = $rfs->get_comments();
    if (count($cmts)) {
      foreach ($cmts as $cmt) {
        $comments[] = [
          'user_name' => $cmt->owner->name,
          'user_noted' => $cmt->content,
          'created_at_1' => date('d/m/Y', strtotime($cmt->created_at)),
          'created_at_2' => date('H:i:s', strtotime($cmt->created_at)),
        ];
      }
    }

    $html = view('tastevn.htmls.item_report_photo_not_found')
      ->with('rfs', $rfs)
      ->with('predictions', $rbf_predictions)
      ->with('versions', $rbf_versions)
      ->with('model', $rfs->rbf_model)
      ->with('comments', $comments)
      ->render();

    $texts = $rfs->get_texts(['text_id_only' => 1]);
    $texts = count($texts) ? array_column($texts->toArray(), 'id') : [];

    //tester
    $ingredients = $rfs->get_ingredients_missing();
    $ingredients = count($ingredients) ? array_column($ingredients, 'id') : [];

    $photo_ids = ReportPhoto::select('restaurant_food_scan_id as id')
      ->where('report_id', $row->id)
      ->where('reporting', 0)
      ->where('status', 'failed')
      ->orderBy('restaurant_food_scan_id', 'asc')
      ->get();
    $photo_ids = count($photo_ids) ? array_column($photo_ids->toArray(), 'id') : [];

    return response()->json([
      'status' => true,
      'ids' => $photo_ids,
      'photo' => [
        'id' => $photo->id,
        'point' => number_format($photo->point, 1, '.', ''),
      ],
      'rfs' => [
        'id' => $rfs->id,
        'food_id' => $rfs->food_id,

        'note' => $rfs->note,
        'texts' => $texts,

        'comments' => $comments,

        'ingredients' => $ingredients,

        'rbf_error' => $rfs->rbf_error,
        'customer_requested' => $rfs->customer_requested,
        'note_kitchen' => $rfs->note_kitchen,
        'count_foods' => $rfs->count_foods,
      ],
      'html' => $html,
    ], 200);
  }
}
