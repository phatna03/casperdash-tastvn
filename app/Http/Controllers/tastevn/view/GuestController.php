<?php

namespace App\Http\Controllers\tastevn\view;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;

use Carbon\Carbon;
use App\Api\SysCore;

use Maatwebsite\Excel\Facades\Excel;
use App\Excel\ExportRfs;
use App\Excel\ExportFoodReport;

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

  public function excel1(Request $request)
  {
    $values = $request->all();

    $date = isset($values['date']) ? $values['date'] : date('Y-m-d');
    $dated = isset($values['date']) ? $values['date'] : date('Y_m_d');

    $restaurant_id = isset($values['restaurant_id']) ? (int)$values['restaurant_id'] : 0;
    $restaurants = isset($values['restaurants']) ? $values['restaurants'] : NULL;
    $restaurants = !empty($restaurants) ? array_filter(explode(',', $restaurants)) : [];

    $select = RestaurantFoodScan::where('deleted', 0)
      ->whereIn('status', ['checked', 'edited', 'failed'])
      ->where('total_seconds', '>', 0)
      ->where('rbf_api', '<>', NULL)
      ->whereDate('time_photo', $date)
      ->orderBy('id', 'desc');

    if ($restaurant_id) {
      $select->where('restaurant_id', $restaurant_id);
    } elseif (count($restaurants)) {
      $select->whereIn('restaurant_id', $restaurants);
    }

    $items = [];
    $rows = $select->get();
    if (count($rows)) {
      foreach ($rows as $row) {

//        58-5b-69-15-cd-2b/SENSOR/1/2024-04-22/9/SENSOR1_2024-04-22-09-32-39-229_087.jpg
        $photo_names = explode('SENSOR1_', $row->photo_name);
        if (count($photo_names) < 2) {
          $photo_names = explode('SENSOR_', $row->photo_name);
        }
        $temps = array_filter(explode('-', $photo_names[1]));
        $time_photo = date($temps[0] . '-' . $temps[1] . '-' . $temps[2] . ' ' . $temps[3] . ':' . $temps[4] . ':' . $temps[5]);

        $time_s3 = date('Y-m-d H:i:s', strtotime($row->time_photo));
        $time_web = date('Y-m-d H:i:s', strtotime($row->created_at));
        $time_scan = date('Y-m-d H:i:s', strtotime($row->time_scan));
        $time_end = !empty($row->time_end) ? date('Y-m-d H:i:s', strtotime($row->time_end)) : '';

        $time1 = (float)date('s', strtotime($time_s3) - strtotime($time_photo))
          ? (float)date('s', strtotime($time_s3) - strtotime($time_photo)) : 0;

        $time2 = (float)date('s', strtotime($time_web) - strtotime($time_s3))
          ? (float)date('s', strtotime($time_web) - strtotime($time_s3)) : 0;

        $time3 = (float)date('s', strtotime($time_scan) - strtotime($time_web))
          ? (float)date('s', strtotime($time_scan) - strtotime($time_web)) : 0;

        $time4 = (float)date('s', strtotime($time_end) - strtotime($time_scan))
          ? (float)date('s', strtotime($time_end) - strtotime($time_scan)) : 0;

        $time5 = !empty($row->time_end)
          ? $time1 + $time2 + $time3 + $time4 : 0;

        $items[] = [
          'id' => $row->id,
          'photo_url' => $row->photo_url,

          'restaurant_name' => $row->get_restaurant()->name,
          'error_s3' => strtotime($time_s3) < strtotime($time_photo) ? 'device time error' : '',

          'time_photo' => $time_photo,
          'time_s3' => $time_s3,
          'time_web' => $time_web,
          'time_scan' => $time_scan,
          'time_end' => $time_end,

          'time_1' => $time1,
          'time_2' => $time2,
          'time_3' => $time3,
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

  public function excel2(Request $request)
  {
    $values = $request->all();
    $api_core = new SysCore();

    $date = isset($values['date']) ? $values['date'] : date('Y-m-d');
    $dated = isset($values['date']) ? $values['date'] : date('Y_m_d');

    $restaurant_id = isset($values['restaurant_id']) ? (int)$values['restaurant_id'] : 0;

    $tblFood = app(Food::class)->getTable();
    $tblRfs = app(RestaurantFoodScan::class)->getTable();

    $select = Food::query($tblFood)
//      ->distinct()
      ->select("$tblFood.id", "$tblFood.name")
      ->selectRaw("COUNT($tblRfs.id) as total_photos")
      ->selectRaw("COUNT($tblRfs.missing_ids) as photo_missing")
      ->leftJoin($tblRfs, "$tblFood.id", '=', "$tblRfs.food_id")
      ->where("$tblFood.deleted", 0)
      ->where("$tblRfs.deleted", 0)
      ->whereIn("$tblRfs.status", ['checked', 'edited'])
      ->whereDate("$tblRfs.time_photo", $date)
      ->groupBy("$tblFood.id", "$tblFood.name")
      ->orderByRaw("TRIM(LOWER($tblFood.name))");

    if ($restaurant_id) {
      $select->where("$tblRfs.restaurant_id", $restaurant_id);
    }

//    var_dump($api_core->parse_to_query($select));die;

    $items = $select->get();

    if (!count($items)) {
      die('no data');
    }
//    echo '<pre>';var_dump($items);die;

    $excel = new ExportFoodReport();
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

    $file_log = $api_core::_DEBUG_LOG_FILE_S3_CALLBACK;
    $api_core::_DEBUG ? Storage::append($file_log, '============================================') : $api_core->log_failed();
    $api_core::_DEBUG ? Storage::append($file_log, 'CRON_RUN_AT: ' . date('H:i:s')) : $api_core->log_failed();
    $api_core::_DEBUG ? Storage::append($file_log, 'WITH_PARAMS: ' . json_encode($values)) : $api_core->log_failed();

    $s3_bucket = isset($values['bucket']) ? $values['bucket'] : NULL; //bucket: 'cargo.tastevietnam.asia'
    $s3_photo_name = isset($values['key']) ? $values['key'] : NULL; //key: '58-5b-69-19-ad-67/SENSOR/1/2024-05-02/'
    $rbf_api_js = isset($values['rbf_api_js']) ? $values['rbf_api_js'] : NULL;

    if (empty($s3_bucket) || empty($s3_photo_name)) {
      $api_core::_DEBUG ? Storage::append($file_log, 'INVALID_PARAMS') : $api_core->log_failed();
      return false;
    }

    $restaurant = NULL;
    $temps = explode('/SENSOR/', $s3_photo_name);
    $s3_address = count($temps) ? $temps[0] : NULL;

    if (!empty($s3_address)) {
      $restaurant = Restaurant::where('deleted', 0)
        ->where('s3_bucket_name', $s3_bucket)
        ->where('s3_bucket_address', 'LIKE', "%{$s3_address}%")
        ->orderBy('id', 'desc')
        ->limit(1)
        ->first();
    }

    if (!$restaurant) {
      $api_core::_DEBUG ? Storage::append($file_log, 'EMPTY_RESTAURANT') : $api_core->log_failed();
      return false;
    }

    $api_core::_DEBUG ? Storage::append($file_log, '============================================') : $api_core->log_failed();
    $api_core::_DEBUG ? Storage::append($file_log, 'CRON_RUN_AT: ' . date('H:i:s')) : $api_core->log_failed();
    $api_core::_DEBUG ? Storage::append($file_log, 'WITH_PARAMS: ' . json_encode($values)) : $api_core->log_failed();

    //settings
    $s3_region = $api_core->get_setting('s3_region');
    $s3_api_key = $api_core->get_setting('s3_api_key');
    $s3_api_secret = $api_core->get_setting('s3_api_secret');

    if (empty($s3_region) || empty($s3_api_key) || empty($s3_api_secret)) {
      $api_core::_DEBUG ? Storage::append($file_log, 'INVALID_CONFIG') : $api_core->log_failed();
      return false;
    }

    DB::beginTransaction();
    try {

      $s3_photo_url = "https://s3.{$s3_region}.amazonaws.com/{$s3_bucket}/{$s3_photo_name}";
      $s3_photo_ext = explode('.', $s3_photo_name);

      $api_core::_DEBUG ? Storage::append($file_log, 'PHOTO_NAME_' . $s3_photo_name) : $api_core->log_failed();
      $api_core::_DEBUG ? Storage::append($file_log, 'PHOTO_URL_' . $s3_photo_url) : $api_core->log_failed();

      //valid photo
      if (@getimagesize($s3_photo_url)) {

        //check exist
        $row = RestaurantFoodScan::where('restaurant_id', $restaurant->id)
          ->where('photo_name', $s3_photo_name)
          ->first();

        if (!$row) {

          $api_core::_DEBUG ? Storage::append($file_log, 'PHOTO_URL_VALID') : $api_core->log_failed();

          $row = $restaurant->photo_save([
            'photo_url' => $s3_photo_url,
            'photo_name' => $s3_photo_name,
            'photo_ext' => $s3_photo_ext[1],
            'time_photo' => date('Y-m-d H:i:s'),
          ]);

          $restaurant->photo_scan($row);
        }
      }

      DB::commit();

    } catch (\Exception $e) {

      DB::rollBack();

      $api_core->bug_add([
        'type' => 's3_callback',
        'line' => $e->getLine(),
        'file' => $e->getFile(),
        'message' => $e->getMessage(),
        'params' => json_encode($e),
      ]);
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
