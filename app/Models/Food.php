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

  public function get_photo_standard($restaurant)
  {
    $photo = url('custom/img/no_photo.png');

    if ($restaurant) {

      $restaurant_ids = Restaurant::where('deleted', 0)
        ->select('id')
        ->where('restaurant_parent_id', $restaurant->restaurant_parent_id);

      $restaurant_food = RestaurantFood::where('deleted', 0)
        ->whereIn('restaurant_id', $restaurant_ids)
        ->where('food_id', $this->id)
        ->where('photo', '<>', NULL)
        ->orderBy('updated_at', 'desc')
        ->limit(1)
        ->first();

      if ($restaurant_food) {
        $photo = $restaurant_food->photo;
      }
    }

    return $photo;
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
        "{$tblFoodIngredient}.ingredient_quantity", "{$tblFoodIngredient}.ingredient_type"
      )
      ->leftJoin($tblIngredient, "{$tblIngredient}.id", "=", "{$tblFoodIngredient}.ingredient_id")
      ->where("{$tblFoodIngredient}.deleted", 0)
      ->where("{$tblFoodIngredient}.food_id", $this->id)
      ->orderBy("{$tblFoodIngredient}.ingredient_type", "asc")
      ->orderBy("{$tblFoodIngredient}.ingredient_quantity", "desc")
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

    if (isset($pars['restaurant_parent_id']) && !empty($pars['restaurant_parent_id'])) {
      $select->where("{$tblFoodIngredient}.restaurant_parent_id", (int)$pars['restaurant_parent_id']);
    }

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

  public function get_ingredients_info($pars)
  {
    $ingredients = isset($pars['ingredients']) ? (array)$pars['ingredients'] : [];
    $restaurant_parent_id = isset($pars['restaurant_parent_id']) ? (int)$pars['restaurant_parent_id'] : 0;

    $arr = [];

    if (count($ingredients) && $restaurant_parent_id) {
      foreach ($ingredients as $ing) {
        $ingredient = Ingredient::find($ing['id']);

        $row = FoodIngredient::where('food_id', $this->id)
          ->where('ingredient_id', $ingredient->id)
          ->where('restaurant_parent_id', $restaurant_parent_id)
          ->first();
        if (!$row) {
          continue;
        }

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
