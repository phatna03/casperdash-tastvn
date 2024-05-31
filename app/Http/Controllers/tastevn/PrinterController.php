<?php

namespace App\Http\Controllers\tastevn;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
//model
use App\Models\RestaurantFoodScan;

class PrinterController extends Controller
{

  public function index(Request $request)
  {
    $values = $request->all();

    $user = Auth::user();
    if (!$user) {
      return response()->json([
        'error' => 'Invalid user'
      ], 422);
    }

    $ids = isset($values['ids']) ? array_filter(explode(',', $values['ids'])) : [];
    if (!count($ids)) {
      return response()->json([
        'error' => 'Invalid item'
      ], 422);
    }

    $datas = [];
    $escpos = '';

    foreach ($ids as $id) {

      $row = RestaurantFoodScan::find((int)$id);
      if (!$row) {
        continue;
      }

      $datas[] = [
        'restaurant' => $row->get_restaurant(),
        'item' => $row,
      ];
    }

    $pageConfigs = [
      'myLayout' => 'horizontal',
      'hasCustomizer' => false,

      'datas' => $datas,
    ];

    return view('tastevn.pages.print_food_scan', ['pageConfigs' => $pageConfigs]);
  }

  public function test(Request $request)
  {
    $values = $request->all();

    $pageConfigs = [
      'myLayout' => 'horizontal',
      'hasCustomizer' => false,
    ];

    return view('tastevn.pages.printer', ['pageConfigs' => $pageConfigs]);
  }


}
