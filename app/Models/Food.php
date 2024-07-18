<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Food extends Model
{
  use HasFactory;

  public $table = 'foods';

  protected $fillable = [
    'name',
    'photo',
    'live_group',
    'count_restaurants',
    'creator_id',
    'deleted',
  ];

  public function get_type()
  {
    return 'food';
  }

  public function get_log()
  {
    return [
//      'live_group' => $this->live_group,
      'name' => $this->name,
    ];
  }

  public function get_log_ingredient($pars = [])
  {
    $restaurant_parent_id = isset($pars['restaurant_parent_id']) ? (int)$pars['restaurant_parent_id'] : 0;

    $ingredients = [];
    $arr = $this->get_ingredients([
      'restaurant_parent_id' => $restaurant_parent_id
    ]);
    if (count($arr)) {

      $a1 = [];
      $a2 = [];

      foreach ($arr as $key => $itm) {
        $ingredients[] = [
          'id' => $itm->id,
          'quantity' => $itm->ingredient_quantity,
        ];

        $a1[$key] = $itm->id;
        $a2[$key] = $itm->ingredient_quantity;
      }

      array_multisort($a1, SORT_ASC, $a2, SORT_DESC, $ingredients);
    }

    return [
      'restaurant_parent_id' => $restaurant_parent_id,
      'ingredients' => $ingredients,
    ];
  }

  public function get_log_recipe($pars = [])
  {
    $restaurant_parent_id = isset($pars['restaurant_parent_id']) ? (int)$pars['restaurant_parent_id'] : 0;

    $ingredients = [];
    $arr = $this->get_recipes([
      'restaurant_parent_id' => $restaurant_parent_id
    ]);
    if (count($arr)) {

      $a1 = [];
      $a2 = [];

      foreach ($arr as $key => $itm) {
        $ingredients[] = [
          'id' => $itm->id,
          'quantity' => $itm->ingredient_quantity,
        ];

        $a1[$key] = $itm->id;
        $a2[$key] = $itm->ingredient_quantity;
      }

      array_multisort($a1, SORT_ASC, $a2, SORT_DESC, $ingredients);
    }

    return [
      'restaurant_parent_id' => $restaurant_parent_id,
      'ingredients' => $ingredients,
    ];
  }

  public function add_recipes($pars = [])
  {
    $ingredients = isset($pars['ingredients']) ? (array)$pars['ingredients'] : [];
    $restaurant_parent_id = isset($pars['restaurant_parent_id']) ? (int)$pars['restaurant_parent_id'] : 0;

    //duplicate
    $ids = [];

    if (count($ingredients)) {
      foreach ($ingredients as $ingredient) {
        $ingredient = (array)$ingredient;

        if (!in_array((int)$ingredient['id'], $ids)) {
          FoodRecipe::create([
            'restaurant_parent_id' => $restaurant_parent_id,
            'food_id' => $this->id,
            'ingredient_id' => (int)$ingredient['id'],
            'ingredient_quantity' => (int)$ingredient['quantity'],
          ]);
        }

        $ids[] = (int)$ingredient['id'];
      }
    }
  }

  public function get_recipes($pars = [])
  {
    $tblFoodIngredient = app(FoodRecipe::class)->getTable();
    $tblIngredient = app(Ingredient::class)->getTable();

    $select = FoodRecipe::query($tblFoodIngredient)
      ->distinct()
      ->select("{$tblFoodIngredient}.id as food_ingredient_id", "{$tblIngredient}.id",
        "{$tblIngredient}.name", "{$tblIngredient}.name_vi", "{$tblFoodIngredient}.ingredient_quantity"
      )
      ->leftJoin($tblIngredient, "{$tblIngredient}.id", "=", "{$tblFoodIngredient}.ingredient_id")
      ->where("{$tblFoodIngredient}.deleted", 0)
      ->where("{$tblFoodIngredient}.food_id", $this->id)
      ->orderBy("{$tblFoodIngredient}.ingredient_quantity", "desc")
      ->orderByRaw("CHAR_LENGTH({$tblIngredient}.name)")
      ->orderBy("{$tblFoodIngredient}.id");

    if (isset($pars['restaurant_parent_id']) && !empty($pars['restaurant_parent_id'])) {
      $select->where("{$tblFoodIngredient}.restaurant_parent_id", (int)$pars['restaurant_parent_id']);
    }

    return $select->get();
  }

  public function add_ingredients($pars = [])
  {
    $ingredients = isset($pars['ingredients']) ? (array)$pars['ingredients'] : [];
    $restaurant_parent_id = isset($pars['restaurant_parent_id']) ? (int)$pars['restaurant_parent_id'] : 0;

    //duplicate
    $ids = [];

    if (count($ingredients)) {
      foreach ($ingredients as $ingredient) {
        $ingredient = (array)$ingredient;

        if (!in_array((int)$ingredient['id'], $ids)) {
          FoodIngredient::create([
            'restaurant_parent_id' => $restaurant_parent_id,
            'food_id' => $this->id,
            'ingredient_id' => (int)$ingredient['id'],
            'ingredient_type' => (int)$ingredient['core'] ? 'core' : 'additive',
            'ingredient_quantity' => (int)$ingredient['quantity'],
            'ingredient_color' => isset($ingredient['color']) && !empty($ingredient['color']) ? $ingredient['color'] : null,
          ]);
        }

        $ids[] = (int)$ingredient['id'];
      }
    }
  }

  public function update_ingredients($pars = [])
  {
    $restaurant_parent_id = isset($pars['restaurant_parent_id']) ? (int)$pars['restaurant_parent_id'] : 0;
    $ingredients = isset($pars['ingredients']) ? (array)$pars['ingredients'] : [];

    //duplicate
    $ids = [];
    $keeps = [];

    if (count($ingredients)) {
      foreach ($ingredients as $ingredient) {
        $ingredient = (array)$ingredient;

        if (in_array((int)$ingredient['id'], $ids)) {
          continue;
        }

        if ((int)$ingredient['old']) {

          //update
          $row = FoodIngredient::find((int)$ingredient['old']);
          if ($row) {
            $row->update([
              'restaurant_parent_id' => $restaurant_parent_id,
              'ingredient_id' => (int)$ingredient['id'],
              'ingredient_type' => (int)$ingredient['core'] ? 'core' : 'additive',
              'ingredient_quantity' => (int)$ingredient['quantity'],
              'ingredient_color' => isset($ingredient['color']) && !empty($ingredient['color']) ? $ingredient['color'] : null,
            ]);

            $keeps[] = $row->id;
          }

        } else {

          //check deleted
          $row = FoodIngredient::where('food_id', $this->id)
            ->where('restaurant_parent_id', $restaurant_parent_id)
            ->where('ingredient_id', (int)$ingredient['id'])
            ->first();
          if ($row) {

            $row->update([
              'restaurant_parent_id' => $restaurant_parent_id,
              'deleted' => 0,
              'ingredient_type' => (int)$ingredient['core'] ? 'core' : 'additive',
              'ingredient_quantity' => (int)$ingredient['quantity'],
              'ingredient_color' => isset($ingredient['color']) && !empty($ingredient['color']) ? $ingredient['color'] : null,
            ]);

          } else {

            //create
            $row = FoodIngredient::create([
              'restaurant_parent_id' => $restaurant_parent_id,
              'food_id' => $this->id,
              'ingredient_id' => (int)$ingredient['id'],
              'ingredient_type' => (int)$ingredient['core'] ? 'core' : 'additive',
              'ingredient_quantity' => (int)$ingredient['quantity'],
              'ingredient_color' => isset($ingredient['color']) && !empty($ingredient['color']) ? $ingredient['color'] : null,
            ]);
          }

          $keeps[] = $row->id;
        }

        $ids[] = (int)$ingredient['id'];
      }
    }

    //remove
    FoodIngredient::where('food_id', $this->id)
      ->where('restaurant_parent_id', $restaurant_parent_id)
      ->whereNotIn('id', $keeps)
      ->update([
        'deleted' => Auth::user() ? Auth::user()->id : 999999,
      ]);
  }

  public function update_ingredients_recipe($pars = [])
  {
    $restaurant_parent_id = isset($pars['restaurant_parent_id']) ? (int)$pars['restaurant_parent_id'] : 0;
    $ingredients = isset($pars['ingredients']) ? (array)$pars['ingredients'] : [];

    //duplicate
    $ids = [];
    $keeps = [];

    if (count($ingredients)) {
      foreach ($ingredients as $ingredient) {
        $ingredient = (array)$ingredient;

        if (in_array((int)$ingredient['id'], $ids)) {
          continue;
        }

        if ((int)$ingredient['old']) {

          //update
          $row = FoodRecipe::find((int)$ingredient['old']);
          if ($row) {
            $row->update([
              'restaurant_parent_id' => $restaurant_parent_id,
              'ingredient_id' => (int)$ingredient['id'],
              'ingredient_quantity' => (int)$ingredient['quantity'],
            ]);

            $keeps[] = $row->id;
          }

        } else {

          //check deleted
          $row = FoodRecipe::where('food_id', $this->id)
            ->where('restaurant_parent_id', $restaurant_parent_id)
            ->where('ingredient_id', (int)$ingredient['id'])
            ->first();
          if ($row) {

            $row->update([
              'restaurant_parent_id' => $restaurant_parent_id,
              'deleted' => 0,
              'ingredient_quantity' => (int)$ingredient['quantity'],
            ]);

          } else {

            //create
            $row = FoodRecipe::create([
              'restaurant_parent_id' => $restaurant_parent_id,
              'food_id' => $this->id,
              'ingredient_id' => (int)$ingredient['id'],
              'ingredient_quantity' => (int)$ingredient['quantity'],
            ]);
          }

          $keeps[] = $row->id;
        }

        $ids[] = (int)$ingredient['id'];
      }
    }

    //remove
    FoodRecipe::where('food_id', $this->id)
      ->where('restaurant_parent_id', $restaurant_parent_id)
      ->whereNotIn('id', $keeps)
      ->update([
        'deleted' => Auth::user() ? Auth::user()->id : 999999,
      ]);
  }

  public function get_ingredients($pars = [])
  {
    $tblFoodIngredient = app(FoodIngredient::class)->getTable();
    $tblIngredient = app(Ingredient::class)->getTable();

    $select = FoodIngredient::query($tblFoodIngredient)
      ->distinct()
      ->select("{$tblFoodIngredient}.id as food_ingredient_id", "{$tblIngredient}.id",
        "{$tblIngredient}.name", "{$tblIngredient}.name_vi", "{$tblFoodIngredient}.ingredient_color",
        "{$tblFoodIngredient}.ingredient_quantity", "{$tblFoodIngredient}.ingredient_type",
        "{$tblFoodIngredient}.confidence"
      )
      ->leftJoin($tblIngredient, "{$tblIngredient}.id", "=", "{$tblFoodIngredient}.ingredient_id")
      ->where("{$tblFoodIngredient}.deleted", 0)
      ->where("{$tblFoodIngredient}.food_id", $this->id)
      ->orderBy("{$tblFoodIngredient}.ingredient_type", "asc")
      ->orderBy("{$tblFoodIngredient}.ingredient_quantity", "desc")
      ->orderByRaw("CHAR_LENGTH({$tblIngredient}.name)")
      ->orderBy("{$tblFoodIngredient}.id");

    if (isset($pars['restaurant_parent_id']) && !empty($pars['restaurant_parent_id'])) {
      $select->where("{$tblFoodIngredient}.restaurant_parent_id", (int)$pars['restaurant_parent_id']);
    }

    return $select->get();
  }

  public function get_ingredients_core($pars = [])
  {
    $tblFoodIngredient = app(FoodIngredient::class)->getTable();
    $tblIngredient = app(Ingredient::class)->getTable();

    $select = FoodIngredient::query($tblFoodIngredient)
      ->distinct()
      ->select("{$tblIngredient}.id as ingredient_id", "{$tblIngredient}.name as ingredient_name",
        "{$tblFoodIngredient}.ingredient_quantity", "{$tblFoodIngredient}.confidence as ingredient_confidence"
      )
      ->leftJoin($tblIngredient, "{$tblIngredient}.id", "=", "{$tblFoodIngredient}.ingredient_id")
      ->where("{$tblFoodIngredient}.deleted", 0)
      ->where("{$tblFoodIngredient}.food_id", $this->id)
      ->where("{$tblFoodIngredient}.ingredient_type", 'core')
      ->orderBy("{$tblFoodIngredient}.ingredient_type", "asc")
      ->orderBy("{$tblFoodIngredient}.ingredient_quantity", "desc")
      ->orderBy("{$tblFoodIngredient}.id");

    if (isset($pars['restaurant_parent_id']) && !empty($pars['restaurant_parent_id'])) {
      $select->where("{$tblFoodIngredient}.restaurant_parent_id", (int)$pars['restaurant_parent_id']);
    }

    return $select->get();
  }

  public function missing_ingredients($pars = [])
  {
    $predictions = isset($pars['ingredients']) ? (array)$pars['ingredients'] : [];
    $restaurant_parent_id = isset($pars['restaurant_parent_id']) ? (int)$pars['restaurant_parent_id'] : 0;

    $arr = [];
    $ids = [];

    $ingredients = $this->get_ingredients([
      'restaurant_parent_id' => $restaurant_parent_id,
    ]);
    if (count($ingredients) && count($predictions)) {
      foreach ($ingredients as $ingredient) {
        $found = false;

        foreach ($predictions as $prediction) {
          if ($prediction['id'] == $ingredient['id']) {
            $found = true;

            if ($prediction['quantity'] < $ingredient['ingredient_quantity']) {
              if (!in_array($prediction['id'], $ids)) {
                $prediction['quantity'] = $ingredient['ingredient_quantity'] - $prediction['quantity'];

                $ing = Ingredient::find($prediction['id']);
                $arr[] = [
                  'id' => $ing->id,
                  'quantity' => $prediction['quantity'],
                  'name' => $ing->name,
                  'name_vi' => $ing->name_vi,
                  'type' => $ing->ingredient_type,
                ];

                $ids[] = $prediction['id'];
              }
            }
          }
        }

        if (!$found) {
          $arr[] = [
            'id' => $ingredient->id,
            'quantity' => $ingredient->ingredient_quantity,
            'name' => $ingredient->name,
            'name_vi' => $ingredient->name_vi,
            'type' => $ingredient->ingredient_type,
          ];
        }
      }

    } else {

      if (count($ingredients)) {
        foreach ($ingredients as $ingredient) {
          $arr[] = [
            'id' => $ingredient->id,
            'quantity' => $ingredient->ingredient_quantity,
            'name' => $ingredient->name,
            'name_vi' => $ingredient->name_vi,
            'type' => $ingredient->ingredient_type,
          ];
        }
      }
    }

    return $arr;
  }

  public function get_ingredients_info($pars)
  {
    $debug = isset($pars['debug']) ? (bool)$pars['debug'] : false;

    $predictions = isset($pars['predictions']) ? (array)$pars['predictions'] : [];
    $restaurant_parent_id = isset($pars['restaurant_parent_id']) ? (int)$pars['restaurant_parent_id'] : 0;

    $arr = [];
    $ids = [];

    if (count($predictions) && $restaurant_parent_id) {

      $temps = [];
      foreach ($predictions as $prediction) {
        $prediction = (array)$prediction;

        $temps[] = [
          'ingredient' => strtolower(trim($prediction['class'])),
          'confidence' => round($prediction['confidence'] * 100),
        ];
      }

      if ($debug) {
        var_dump('+++++ ING COMPACT?');
        var_dump($temps);
      }

      foreach ($temps as $temp) {

        $ingredient = Ingredient::where('deleted', 0)
          ->whereRaw('LOWER(name) LIKE ?', $temp['ingredient'])
          ->first();

        if ($debug) {
          var_dump('***** INGREDIENT? = ' . ($ingredient ? $ingredient->id . ' - ' . $ingredient->name : 0));
        }

        if ($ingredient) {
          $row = FoodIngredient::where('deleted', 0)
            ->where('food_id', $this->id)
            ->where('ingredient_id', $ingredient->id)
            ->where('restaurant_parent_id', $restaurant_parent_id)
            ->where('confidence', '<=', $temp['confidence'])
            ->first();

          if ($debug) {
            var_dump('***** INGREDIENT VALID? = ' . ($row ? $row->id . ' - ' . $temp['confidence'] . '%' : 0));
          }

          if ($row) {

            if (count($ids) && in_array($ingredient->id, $ids)) {
              $arr[$ingredient->id]['quantity'] = $arr[$ingredient->id]['quantity'] + 1;
            } else {
              $arr[$ingredient->id] = [
                'id' => $ingredient->id,
                'quantity' => 1,
                'name' => $ingredient->name,
                'name_vi' => $ingredient->name_vi,
                'type' => $row ? $row->ingredient_type : 'additive',
              ];
            }

            if (!in_array($ingredient->id, $ids)) {
              $ids[] = $ingredient->id;
            }
          }
        }
      }
    }

    return $arr;
  }

  public function get_photo($pars = [])
  {
    $photo = url('custom/img/no_photo.png');

    if (isset($pars['restaurant_parent_id']) && !empty($pars['restaurant_parent_id'])) {
      $restaurant_parent_id = (int)$pars['restaurant_parent_id'];
      $restaurant_parent = RestaurantParent::find($restaurant_parent_id);
      if ($restaurant_parent) {
        $food_photo = $restaurant_parent->get_food_photo($this);
        if (!empty($food_photo)) {
          $photo = $food_photo;
        }
      }
    }

    return $photo;
  }

  public function get_category($pars = [])
  {
    $food_category = NULL;

    if (isset($pars['restaurant_parent_id']) && !empty($pars['restaurant_parent_id'])) {
      $restaurant_parent_id = (int)$pars['restaurant_parent_id'];
      $restaurant_parent = RestaurantParent::find($restaurant_parent_id);
      if ($restaurant_parent) {
        $food_category = $restaurant_parent->get_food_category($this);
      }
    }

    return $food_category;
  }

  public function get_live_group($pars = [])
  {
    $live_group = 3;

    if (isset($pars['restaurant_parent_id']) && !empty($pars['restaurant_parent_id'])) {
      $restaurant_parent_id = (int)$pars['restaurant_parent_id'];
      $restaurant_parent = RestaurantParent::find($restaurant_parent_id);
      if ($restaurant_parent) {
        $live_group = $restaurant_parent->get_food_live_group($this);
      }
    }

    return $live_group;
  }

  public function get_food_confidence($pars = [])
  {
    $confidence = 30;

    if (isset($pars['restaurant_parent_id']) && !empty($pars['restaurant_parent_id'])) {
      $restaurant_parent_id = (int)$pars['restaurant_parent_id'];
      $restaurant_parent = RestaurantParent::find($restaurant_parent_id);
      if ($restaurant_parent) {
        $confidence = $restaurant_parent->get_food_confidence($this);
      }
    }

    return $confidence;
  }

  public function get_model_name($pars = [])
  {
    $model_name = NULL;

    if (isset($pars['restaurant_parent_id']) && !empty($pars['restaurant_parent_id'])) {
      $restaurant_parent_id = (int)$pars['restaurant_parent_id'];
      $restaurant_parent = RestaurantParent::find($restaurant_parent_id);
      if ($restaurant_parent) {
        $model_name = $restaurant_parent->get_food_model_name($this);
      }
    }

    return $model_name;
  }

  public function get_model_version($pars = [])
  {
    $model_version = NULL;

    if (isset($pars['restaurant_parent_id']) && !empty($pars['restaurant_parent_id'])) {
      $restaurant_parent_id = (int)$pars['restaurant_parent_id'];
      $restaurant_parent = RestaurantParent::find($restaurant_parent_id);
      if ($restaurant_parent) {
        $model_version = $restaurant_parent->get_food_model_version($this);
      }
    }

    return $model_version;
  }
}
