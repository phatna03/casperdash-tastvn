<?php

namespace App\Http\Controllers\tastevn\view;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;

use Carbon\Carbon;
use App\Api\SysCore;

use Maatwebsite\Excel\Facades\Excel;
use App\Excel\ExportRfs;

use Validator;
use App\Models\User;
use App\Models\Restaurant;
use App\Models\RestaurantFoodScan;
use App\Models\Food;
use App\Models\Ingredient;
use App\Models\KasWebhook;

//printer
//require __DIR__ . '/vendor/autoload.php';
use Mike42\Escpos\PrintConnectors\NetworkPrintConnector;
use Mike42\Escpos\PrintConnectors\FilePrintConnector;
use Mike42\Escpos\Printer;

class GuestController extends Controller
{
  protected const _DEBUG = true;
  protected const _DEBUG_LOG_FILE_CALLBACK = 'public/logs/s3_callback.log';

  public function login(Request $request)
  {
    if (Auth::user()) {
      return redirect('/admin');
    }

    if (url()->previous() != url()->current()) {
      Redirect::setIntendedUrl(url()->previous());
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

  public function excel(Request $request)
  {
    $values = $request->all();

    $date = isset($values['date']) ? $values['date'] : date('Y-m-d');
    $dated = isset($values['date']) ? $values['date'] : date('Y_m_d');

    $rows = RestaurantFoodScan::where('deleted', 0)
      ->whereIn('status', ['checked', 'failed'])
      ->where('total_seconds', '>', 0)
      ->where('rbf_api', '<>', NULL)
      ->whereDate('time_photo', $date)
      ->orderBy('id', 'desc')
      ->get();

    $items = [];
    if (count($rows)) {
      foreach ($rows as $row) {

        $time1 = (float)date('s', strtotime($row->time_scan) - strtotime($row->time_photo));
        $time2 = (float)$row->total_seconds;
        $time4 = !empty($row->time_end)
          ? (float)date('s', strtotime($row->time_end) - strtotime($row->time_scan)) : 0;
        $time5 = !empty($row->time_end)
          ? $time1 + $time2 + $time4 : 0;

        $items[] = [
          'photo_url' => $row->photo_url,
          'time_photo' => $row->time_photo,
          'time_scan' => $row->time_scan,
          'time_end' => $row->time_end,
          'updated_at' => date('Y-m-d H:i:s', strtotime($row->updated_at)),

          'time_1' => $time1,
          'time_2' => $time2,
          'time_3' => $time1 + $time2,

          'time_4' => $time4,
          'time_5' => $time5,
        ];
      }
    }

    if (!count($items)) {
      die('no data');
    }
//    echo '<pre>';var_dump($items);die;

    $excel = new ExportRfs();
    $excel->setItems($items);
    return Excel::download($excel, 'export_data_' . $dated . '.xlsx');
  }

  public function guide_printer()
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

  public function guide_speaker()
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

  public function s3_bucket_callback(Request $request)
  {
    $values = $request->post();
    $api_core = new SysCore();

    $bucket = isset($values['bucket']) ? $values['bucket'] : NULL;
    $key = isset($values['key']) ? $values['key'] : NULL;

    $this::_DEBUG ? Storage::append($this::_DEBUG_LOG_FILE_CALLBACK, 'TODO_AT_' . date('d_M_Y_H_i_s')) : $api_core->log_failed();

    if (!empty($bucket) && !empty($key)) {

      $sensor = explode('/SENSOR/', $key);
      $restaurant = Restaurant::where('deleted', 0)
        ->where('s3_bucket_name', $bucket)
        ->where('s3_bucket_address', 'LIKE', "%{$sensor[0]}%")
        ->orderBy('id', 'desc')
        ->limit(1)
        ->first();

      $this::_DEBUG ? Storage::append($this::_DEBUG_LOG_FILE_CALLBACK, 'BUCKET_' . $bucket) : $api_core->log_failed();
      $this::_DEBUG ? Storage::append($this::_DEBUG_LOG_FILE_CALLBACK, 'KEY_' . $key) : $api_core->log_failed();

      if ($restaurant) {

        $this::_DEBUG ? Storage::append($this::_DEBUG_LOG_FILE_CALLBACK, 'RESTAURANT_' . $restaurant->id . '_' . $restaurant->name) : $api_core->log_failed();

        //step 1= photo get
        //settings
        $s3_region = $api_core->get_setting('s3_region');
        $s3_api_key = $api_core->get_setting('s3_api_key');
        $s3_api_secret = $api_core->get_setting('s3_api_secret');
        $photo_URL = "https://s3.{$s3_region}.amazonaws.com/{$bucket}/{$key}";
        //valid photo
        if (@getimagesize($photo_URL)) {

          $this::_DEBUG ? Storage::append($this::_DEBUG_LOG_FILE_CALLBACK, 'VALID_' . $photo_URL) : $api_core->log_failed();

          //check exist
          $row = RestaurantFoodScan::where('deleted', 0)
            ->where('restaurant_id', $restaurant->id)
            ->where('photo_name', $key)
            ->first();

          if (!$row) {
            $time_photo = date('Y-m-d H:i:s');
            $exts = explode('.', $key);

            $row = RestaurantFoodScan::create([
              'restaurant_id' => $restaurant->id,
              'photo_url' => $photo_URL,
              'photo_name' => $key,
              'photo_ext' => $exts[1],
              'status' => 'new',
              'time_photo' => $time_photo,
            ]);
          }

          if ($restaurant->rbf_scan) {
            //step 2= photo scan
            $rbf_dataset = $api_core->get_setting('rbf_dataset_scan');
            $rbf_api_key = $api_core->get_setting('rbf_api_key');

            // URL for Http Request
            $url = "https://detect.roboflow.com/" . $rbf_dataset
              . "?api_key=" . $rbf_api_key
              . "&image=" . urlencode($photo_URL);

            // Setup + Send Http request
            $options = array(
              'http' => array(
                'header' => "Content-type: application/x-www-form-urlencoded\r\n",
                'method' => 'POST'
              ));

            $context = stream_context_create($options);
            $result = file_get_contents($url, false, $context);
            if (!empty($result)) {
              $result = (array)json_decode($result);
            }

            //valid data
            if (count($result)) {

              $row->update([
                'status' => 'scanned',
                'time_scan' => date('Y-m-d H:i:s'),
                'rbf_api' => json_encode($result),
              ]);

            } else {

              $row->update([
                'status' => 'failed',
                'time_scan' => date('Y-m-d H:i:s'),
              ]);
            }

            //step 3= photo predict
            $row->predict_food();
          }
        }
      }

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

  public function kas_cart_info(Request $request)
  {
    $values = $request->post();

    $rows = KasWebhook::where('type', 'cart_info')
//      ->where('created_at', '>=', Carbon::now()->subMinutes(1)->toDateTimeString())
      ->where('params', json_encode($values))
      ->get();
    if (count($rows) > 1) {
      return response()->json([
        'error' => 'No spam request.',
      ], 404);
    }

    KasWebhook::create([
      'type' => 'cart_info',
      'params' => json_encode($values),
    ]);

    $restaurant_id = isset($values['restaurant_id']) && !empty($values['restaurant_id']) ? (int)$values['restaurant_id'] : 0;
    if (!$restaurant_id) {
      return response()->json([
        'error' => 'No restaurant ID found.',
      ], 404);
    }

    $items = isset($values['items']) && !empty($values['items']) && count($values['items']) ? (array)$values['items'] : [];
    if (!count($items)) {
      return response()->json([
        'error' => 'No cart items found.',
      ], 404);
    }

    $valid_cart = true;
    foreach ($items as $item) {
      $item_id = isset($item['item_id']) && !empty($values['item_id']) ? (int)$values['item_id'] : 0;
      $item_quantity = isset($item['quantity']) && !empty($values['quantity']) ? (int)$values['quantity'] : 1;
      $item_code = isset($item['item_code']) && !empty($values['item_code']) ? trim($values['item_code']) : NULL;
      $item_name = isset($item['item_name']) && !empty($values['item_name']) ? trim($values['item_name']) : NULL;
      $item_status = isset($item['status']) && !empty($values['status']) ? trim($values['status']) : NULL;
      $item_note = isset($item['note']) && !empty($values['note']) ? trim($values['note']) : NULL;

      if (empty($item_id) || empty($item_code) || empty($item_name) || empty($item_status)) {
        $valid_cart = false;
        break;
      }
    }
    if (!$valid_cart) {
      return response()->json([
        'error' => 'Invalid cart item parameter.',
      ], 404);
    }

    return response()->json([
      'status' => true,
    ], 200);
  }
}
