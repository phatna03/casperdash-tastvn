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
use Illuminate\Support\Facades\DB;

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

  public function photo_rescan(Request $request)
  {
    $values = $request->post();

    $ids = [];
    $date = '2024-06-18';
    $count = 0;

    $rows = RestaurantFoodScan::where('deleted', 0)
      ->whereIn('restaurant_id', [9, 10])
      ->whereDate('time_photo', '>=', '2024-06-10')
      ->where('sys_confidence', 0)
      ->orderBy('id', 'asc')
      ->limit(8)
      ->get();

    if (count($rows)) {
      foreach ($rows as $row) {

        DB::beginTransaction();

        try {
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

          $row->update([
            'sys_confidence' => 10,
          ]);

          $ids[] = $row->id;

          DB::commit();

        } catch (\Exception $exception) {

          DB::rollBack();

          $row->update([
            'sys_confidence' => 11,
          ]);
        }
      }
    }

    $count = RestaurantFoodScan::where('deleted', 0)
      ->whereIn('restaurant_id', [9, 10])
      ->whereDate('time_photo', '>=', '2024-06-10')
      ->where('sys_confidence', 0)
      ->count();

    return response()->json([
      'ids' => $ids,
      'count' => $count,
    ]);
  }
}
