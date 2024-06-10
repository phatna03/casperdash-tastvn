<?php

namespace App\Http\Controllers\tastevn\auth;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
//lib
use Validator;
use App\Api\SysApp;
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
use App\Models\RestaurantFood;
use App\Models\RestaurantParent;

class RoboflowController extends Controller
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
    $values = $request->all();

    $invalid_roles = ['user'];
    if (in_array($this->_viewer->role, $invalid_roles)) {
      return redirect('error/404');
    }

    $debug = isset($values['debug']) ? (int)$values['debug'] : 0;

    $food = Food::where('deleted', 0)
      ->orderByDesc('id')
      ->limit(1)
      ->first();

    $pageConfigs = [
      'myLayout' => 'horizontal',
      'hasCustomizer' => false,

      'debug' => $debug
    ];

    $this->_viewer->add_log([
      'type' => 'view_modal_testing',
    ]);

    return view('tastevn.pages.roboflow', ['pageConfigs' => $pageConfigs]);
  }

  public function detect(Request $request)
  {
    $status = false;
    $values = $request->all();

    $rbf_dataset = $this->_sys_app->get_setting('rbf_dataset_scan');
    $rbf_api_key = $this->_sys_app->get_setting('rbf_api_key');

    if (empty($rbf_dataset) || empty($rbf_dataset)) {
      return response()->json([
        'status' => false,
        'error' => "Please contact admin for config valid settings!",
      ], 400);
    }

    $food = null;
    $food_predict = null;
    $food_photo = url('custom/img/no_photo.png');
    $ingredients_found = [];
    $sys_food_predicts = [];
    $sys_food_predict = [];

    $rbf_food = null;
    $rbf_food_id = 0;
    $rbf_food_name = NULL;
    $rbf_food_confidence = 0;
    $rbf_ingredients_found = [];
    $rbf_ingredients_missing = [];
    $rbf_food_found = [];

    $sys_food = NULL;
    $sys_food_id = 0;
    $sys_food_name = NULL;
    $sys_food_confidence = 0;
    $sys_ingredients_found = [];
    $sys_ingredients_missing = [];

    //img upload
    $img = 'roboflow_detect';
    $imgFILE = $request->file('image');

    $datas = [];
    $result = [];
    $img_url = '';

    if (!empty($imgFILE)) {

      foreach ($imgFILE as $file) {

        $folder = time();

        $pathStr = "/roboflow/test/{$folder}/";
        $path = public_path($pathStr);
        //os
        if (strtoupper(substr(PHP_OS, 0, 3)) == 'WIN') {
          $path = str_replace('/', '\\', $path);
        }
        if (!file_exists($path)) {
          mkdir($path, 0777, true);
        }

        $fileName = $file->getClientOriginalName();
        $fileExt = $file->getClientOriginalExtension();

        $photoName = $img . '.' . $fileExt;
        $photoPath = $pathStr . $photoName;
        $file->move(public_path($pathStr), $photoName);

        //rotate image mobile upload
        $storagePath = public_path($photoPath);

        //roboflow
        $img_url = "https://s3.ap-southeast-1.amazonaws.com/cargo.tastevietnam.asia/58-5b-69-19-ad-83/SENSOR/1/2024-06-06/21/SENSOR_2024-06-06-21-21-34-723_176.jpg";
        if (App::environment() == 'production') {
          $img_url = url("roboflow/test") . "/{$folder}/" . $photoName;
        }

        //step 2= photo scan
        $datas = SysRobo::photo_scan($img_url, [
          'confidence' => SysRobo::_SCAN_CONFIDENCE,
          'overlap' => SysRobo::_SCAN_OVERLAP,
        ]);
      }

      if ($datas['status']) {

        $status = true;
        $predictions = $datas['result']['predictions'];
        if (count($predictions)) {

          //ingredients
          $ingredients_found = SysRobo::ingredients_compact($predictions);
          if (count($ingredients_found)) {
            foreach ($ingredients_found as $temp) {
              $ing = Ingredient::find((int)$temp['id']);
              if ($ing) {
                $rbf_ingredients_found[] = [
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
              $rbf_food_found[] = [
                'confidence' => $confidence,
                'title' => $food->name,
              ];
            }
          }

        }
      }
    }


    $data = [
      'food' => [
        'photo' => $food_photo,

        'predictions' => $predictions,
      ],
      'rbf' => [
        'food_id' => $rbf_food_id,
        'food_name' => $rbf_food_name,
        'food_confidence' => $rbf_food_confidence,

        'foods_found' => $rbf_food_found,
        'ingredients_found' => $rbf_ingredients_found,
        'ingredients_missing' => $rbf_ingredients_missing,
      ],
      'sys' => [
        'food_id' => $sys_food_id,
        'food_name' => $sys_food_name,
        'food_confidence' => $sys_food_confidence,

        'foods_predict' => $sys_food_predicts,

        'ingredients_missing' => $sys_ingredients_missing,
      ],

      'api' => [
        'result' => $datas['result'],
      ]
    ];

    return response()->json([
      'status' => $status,

      'img_url' => $img_url,

      'data' => $data,
      'food' => $food ? $food->id : 0,
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

    $restaurant_ids = Restaurant::select('id')
      ->where('deleted', 0)
      ->where('restaurant_parent_id', $restaurant_parent_id);

    $rows = RestaurantFood::query("restaurant_foods")
      ->distinct()
      ->select('foods.id', 'foods.name')
      ->leftJoin('foods', 'foods.id', '=', 'restaurant_foods.food_id')
      ->whereIn('restaurant_foods.restaurant_id', $restaurant_ids)
      ->where('foods.deleted', 0)
      ->where('restaurant_foods.deleted', 0)
      ->orderByRaw('TRIM(LOWER(foods.name))')
      ->get();

    $items = [];
    $count = 0;

    if (count($rows)) {
      foreach ($rows as $row) {

        $count++;

        $items[] = [
          'id' => $row->id,
          'name' => $count . '. ' . $row->name,
        ];
      }
    }

    return response()->json([
      'status' => true,
      'items' => $items,
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
    $row = Food::findOrFail((int)$values['item']);
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
