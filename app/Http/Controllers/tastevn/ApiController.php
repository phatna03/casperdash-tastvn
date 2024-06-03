<?php

namespace App\Http\Controllers\tastevn;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
//excel
use Maatwebsite\Excel\Facades\Excel;
//lib
use App\Api\SysApp;
//model
use App\Models\Food;
use App\Models\Restaurant;
use App\Models\RestaurantParent;
use App\Models\KasWebhook;

class ApiController extends Controller
{

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
            'photo' => $food->get_photo_standard($restaurant_parent->get_sensors([
              'one_sensor' => 1,
            ])),
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
          "class" => "air dried striploin steak - canadian lobster",
          "confidence" => 0.78
        ],
        [
          "class" => "grilled striploin steak",
          "confidence" => 0.78
        ],
        [
          "class" => "canadian lobster",
          "confidence" => 0.78
        ],
        [
          "class" => "baked potato",
          "confidence" => 0.78
        ],
        [
          "class" => "steak sauce",
          "confidence" => 0.78
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

    $sys_app = new SysApp();
    $restaurant = Restaurant::find(5);

    $food_id = 0;
    $food_confidence = 0;
    $food_name = '';
    $ing_found = [];
    $ing_missing = [];

    if (count($predictions)) {

      $food = NULL;
      $foods = [];
      $ingredients_found = $sys_app->sys_ingredients_compact($predictions);

      //find food
      foreach ($predictions as $prediction) {
        $prediction = (array)$prediction;

        $confidence = (int)($prediction['confidence'] * 100);

        $found = Food::whereRaw('LOWER(name) LIKE ?', strtolower(trim($prediction['class'])))
          ->first();
        if ($found && $confidence >= 50 && count($ingredients_found) && $restaurant->serve_food($found)) {

          //check valid ingredient
          $valid_food = true;
          $food_ingredients = $found->get_ingredients([
            'restaurant_parent_id' => $restaurant->restaurant_parent_id,
          ]);
          if (!count($food_ingredients)) {
            $valid_food = false;
          }

          //check core ingredient
          $valid_core = true;
          $core_ids = $found->get_ingredients_core([
            'restaurant_parent_id' => $restaurant->restaurant_parent_id,
            'ingredient_id_only' => 1,
          ]);
          if (count($core_ids)) {
            $found_ids = array_column($ingredients_found, 'id');
            $found_count = 0;
            foreach ($found_ids as $found_id) {
              if (in_array($found_id, $core_ids)) {
                $found_count++;
              }
            }
            if ($found_count != count($core_ids)) {
              $valid_core = false;
            }
          }

          if ($valid_core && $valid_food) {
            $foods[] = [
              'food' => $found->id,
              'confidence' => $confidence,
            ];
          }
        }
      }

      if (count($foods)) {
        if (count($foods) > 1) {
          $a1 = [];
          $a2 = [];
          foreach ($foods as $key => $val) {
            $a1[$key] = $val['confidence'];
            $a2[$key] = $val['food'];
          }
          array_multisort($a1, SORT_DESC, $a2, SORT_DESC, $foods);
        }

        $foods = $foods[0];
        $food = Food::find($foods['food']);
        $food_confidence = $foods['confidence'];
      }

      if ($food) {

        $food_id = $food->id;
        $food_name = $food->name;

        $ing_found = $food->get_ingredients_info([
          'restaurant_parent_id' => $restaurant->restaurant_parent_id,
          'ingredients' => $ingredients_found,
        ]);
        $ing_missing = $food->missing_ingredients([
          'restaurant_parent_id' => $restaurant->restaurant_parent_id,
          'ingredients' => $ingredients_found,
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
