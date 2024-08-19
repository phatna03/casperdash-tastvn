<?php

namespace App\Http\Controllers\tastevn;
use App\Http\Controllers\Controller;
use App\Models\RestaurantFoodScan;
use Illuminate\Http\Request;
//excel
use Maatwebsite\Excel\Facades\Excel;
//lib
use App\Api\SysRobo;
//model
use App\Models\Food;
use App\Models\Restaurant;
use App\Models\RestaurantParent;
use App\Models\KasWebhook;

class ApiController extends Controller
{

  //dev
  public function rfs_check(Request $request)
  {
    $values = $request->post();

    $status = false;
    $datas = [];

    $ids = isset($values['ids']) && !empty($values['ids']) ? (array)$values['ids'] : [];
    if (count($ids)) {

      $datas = RestaurantFoodScan::where('deleted', 0)
        ->whereIn('id', $ids)
        ->select('id', 'local_storage', 'photo_url')
        ->get()
        ->toArray();

      $status = true;
    }

    return response()->json([
      'status' => $status,
      'datas' => $datas,
      'ids' => $ids,
    ]);
  }

  public function rfs_get(Request $request)
  {
    $values = $request->post();

    $status = false;
    $datas = [];

    $min_id = isset($values['min_id']) && !empty($values['min_id']) ? (int)$values['min_id'] : 0;
    if ($min_id) {

      $datas = RestaurantFoodScan::where('id', '>', $min_id)
        ->whereNotIn('status', ['new', 'scanned'])
        ->orderBy('id', 'asc')
        ->limit(10)
        ->get()
        ->toArray();

      $status = true;
    }

    return response()->json([
      'status' => $status,
      'datas' => $datas,
    ]);
  }

  //hop
  public function food_ingredient(Request $request)
  {
    $values = $request->all();
//    echo '<pre>';

    $items = [];

    $restaurant_parents = RestaurantParent::where('deleted', 0)
      ->get();

    foreach ($restaurant_parents as $restaurant_parent) {

      $foods = [];

      $temps = $restaurant_parent->get_foods();
      if (count($temps)) {

        $ids = [];
        foreach ($temps as $temp) {

          $food = Food::find((int)$temp['food_id']);
          if (!$food || in_array($food->id, $ids)) {
            continue;
          }

          $ids[] = $food->id;
          $ings = [];

          $ingredients = $food->get_ingredients([
            'restaurant_parent_id' => $restaurant_parent->id,
          ]);
          if (count($ingredients)) {
            foreach ($ingredients as $ingredient) {
              $ings[] = [
                'quantity' => $ingredient->ingredient_quantity,
                'type' => $ingredient->ingredient_type,
                'name' => strtolower($ingredient->name)
              ];
            }
          }

          $foods[] = [
            'name' => strtolower($food->name),
            'photo' => $food->get_photo([
              'restaurant_parent_id' => $restaurant_parent->id,
            ]),
            'ingredients' => $ings,
          ];
        }
      }

      $items[] = [
        'restaurant' => strtolower($restaurant_parent->name),
        'foods' => $foods,
      ];
    }

    return response()->json($items);
  }

  public function food_datas()
  {
    $ch = curl_init();
    $headers = [
      'Accept: application/json',
    ];

    $URL = url('api/food/predict');
    $postData = [
      'predictions' => [
        [
          "class" => "beef striploin salad",
          "confidence" => 0.78
        ],
        [
          "class" => "sliced grilled striploin steak",
          "confidence" => 0.78
        ],
        [
          "class" => "avocado cut",
          "confidence" => 0.78
        ],
        [
          "class" => "orange segments",
          "confidence" => 0.78
        ],
        [
          "class" => "garlic bread",
          "confidence" => 0.91
        ],
        [
          "class" => "garlic bread",
          "confidence" => 0.70
        ],
        [
          "class" => "garlic bread",
          "confidence" => 0.44
        ],
      ]
    ];

    curl_setopt($ch, CURLOPT_URL, $URL);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    $result = curl_exec($ch);
    $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    curl_close($ch);

    $data = (array)json_decode($result);

    echo '<pre>';var_dump($data);die;
  }

  public function food_predict(Request $request)
  {
    $values = $request->post();

    $valid_data = true;
    $predictions = isset($values['predictions']) && !empty($values['predictions']) ? (array)$values['predictions'] : [];
    if (count($predictions)) {
      foreach ($predictions as $prediction) {
        if (isset($prediction['class']) && isset($prediction['confidence'])) {
          continue;
        }

        $valid_data = false;
      }
    }
    if (!$valid_data) {
      return response()->json([
        'status' => false,
        'error' => 'invalid data',

        'datas' => json_encode($predictions),
      ]);
    }

    $restaurant = Restaurant::find(5);

    $food_id = 0;
    $food_confidence = 0;
    $food_name = '';
    $ing_found = [];
    $ing_missing = [];

    if (count($predictions)) {

      //find foods
      $foods = SysRobo::foods_find([
        'predictions' => $predictions,
        'restaurant_parent_id' => $restaurant->restaurant_parent_id,
      ]);

      //food highest confidence
      $foods = SysRobo::foods_valid($foods);

      $food = NULL;
      if (count($foods) && $foods['food']) {
        $food = Food::find($foods['food']);
        $food_confidence = $foods['confidence'];
      }

      //find missing ingredients
      if ($food) {

        $food_id = $food->id;
        $food_name = $food->name;

        $ing_found = $food->get_ingredients_info([
          'restaurant_parent_id' => $restaurant->restaurant_parent_id,
          'predictions' => $predictions,
        ]);

        $ing_missing = $food->missing_ingredients([
          'restaurant_parent_id' => $restaurant->restaurant_parent_id,
          'ingredients' => $ing_found,
        ]);
      }
    }

    return response()->json([
      'status' => true,
      'food' => [
        'id' => $food_id,
        'confidence' => $food_confidence,
        'name' => $food_name,
      ],
      'ingredient' => [
        'found' => $ing_found,
        'missing' => $ing_missing,
      ]
    ]);
  }

  //kas
  public function kas_cart_info(Request $request)
  {
    $values = $request->post();

    $rows = KasWebhook::where('type', 'cart_info')
//      ->where('created_at', '>=', Carbon::now()->subMinutes(1)->toDateTimeString())
      ->where('params', json_encode($values))
      ->get();
//    if (count($rows) > 1) {
//      return response()->json([
//        'error' => 'No spam request.',
//      ], 404);
//    }

    KasWebhook::create([
      'type' => 'cart_info',
      'params' => json_encode($values),
    ]);

    $restaurant_id = isset($values['restaurant_id']) && !empty($values['restaurant_id']) ? $values['restaurant_id'] : NULL;
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
      $item_id = isset($item['item_id']) && !empty($item['item_id']) ? $item['item_id'] : NULL;
      $item_quantity = isset($item['quantity']) && !empty($item['quantity']) ? (int)$item['quantity'] : 1;
      $item_code = isset($item['item_code']) && !empty($item['item_code']) ? trim($item['item_code']) : NULL;
      $item_name = isset($item['item_name']) && !empty($item['item_name']) ? trim($item['item_name']) : NULL;
      $item_status = isset($item['status']) && !empty($item['status']) ? trim($item['status']) : NULL;
      $item_note = isset($item['note']) && !empty($item['note']) ? trim($item['note']) : NULL;

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
