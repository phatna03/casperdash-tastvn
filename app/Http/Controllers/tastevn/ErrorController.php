<?php

namespace App\Http\Controllers\tastevn;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
//lib
use App\Api\SysApp;
use App\Api\SysRobo;
use App\Models\RestaurantFoodScan;

class ErrorController extends Controller
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

  public function index()
  {
    $pageConfigs = [
      'myLayout' => 'blank'
    ];
    return view('tastevn.pages.error_404', ['pageConfigs' => $pageConfigs]);
  }

  public function photo_check()
  {
    if (!$this->_viewer->is_dev()) {
      return redirect('error/404');
    }

    $pageConfigs = [
      'myLayout' => 'blank'
    ];

    return view('tastevn.pages.error_photo_check', ['pageConfigs' => $pageConfigs]);
  }

  public function photo_scan(Request $request)
  {
    $values = $request->post();

    $ids = [];
    $date = '2024-06-18';

    $select = RestaurantFoodScan::where('deleted', 0)
      ->whereIn('restaurant_id', [6])
      ->whereDate('time_photo', '>=', $date)
      ->where('sys_confidence', '<>', 10)
      ->orderBy('id', 'asc')
      ->limit(6);

    $rows = $select->get();
    if (count($rows)) {
      foreach ($rows as $row) {

        $row->model_api_1([
          'confidence' => SysRobo::_SCAN_CONFIDENCE,
          'overlap' => SysRobo::_SCAN_OVERLAP,
        ]);

        $row->predict_food([
          'notification' => false,
        ]);

        $row->update([
          'sys_confidence' => 10,
        ]);

        //time changed
        $ts_end = strtotime($row->time_photo) + (strtotime($row->time_end) - strtotime($row->time_scan));

        $row->update([
          'time_scan' => $row->time_photo,
          'time_end' => empty($row->time_end) ? NULL : date('Y-m-d H:i:s', $ts_end),
        ]);

        $ids[] = $row->id;
      }
    }

    $count = RestaurantFoodScan::where('deleted', 0)
      ->whereIn('restaurant_id', [6])
      ->whereDate('time_photo', '>=', $date)
      ->where('sys_confidence', '<>', 10)
      ->orderBy('id', 'asc')
      ->count();

    return response()->json([
      'ids' => $ids,
      'count' => $count,
    ]);
  }
}
