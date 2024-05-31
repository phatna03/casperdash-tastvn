<?php

namespace App\Http\Controllers\tastevn\auth;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
//lib
use Validator;
use App\Api\SysApp;
use App\Models\SysSetting;

class SettingController extends Controller
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

  /**
   * Display a listing of the resource.
   */
  public function index(Request $request)
  {
    $invalid_roles = ['moderator', 'user'];
    if (in_array($this->_viewer->role, $invalid_roles)) {
      return redirect('error/404');
    }

    $settings = [];

    $rows = SysSetting::all();
    if (count($rows)) {
      foreach ($rows as $row) {
        $settings[$row->key] = $row->value;
      }
    }

    $pageConfigs = [
      'myLayout' => 'horizontal',
      'hasCustomizer' => false,

      'settings' => $settings
    ];

    $this->_viewer->add_log([
      'type' => 'view_listing_setting',
    ]);

    return view('tastevn.pages.settings', ['pageConfigs' => $pageConfigs]);
  }

  public function update(Request $request)
  {
    $values = $request->all();

    $diffs['before'] = $this->_sys_app->get_log_settings();

    if (count($values)) {
      $this->save_settings($values);
    }

    $diffs['after'] = $this->_sys_app->get_log_settings();
    if (json_encode($diffs['before']) !== json_encode($diffs['after'])) {
      $this->_viewer->add_log([
        'type' => 'edit_setting',
        'params' => json_encode($diffs),
      ]);
    }

    return response()->json([
      'status' => true,
    ], 200);
  }


  protected function save_settings($settings = [])
  {
    foreach ($settings as $key => $value) {
      SysSetting::updateOrCreate([
        'key' => $key,
      ],
      [
        'value' => $value,
      ]);
    }
  }
}
