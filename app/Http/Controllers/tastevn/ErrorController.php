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

  public function photo_rescan(Request $request)
  {
    $values = $request->post();

    $ids = [];
    $date = '2024-06-18';
    $count = 0;



    return response()->json([
      'ids' => $ids,
      'count' => $count,
    ]);
  }
}
