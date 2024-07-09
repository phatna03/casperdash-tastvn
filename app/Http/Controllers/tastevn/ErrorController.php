<?php

namespace App\Http\Controllers\tastevn;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
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
    if (!$this->_viewer->is_super_admin()) {
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
    $date = date('Y-m-d');
    $count = 0;


    $rows = RestaurantFoodScan::whereIn('restaurant_id', [5,6])
      ->where('rbf_api', '<>', NULL)
      ->whereDate('time_photo', '>=', '2024-06-24')
//      ->whereDate('time_photo', '<', '2024-06-10')
      ->where('sys_confidence', 0)
      ->where('deleted', 0)
      ->whereIn('status', ['checked', 'failed', 'edited'])
      ->orderBy('id', 'desc')
      ->limit(6)
      ->get();

    if (count($rows)) {
      foreach ($rows as $row) {



        $ids[] = $row->id;
      }
    }

    $count = RestaurantFoodScan::whereIn('restaurant_id', [5,6])
      ->where('rbf_api', '<>', NULL)
      ->whereDate('time_photo', '>=', '2024-06-24')
      ->where('sys_confidence', 201)
      ->where('deleted', 0)
      ->whereIn('status', ['checked', 'failed', 'edited'])
      ->count();

    return response()->json([
      'ids' => $ids,
      'count' => $count,
    ]);
  }
}
