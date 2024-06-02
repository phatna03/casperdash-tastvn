<?php

namespace App\Http\Controllers\tastevn;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
//excel
use Maatwebsite\Excel\Facades\Excel;
//model
use App\Models\Food;
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
