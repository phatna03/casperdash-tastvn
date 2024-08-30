<?php

namespace App\Http\Controllers\tastevn\auth;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
//lib
use Validator;
use App\Api\SysApp;
use App\Excel\ImportData;
//model
use App\Models\Food;
use App\Models\RestaurantParent;
use App\Models\Ingredient;
use App\Models\Restaurant;
use App\Models\KasItem;

class KasController extends Controller
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
    $invalid_roles = ['user', 'moderator'];
    if (in_array($this->_viewer->role, $invalid_roles)) {
      return redirect('error/404');
    }

    $pageConfigs = [
      'myLayout' => 'horizontal',
      'hasCustomizer' => false,
    ];

//    $this->_viewer->add_log([
//      'type' => 'view_listing_kas_food',
//    ]);

    return view('tastevn.pages.kas.foods', ['pageConfigs' => $pageConfigs]);
  }

  public function food_get(Request $request)
  {


    return response()->json([
      'status' => true,
    ]);
  }
}
