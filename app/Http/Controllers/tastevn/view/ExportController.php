<?php

namespace App\Http\Controllers\tastevn\view;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;

use Validator;
use Carbon\Carbon;
use App\Api\SysCore;

use Maatwebsite\Excel\Facades\Excel;
use App\Excel\ExportFoodIngredient;

use App\Models\Food;
use App\Models\RestaurantParent;

class ExportController extends Controller
{
  protected $_api_core = null;

  public function __construct()
  {
    $this->_api_core = new SysCore();
  }

  public function food_ingredient(Request $request)
  {
    $values = $request->all();
//    echo '<pre>';

    $items = [];

    $restaurant_parents = RestaurantParent::where('deleted', 0)
      ->get();

    foreach ($restaurant_parents as $restaurant_parent) {

      $foods = [];

      $temps = $restaurant_parent->get_food_datas();
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

}
