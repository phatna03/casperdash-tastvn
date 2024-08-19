<?php

namespace App\Http\Controllers\tastevn\auth;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
//lib
use Validator;
use App\Api\SysCore;
use App\Api\SysRobo;
use App\Jobs\PhotoUpload;
//model
use App\Models\User;
use App\Models\Restaurant;
use App\Models\RestaurantAccess;
use App\Models\RestaurantFoodScan;
use App\Models\Food;
use App\Models\FoodIngredient;
use App\Models\Ingredient;
use App\Models\RestaurantParent;

class RoboflowController extends Controller
{
  protected $_viewer = null;

  public function __construct()
  {
    $this->middleware(function ($request, $next) {

      $this->_viewer = Auth::user();

      return $next($request);
    });

    $this->middleware('auth');
  }

  public function index(Request $request)
  {
    $values = $request->all();

    $invalid_roles = ['user'];
    if (in_array($this->_viewer->role, $invalid_roles)) {
      return redirect('error/404');
    }

    $debug = isset($values['debug']) ? (int)$values['debug'] : 0;
    $img_1024 = isset($values['img_1024']) ? (int)$values['img_1024'] : 0;

    $food = Food::where('deleted', 0)
      ->orderByDesc('id')
      ->limit(1)
      ->first();

    $pageConfigs = [
      'myLayout' => 'horizontal',
      'hasCustomizer' => false,

      'img_1024' => $img_1024,
      'debug' => $debug,
    ];

    $this->_viewer->add_log([
      'type' => 'view_modal_testing',
    ]);

    return view('tastevn.pages.roboflow', ['pageConfigs' => $pageConfigs]);
  }

  public function detect(Request $request)
  {
    $values = $request->all();

    //model 1
    $api_key = SysCore::get_sys_setting('rbf_api_key');
    $dataset = SysCore::str_trim_slash(SysCore::get_sys_setting('rbf_dataset_scan'));
    $version = SysCore::get_sys_setting('rbf_dataset_ver');

    $dataset = isset($values['dataset']) ? $values['dataset'] : $dataset;
    $version = isset($values['version']) ? $values['version'] : $version;

    if (empty($api_key) || empty($dataset) || empty($version)) {
      return response()->json([
        'status' => false,
        'error' => "Invalid config...",
      ], 400);
    }

//    echo '<pre>';
    $confidence = isset($values['confidence']) ? $values['confidence'] : SysRobo::_RBF_CONFIDENCE;
    $overlap = isset($values['overlap']) ? $values['overlap'] : SysRobo::_RBF_OVERLAP;
    $max_objects = isset($values['max_objects']) ? $values['max_objects'] : SysRobo::_RBF_MAX_OBJECTS;
    $debug = isset($values['debug']) ? (bool)$values['debug'] : false;
    $img_1024 = isset($values['img_1024']) ? (int)$values['img_1024'] : 0;

    $status = false;
    $datas = [];
    $img_url = NULL;
    $ingredients = [];
    $foods = [];
    $predictions = [];

    //img upload
    $img_name = 'img_' . time();
    $img_file = $request->file('image');
    if (!empty($img_file)) {
      foreach ($img_file as $file) {

        $img_path = "/roboflow/modal_testing/user_{$this->_viewer->id}/";
        $file_path = public_path($img_path);
        $file_path = SysCore::os_slash_file($file_path);
        if (!file_exists($file_path)) {
          mkdir($file_path, 0777, true);
        }

        $file_name = $file->getClientOriginalName();
        $file_ext = $file->getClientOriginalExtension();

        $photo_name = $img_name . '.' . $file_ext;
        $file->move(public_path($img_path), $photo_name);

        //roboflow
        $img_url = "https://s3.ap-southeast-1.amazonaws.com/cargo.tastevietnam.asia/58-5b-69-19-ad-83/SENSOR/1/2024-07-08/19/SENSOR_2024-07-08-19-47-41-791_847.jpg";
        if (App::environment() != 'local') {
          $img_url = url($img_path) . '/' . $photo_name;
        }

        if ($img_1024) {
          $img_url = SysRobo::photo_1024($img_url);
        }

        $datas = SysRobo::photo_scan([
          'img_url' => $img_url,

          'api_key' => $api_key,
          'dataset' => $dataset,
          'version' => $version,

          'confidence' => $confidence,
          'overlap' => $overlap,
          'max_objects' => $max_objects,

          'debug' => $debug,

          'type' => 'modal_testing',
        ]);
      }

      $no_data = false;
      if (!count($datas) || !$datas['status']
        || ($datas['status'] && (!isset($datas['result']['predictions'])) || !count($datas['result']['predictions']))) {
        $no_data = true;
      }

      if (!$no_data) {

        $status = true;
        $predictions = $datas['result']['predictions'];
        if (count($predictions)) {

          //ingredients
          $ingredients_found = SysRobo::ingredients_compact($predictions);
          if (count($ingredients_found)) {
            foreach ($ingredients_found as $temp) {
              $ing = Ingredient::find((int)$temp['id']);
              if ($ing) {
                $ingredients[] = [
                  'quantity' => $temp['quantity'],
                  'title' => !empty($ing['name_vi']) ? $ing['name'] . ' - ' . $ing['name_vi'] : $ing['name'],
                ];
              }
            }
          }

          //foods
          foreach ($predictions as $prediction) {
            $prediction = (array)$prediction;
            $confidence = (int)($prediction['confidence'] * 100);

            $food = Food::whereRaw('LOWER(name) LIKE ?', strtolower(trim($prediction['class'])))
              ->first();
            if ($food) {
              $foods[] = [
                'confidence' => $confidence,
                'title' => $food->name,
              ];
            }
          }

        }
      }
    }

    return response()->json([
      'status' => $status,

      'img_1024' => $img_1024,
      'img_url' => $img_url,
      'datas' => $datas,

      'predictions' => $predictions,
      'ingredients' => $ingredients,
      'foods' => $foods,

    ], 200);
  }

  public function retraining(Request $request)
  {
    $values = $request->post();
    $ids = isset($values['items']) ? (array)$values['items'] : [];
//    echo '<pre>';var_dump($ids);die;

    if (count($ids)) {

      foreach ($ids as $id) {
        $row = RestaurantFoodScan::find((int)$id);
        if (!$row) {
          continue;
        }

        $row->update([
          'rbf_retrain' => 1,
        ]);
      }

      dispatch(new PhotoUpload());
    }

    return response()->noContent();
  }

  public function restaurant_food_get(Request $request)
  {
    $values = $request->post();

    $validator = Validator::make($values, [
      'item' => 'required',
    ]);
    if ($validator->fails()) {
      return response()->json($validator->errors(), 422);
    }

    $restaurant_parent_id = isset($values['item']) ? (int)$values['item'] : 0;
    $restaurant_parent = RestaurantParent::find($restaurant_parent_id);
    if (!$restaurant_parent) {
      return response()->json([
        'error' => 'Invalid data'
      ], 404);
    }

    $items = $restaurant_parent->get_foods([
      'select_data' => 'food_only',
    ]);

    return response()->json([
      'status' => true,
      'items' => count($items) ? $items->toArray() : [],
    ]);
  }

  public function food_get_info(Request $request)
  {
    $values = $request->post();

    $validator = Validator::make($values, [
      'item' => 'required',
    ]);
    if ($validator->fails()) {
      return response()->json($validator->errors(), 422);
    }

    $restaurant_parent_id = isset($values['restaurant_parent_id']) ? (int)$values['restaurant_parent_id'] : 0;
    $restaurant_parent = RestaurantParent::find($restaurant_parent_id);

    //invalid
    $row = Food::find((int)$values['item']);
    if (!$row || !$restaurant_parent) {
      return response()->json([
        'error' => 'Invalid item'
      ], 422);
    }

    $food_photo = $row->get_photo([
      'restaurant_parent_id' => $restaurant_parent_id
    ]);

    //info
    $html_info = view('tastevn.htmls.item_food_roboflow')
      ->with('ingredients', $row->get_ingredients([
        'restaurant_parent_id' => $restaurant_parent_id,
      ]))
      ->with('recipes', $row->get_recipes([
        'restaurant_parent_id' => $restaurant_parent_id,
      ]))
      ->render();

    return response()->json([
      'food_name' => '[' . $restaurant_parent->name . '] ' . $row->name,
      'food_photo' => $food_photo,

      'html_info' => $html_info,
    ]);
  }

}
