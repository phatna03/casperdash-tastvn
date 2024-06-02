<?php

namespace App\Http\Controllers\tastevn;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
//model
use App\Models\RestaurantFoodScan;

class GuideController extends Controller
{

  public function printer()
  {
    if (!Auth::user()) {
      return redirect('/login');
    }

    $pageConfigs = [
      'myLayout' => 'horizontal',
      'hasCustomizer' => false,
    ];
    return view('tastevn.pages.guide_printer', ['pageConfigs' => $pageConfigs]);
  }

  public function speaker()
  {
    if (!Auth::user()) {
      return redirect('/login');
    }

    $pageConfigs = [
      'myLayout' => 'horizontal',
      'hasCustomizer' => false,
    ];
    return view('tastevn.pages.guide_speaker', ['pageConfigs' => $pageConfigs]);
  }

}
