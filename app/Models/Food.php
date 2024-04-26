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
    $ingredients = [];
    $arr = $this->get_ingredients();
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
      'name' => $this->name,
      'ingredients' => $ingredients,
    ];
  }

  public function add_recipes($ingredients = [])
  {
    //duplicate
    $ids = [];

    if (count($ingredients)) {
      foreach ($ingredients as $ingredient) {
        $ingredient = (array)$ingredient;

        if (!in_array((int)$ingredient['id'], $ids)) {
          FoodRecipe::create([
            'food_id' => $this->id,
            'ingredient_id' => (int)$ingredient['id'],
            'ingredient_quantity' => (int)$ingredient['quantity'],
          ]);
        }

        $ids[] = (int)$ingredient['id'];
      }
    }
  }

  public function get_recipes()
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
      ->orderBy("{$tblFoodIngredient}.id");

    return $select->get();
  }

  public function add_ingredients($ingredients = [])
  {
    //duplicate
    $ids = [];

    if (count($ingredients)) {
      foreach ($ingredients as $ingredient) {
        $ingredient = (array)$ingredient;

        if (!in_array((int)$ingredient['id'], $ids)) {
          FoodIngredient::create([
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

  public function update_ingredients($ingredients = [])
  {
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
            ->where('ingredient_id', (int)$ingredient['id'])
            ->first();
          if ($row) {

            $row->update([
              'deleted' => 0,
              'ingredient_type' => (int)$ingredient['core'] ? 'core' : 'additive',
              'ingredient_quantity' => (int)$ingredient['quantity'],
              'ingredient_color' => isset($ingredient['color']) && !empty($ingredient['color']) ? $ingredient['color'] : null,
            ]);

          } else {

            //create
            $row = FoodIngredient::create([
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
      ->whereNotIn('id', $keeps)
      ->update([
        'deleted' => Auth::user() ? Auth::user()->id : 999999,
      ]);
  }

  public function get_ingredients()
  {
    $tblFoodIngredient = app(FoodIngredient::class)->getTable();
    $tblIngredient = app(Ingredient::class)->getTable();

    $select = FoodIngredient::query($tblFoodIngredient)
      ->distinct()
      ->select("{$tblFoodIngredient}.id as food_ingredient_id", "{$tblIngredient}.id",
        "{$tblIngredient}.name", "{$tblIngredient}.name_vi", "{$tblFoodIngredient}.ingredient_color",
        "{$tblFoodIngredient}.ingredient_quantity", "{$tblFoodIngredient}.ingredient_type"
      )
      ->leftJoin($tblIngredient, "{$tblIngredient}.id", "=", "{$tblFoodIngredient}.ingredient_id")
      ->where("{$tblFoodIngredient}.deleted", 0)
      ->where("{$tblFoodIngredient}.food_id", $this->id)
      ->orderBy("{$tblFoodIngredient}.ingredient_type", "asc")
      ->orderBy("{$tblFoodIngredient}.ingredient_quantity", "desc")
      ->orderBy("{$tblFoodIngredient}.id");

    return $select->get();
  }

  public function get_ingredients_core($pars = [])
  {
    $tblFoodIngredient = app(FoodIngredient::class)->getTable();
    $tblIngredient = app(Ingredient::class)->getTable();

    $select = FoodIngredient::query($tblFoodIngredient)
      ->distinct()
      ->select("{$tblFoodIngredient}.id as food_ingredient_id", "{$tblIngredient}.id",
        "{$tblIngredient}.name", "{$tblIngredient}.name_vi", "{$tblFoodIngredient}.ingredient_color",
        "{$tblFoodIngredient}.ingredient_quantity", "{$tblFoodIngredient}.ingredient_type"
      )
      ->leftJoin($tblIngredient, "{$tblIngredient}.id", "=", "{$tblFoodIngredient}.ingredient_id")
      ->where("{$tblFoodIngredient}.deleted", 0)
      ->where("{$tblFoodIngredient}.food_id", $this->id)
      ->where("{$tblFoodIngredient}.ingredient_type", 'core')
      ->orderBy("{$tblFoodIngredient}.ingredient_type", "asc")
      ->orderBy("{$tblFoodIngredient}.ingredient_quantity", "desc")
      ->orderBy("{$tblFoodIngredient}.id");

    $rows = $select->get();

    if (isset($pars['ingredient_id_only']) && (int)$pars['ingredient_id_only']) {
      $items = [];

      if (count($rows)) {
        foreach ($rows as $row) {
          $items[] = $row->id;
        }
      }

      sort($items);
      return $items;
    }

    return $rows;
  }

  public function check_food_confidence_by_ingredients($predictionsIds = [])
  {
    $count = 0;

    $ingredients = $this->get_ingredients();
    if (count($ingredients) && count($predictionsIds)) {
      foreach ($ingredients as $ingredient) {
        if (in_array($ingredient->id, $predictionsIds)) {
          $count++;
        }
      }
      if ($count) {
        $count = (int)($count / count($ingredients) * 100);
      }
    }

    return $count;
  }

  public function found_ingredients($predictions = [])
  {
    $arr = [];

    $ingredients = $this->get_ingredients();
    if (count($ingredients) && count($predictions)) {
      foreach ($ingredients as $ingredient) {
        foreach ($predictions as $prediction) {
          if ($prediction['id'] == $ingredient['id']) {
            $prediction['quantity'] = $prediction['quantity'] >= $ingredient['ingredient_quantity'] ? $ingredient['ingredient_quantity'] : $prediction['quantity'];
            $arr[] = $prediction;
          }
        }
      }
    }

    return $arr;
  }

  public function missing_ingredients($predictions = [])
  {
    $arr = [];
    $ids = [];

    $ingredients = $this->get_ingredients();
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

  public function get_restaurants()
  {
    $tblRestaurant = app(Restaurant::class)->getTable();
    $tblRestaurantFood = app(RestaurantFood::class)->getTable();

    $select = Restaurant::query($tblRestaurant)
      ->select("{$tblRestaurant}.name")
      ->leftJoin($tblRestaurantFood, "{$tblRestaurantFood}.restaurant_id", "=", "{$tblRestaurant}.id")
      ->where("{$tblRestaurantFood}.food_id", $this->id)
      ->orderBy("{$tblRestaurantFood}.id", "desc");

    return $select->get();
  }

  public function get_ingredients_info($ingredients)
  {
    $arr = [];

    if (count($ingredients)) {
      foreach ($ingredients as $ing) {
        $ingredient = Ingredient::find($ing['id']);

        $row = FoodIngredient::where('food_id', $this->id)
          ->where('ingredient_id', $ingredient->id)
          ->first();

        $arr[] = [
          'id' => $ing['id'],
          'quantity' => $ing['quantity'],
          'name' => $ingredient->name,
          'name_vi' => $ingredient->name_vi,
          'type' => $row ? $row->ingredient_type : 'additive',
        ];
      }
    }

    return $arr;
  }

  public function get_photo()
  {
    $text = url('custom/img/no_photo.png');
    if (!empty($this->photo)) {
      $text = url('') . $this->photo;
    }
    return $text;
  }
}
