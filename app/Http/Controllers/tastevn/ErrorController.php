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
    $date = '2024-06-09';

    $select = RestaurantFoodScan::where('deleted', 0)
      ->whereIn('restaurant_id', [5, 6])
      ->whereDate('time_photo', '>=', $date)
      ->where('sys_confidence', 1)
      ->orderBy('id', 'asc')
      ->limit(6);

    $rows = $select->get();
    if (count($rows)) {
      foreach ($rows as $row) {

        //step 1= photo check
        $img_url = $row->get_photo();

        if (!@getimagesize($img_url)) {

          $row->update([
            'deleted' => 1,
          ]);

          continue;
        }

        //jpg
        $ext = array_filter(explode('.', $img_url));
        if (count($ext) && $ext[count($ext) - 1] != 'jpg') {

          $row->update([
            'sys_confidence' => 2,
          ]);

          continue;
        }

        //step 2= photo scan
        $datas = SysRobo::photo_scan($img_url, [
          'confidence' => SysRobo::_SCAN_CONFIDENCE,
          'overlap' => SysRobo::_SCAN_OVERLAP,

          'version' => 33,
        ]);

        $row->update([
//          'time_scan' => date('Y-m-d H:i:s'),
          'status' => $datas['status'] ? 'scanned' : 'failed',
          'rbf_api' => $datas['status'] ? json_encode($datas['result']) : NULL,
        ]);

        //step 3= photo predict
        $row->predict_food([
          'notification' => false,
        ]);

        $row->update([
          'sys_confidence' => 0,
        ]);

        $ids[] = $row->id;
      }
    }

    $count = RestaurantFoodScan::where('deleted', 0)
      ->whereIn('restaurant_id', [5, 6])
      ->whereDate('time_photo', '>=', $date)
      ->where('sys_confidence', 1)
      ->orderBy('id', 'asc')
      ->count();

    return response()->json([
      'ids' => $ids,
      'count' => $count,
    ]);
  }
}
