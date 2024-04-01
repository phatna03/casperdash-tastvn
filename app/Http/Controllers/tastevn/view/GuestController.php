<?php

namespace App\Http\Controllers\tastevn\view;

use App\Http\Controllers\Controller;
use App\Models\Restaurant;
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

  public function printer_test(Request $request)
  {
    $values = $request->all();

    $pageConfigs = [
      'myLayout' => 'horizontal',
      'hasCustomizer' => false,
    ];

    return view('tastevn.pages.printer', ['pageConfigs' => $pageConfigs]);
  }

  public function guide_printer()
  {
    $pageConfigs = [
      'myLayout' => 'horizontal',
      'hasCustomizer' => false,
    ];
    return view('tastevn.pages.guide_printer', ['pageConfigs' => $pageConfigs]);
  }

  public function s3_bucket_callback(Request $request)
  {
    $values = $request->post();
    $api_core = new SysCore();

    $bucket = isset($values['bucket']) ? $values['bucket'] : NULL;
    $key = isset($values['key']) ? $values['key'] : NULL;

    if (!empty($bucket) && !empty($key)) {

      $restaurants = Restaurant::where('deleted', 0)
          ->where('s3_bucket_name', $bucket)
          ->where('s3_bucket_address', '<>', NULL)
          ->where('s3_checking', 0)
          ->get();
      if (count($restaurants)) {
        foreach ($restaurants as $restaurant) {
          $api_core->s3_get_photos([
            'restaurant_id' => $restaurant->id,
          ]);
        }
      }
    }
    else {

      $api_core->s3_get_photos();
    }

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
