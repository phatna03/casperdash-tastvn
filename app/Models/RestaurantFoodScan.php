<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Support\Facades\Notification;
use App\Notifications\IngredientMissing;

use App\Api\SysCore;

class RestaurantFoodScan extends Model
{
  use HasFactory;

  public $table = 'restaurant_food_scans';

  protected $fillable = [
    'restaurant_id',
    'food_category_id',
    'food_id',
    'photo_url',
    'photo_name',
    'photo_ext',
    'confidence',
    'found_by',
    'status',
    'note',
    'time_photo',
    'time_scan',
    'total_seconds',
    'missing_ids',
    'missing_texts',

    'sys_predict',
    'sys_confidence',

    'usr_edited',
    'usr_predict',

    'rbf_predict',
    'rbf_confidence',
    'rbf_api',
    //1= need retrain //2= retrain success //3= retrain failed
    'rbf_retrain',
    'deleted',
  ];

  public function get_food()
  {
    return Food::find($this->food_id);
  }

  public function get_food_category()
  {
    return FoodCategory::find($this->food_category_id);
  }

  public function get_restaurant()
  {
    return Restaurant::find($this->restaurant_id);
  }

  public function get_ingredients_missing()
  {
    $tblFoodIngredientMissing = app(RestaurantFoodScanMissing::class)->getTable();
    $tblIngredient = app(Ingredient::class)->getTable();

    $select = RestaurantFoodScanMissing::query($tblFoodIngredientMissing)
      ->distinct()
      ->select("{$tblIngredient}.id", "{$tblIngredient}.name", "{$tblIngredient}.name_vi",
        "{$tblFoodIngredientMissing}.ingredient_quantity", "{$tblFoodIngredientMissing}.ingredient_type"
      )
      ->leftJoin($tblIngredient, "{$tblIngredient}.id", "=", "{$tblFoodIngredientMissing}.ingredient_id")
      ->where("$tblFoodIngredientMissing.restaurant_food_scan_id", $this->id)
      ->orderBy("{$tblFoodIngredientMissing}.ingredient_type", "asc")
      ->orderBy("{$tblFoodIngredientMissing}.ingredient_quantity", "desc")
      ->orderBy("{$tblFoodIngredientMissing}.id");

    return $select->get();
  }

  public function predict_food()
  {
    $result = (array)json_decode($this->rbf_api, true);
    $api_core = new SysCore();

    if (count($result)) {

      $food = NULL;
      $ingredients_found = $api_core->sys_ingredients_found($result['predictions']);

      //find food
      $predictions = $result['predictions'];
      if (count($predictions)) {
        foreach ($predictions as $prediction) {
          $prediction = (array)$prediction;

          $food = Food::whereRaw('LOWER(name) LIKE ?', strtolower(trim($prediction['class'])))
            ->first();

          //tester only
          if ($prediction['class'] == 'Scramble eggs') {
            $food = Food::find(4);
          }

          if ($food) {
            break;
          }
        }
      }
      //found?
      if ($food) {
        $this->update([
          'food_id' => $food->id,
          'confidence' => (int)($prediction['confidence'] * 100),
          'rbf_confidence' => (int)($prediction['confidence'] * 100),
          'found_by' => 'rbf',
          'rbf_predict' => $food->id,
        ]);
      } else {
        //system predict
        $predict = $api_core->sys_predict_foods_by_ingredients($ingredients_found, true);
        if (count($predict)) {
          $this->update([
            'food_id' => $predict['food'],
            'confidence' => (int)$predict['confidence'],
            'sys_confidence' => (int)$predict['confidence'],
            'found_by' => 'sys',
            'sys_predict' => $predict['food'],
          ]);
        }
      }

      $food = Food::find($this->food_id);
      //find missing ingredients
      if ($food) {

        $ingredients_found = $food->get_ingredients_info($ingredients_found);
        $ingredients_missing = $food->missing_ingredients($ingredients_found);
        $this->add_ingredients_missing($ingredients_missing);
      }

      //other params
      $this->update([
        'food_category_id' => (int)$this->find_food_category($food),
        'total_seconds' => $result['time'],
        'status' => 'checked',
      ]);

      if (!$food) {
        $this->update([
          'status' => 'failed',
        ]);
      }

    }
  }

  public function find_food_category($food)
  {
    $count = 0;

    if ($food) {
      $count = RestaurantFood::where('restaurant_id', $this->restaurant_id)
        ->where('food_id', $food->id)
        ->where('deleted', 0)
        ->pluck('food_category_id')
        ->first();
    }

    return (int)$count;
  }

  public function add_ingredients_missing($ingredients = [], $notification = true)
  {
    RestaurantFoodScanMissing::where('restaurant_food_scan_id', $this->id)
      ->delete();

    if (count($ingredients)) {
      foreach ($ingredients as $ingredient) {
        RestaurantFoodScanMissing::create([
          'restaurant_food_scan_id' => $this->id,
          'ingredient_id' => $ingredient['id'],
          'ingredient_quantity' => $ingredient['quantity'],
          'ingredient_type' => isset($ingredient['type']) ? $ingredient['type'] : 'additive',
        ]);
      }

      //notify
      if ($notification) {
        $users = $this->get_restaurant()->get_users();
        if (count($users)) {
          foreach ($users as $user) {
            Notification::send($user, new IngredientMissing([
              'type' => 'ingredient_missing',
              'restaurant_food_scan_id' => $this->id,
              'user' => $user,
            ]));
          }
        }
      }
    }

    $this->update_ingredients_missing_text();
  }

  public function update_ingredients_missing_text()
  {
    $ids = [];
    $texts = NULL;

    $ingredients = $this->get_ingredients_missing();
    if (count($ingredients)) {
      foreach ($ingredients as $ingredient) {

        $text = $ingredient['ingredient_quantity'] . ' ' . $ingredient['name'];
        if (!empty($ingredient['name_vi'])) {
          $text .= ' - ' . $ingredient['name_vi'];
        }

        $ids[] = (int)$ingredient['id'];
        $texts .= $text . ' &nbsp ';
      }
    }

    sort($ids);

    $this->update([
      'missing_ids' => count($ids) ? $ids : NULL,
      'missing_texts' => $texts,
    ]);
  }


}
