<?php

namespace App\Http\Controllers\tastevn\api;

use App\Http\Controllers\Controller;
use App\Models\RestaurantFood;
use App\Models\RestaurantParent;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

use Validator;
use App\Models\User;
use App\Models\Restaurant;
use App\Models\RestaurantAccess;
use App\Models\RestaurantFoodScan;
use App\Models\Food;
use App\Models\FoodIngredient;
use App\Models\Ingredient;

use App\Api\SysCore;
use App\Jobs\PhotoUpload;

class RoboflowController extends Controller
{
  public function __construct()
  {
    $this->middleware(function ($request, $next) {
      return $next($request);
    });

    $this->middleware('auth');
  }

  public function index(Request $request)
  {
    $user = Auth::user();
    $invalid_roles = ['user'];
    if (in_array($user->role, $invalid_roles)) {
      return redirect('page_not_found');
    }

    $food = Food::where('deleted', 0)
      ->orderByDesc('id')
      ->limit(1)
      ->first();

    $pageConfigs = [
      'myLayout' => 'horizontal',
      'hasCustomizer' => false,

      'item' => [
        'food_4' => $food,
        'food_4_ingredients' => $food->get_ingredients(),
      ]
    ];

    $user->add_log([
      'type' => 'view_modal_testing',
    ]);

    return view('tastevn.pages.roboflow', ['pageConfigs' => $pageConfigs]);
  }

  public function detect(Request $request)
  {
    $status = false;
    $values = $request->all();

    $api_core = new SysCore();
    $rbf_dataset = $api_core->get_setting('rbf_dataset_scan');
    $rbf_api_key = $api_core->get_setting('rbf_api_key');

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

    if (!empty($imgFILE)) {

      foreach ($imgFILE as $file) {

        $pathStr = '/roboflow/test/';
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
        $img_url = "https://www.eatme.eu/media/dwpb21km/avocado-toast-met-roerei-en-avocado.jpg";
        if (App::environment() == 'production') {
          $img_url = url("roboflow/test") . '/' . $photoName;
        }

        // URL for Http Request
        $url =  "https://detect.roboflow.com/" . $rbf_dataset
          . "?api_key=" . $rbf_api_key
          . "&image=" . urlencode($img_url);

        // Setup + Send Http request
        $options = array(
          'http' => array (
            'header' => "Content-type: application/x-www-form-urlencoded\r\n",
            'method'  => 'POST'
          ));

        try {

          $context = stream_context_create($options);
          $result = file_get_contents($url, false, $context);

        } catch (\Exception $e) {

          return response()->json([
            'status' => false,
            'error' => $e->getMessage(),
          ], 400);
        }

        if (!empty($result)) {
          $result = (array)json_decode($result);
        }
      }

      if (count($result)) {

        $status = true;
        $predictions = $result['predictions'];
        if (count($predictions)) {

          //ingredients
          $ingredients_found = $api_core->sys_ingredients_found($predictions);
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
    ];

    return response()->json([
      'status' => $status,
      'env' => App::environment(),

      'data' => $data,
      'food' => $food ? $food->id : 0,
    ], 200);
  }

  protected function predict_foods($predictions)
  {
    $arr = [];
    $ingredientsIds = [];

    if (count($predictions)) {
      foreach ($predictions as $prediction) {

        $prediction = (array)$prediction;

        $ingredient = Ingredient::whereRaw('LOWER(name) LIKE ?', strtolower(trim($prediction['class'])))
          ->first();
        if ($ingredient) {
          $ingredientsIds[] = $ingredient->id;
        }
      }
    }

    //foods
    $foods = Food::where('deleted', 0)
      ->get();

    if (count($foods) && count($ingredientsIds)) {
      foreach ($foods as $food) {
        $confidence = $food->check_food_confidence_by_ingredients($ingredientsIds);
        if ($confidence) {
          $arr[] = [
            'food' => $food->name,
            'confidence' => $confidence,
          ];
        }
      }
    }

    return $arr;
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
    $user = Auth::user();

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

    $restaurant_ids = Restaurant::where('deleted', 0)
      ->select('id')
      ->where('restaurant_parent_id', $restaurant_parent_id);

    $restaurant_food = RestaurantFood::where('deleted', 0)
      ->whereIn('restaurant_id', $restaurant_ids)
      ->where('food_id', $row->id)
      ->where('photo', '<>', NULL)
      ->orderBy('updated_at', 'desc')
      ->limit(1)
      ->first();
    $food_photo = $restaurant_food ? $restaurant_food->photo : url('custom/img/no_photo.png');

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
