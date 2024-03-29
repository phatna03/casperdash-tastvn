<?php

namespace App\Http\Controllers\tastevn\view;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;

use App\Api\SysCore;

use Validator;
use App\Models\User;
use App\Models\RestaurantFoodScan;
use App\Models\Food;
use App\Models\Ingredient;

//printer
//require __DIR__ . '/vendor/autoload.php';
use Mike42\Escpos\PrintConnectors\NetworkPrintConnector;
use Mike42\Escpos\PrintConnectors\FilePrintConnector;
use Mike42\Escpos\Printer;

class GuestController extends Controller
{
  public function login(Request $request)
  {
    if (Auth::user()) {
      return redirect('/admin');
    }

    if (url()->previous() != url()->current()){
      Redirect::setIntendedUrl(url()->previous());
//      var_dump(url()->previous());
    }

    $pageConfigs = [
      'myLayout' => 'blank',
      'pageAuth' => true,
    ];
    return view('tastevn.pages.auth.login', ['pageConfigs' => $pageConfigs]);
  }

  public function page_not_found()
  {
    $pageConfigs = [
      'myLayout' => 'blank'
    ];
    return view('tastevn.pages.page_not_found', ['pageConfigs' => $pageConfigs]);
  }

  public function printer(Request $request)
  {
    $values = $request->all();
    $api_core = new SysCore();

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

  public function printer_test(Request $request)
  {
    $values = $request->all();

    $pageConfigs = [
      'myLayout' => 'horizontal',
      'hasCustomizer' => false,
    ];

    return view('tastevn.pages.printer', ['pageConfigs' => $pageConfigs]);
  }

  public function s3_bucket_callback(Request $request)
  {
    $values = $request->post();

    $api_core = new SysCore();
    $api_core->s3_get_photos();

    return response()->json([
      'status' => true,
      'params' => $values,
    ]);
  }

  public function s3_bucket_get(Request $request)
  {
    $values = $request->all();

    $api_core = new SysCore();
    $api_core->s3_get_photos($values);

    return response()->json([
      'status' => true,
      'params' => $values,
    ]);
  }
}
