<?php

namespace App\Http\Controllers\tastevn\auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
//lib
use Validator;
use App\Api\SysApp;
use App\Models\Log;

class LogController extends Controller
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
    $invalid_roles = ['user', 'moderator'];
    if (in_array($this->_viewer->role, $invalid_roles)) {
      return redirect('error/404');
    }

    $pageConfigs = [
      'myLayout' => 'horizontal',
      'hasCustomizer' => false,

      'options_type' => $this->_sys_app->get_log_types(),
      'options_item' => $this->_sys_app->get_log_items(),
    ];

    $this->_viewer->add_log([
      'type' => 'view_listing_log',
    ]);

    return view('tastevn.pages.logs', ['pageConfigs' => $pageConfigs]);
  }

}
