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

      //escpos
      $escpos .= "\n" . $row->get_restaurant()->name . " - " . $row->created_at . "\nMissing Ingredients:\n";

      $texts = array_filter(explode('&nbsp', $row->missing_texts));
      if(!empty($row->missing_texts) && count($texts)) {
        foreach($texts as $text) {
          if (!empty(trim($text))) {
            $escpos .= "+ " . trim($text) . "\n";
          }
        }
      }

      $escpos .= "\n\n";
    }

    //auto print
    $printers = array_filter(explode(';', Auth::user()->ips_printer));
//    echo '<pre>';var_dump($printers, $escpos);die;
    $file_log_path = 'public/logs/printer.log';
    $printed = true;

    if (count($printers)) {
      //multi
      foreach ($printers as $printer) {
        try {

          $connector = new NetworkPrintConnector($printer, 9100);
          $printer = new Printer($connector);

          $printer->text($escpos);
          $printer->cut();
          $printer->close();

        } catch (\Exception $e) {
//          var_dump($e->getMessage());
          Storage::prepend($file_log_path, 'PRINT_WITH_IP');
          Storage::prepend($file_log_path, 'MESSAGE_' . $e->getMessage());
          $printed = false;
        }
      }

    } else {

      //default
      try {

        $connector = new FilePrintConnector("php://stdout");
        $printer = new Printer($connector);
        $printer->text($escpos);
        $printer->cut();
        $printer->close();

      } catch (\Exception $e) {
//          var_dump($e->getMessage());
        Storage::prepend($file_log_path, 'PRINT_WITH_DEFAULT_PRINTER');
        Storage::prepend($file_log_path, 'MESSAGE_' . $e->getMessage());
        $printed = false;
      }
    }

    if ($printed) {
      return view('tastevn.pages.printer', []);
    }

    die('please configure your printer...');

    //old
//    $pageConfigs = [
//      'myLayout' => 'horizontal',
//      'hasCustomizer' => false,
//
//      'datas' => $datas,
//    ];
//
//    return view('tastevn.pages.print_food_scan', ['pageConfigs' => $pageConfigs]);
  }
}
