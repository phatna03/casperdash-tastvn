<?php

namespace App\Http\Controllers\tastvn;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;
use App\Models\User;

class Admin extends Controller
{
  public function __construct()
  {
//    $this->middleware(function ($request, $next) {
//      return $next($request);
//    });
//
//    $this->middleware('auth');
  }

  public function index()
  {
    $pageConfigs = [
      'myLayout' => 'horizontal',
      'hasCustomizer' => false,
    ];
    return view('tastvn.pages.admin', ['pageConfigs' => $pageConfigs]);
  }

  public function users()
  {
    $pageConfigs = [
      'myLayout' => 'horizontal',
      'hasCustomizer' => false,
    ];
    return view('tastvn.pages.users', ['pageConfigs' => $pageConfigs]);
  }

  public function restaurant_info()
  {
    $pageConfigs = [
      'myLayout' => 'horizontal',
      'hasCustomizer' => false,
    ];
    return view('tastvn.pages.restaurant_info', ['pageConfigs' => $pageConfigs]);
  }
}
