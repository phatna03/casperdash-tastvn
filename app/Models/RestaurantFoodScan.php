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
    'text_ids',
    'text_texts',
    'time_photo',
    'time_scan',
    'total_seconds',
    'time_end',
    'missing_ids',
    'missing_texts',

    'sys_predict',
    'sys_confidence',

    'usr_edited',
    'usr_predict',

    'rbf_predict',
    'rbf_confidence',
    'rbf_api',
    'rbf_api_js',
    //1= need retrain //2= retrain success //3= retrain failed
    'rbf_retrain',
    'deleted',
  ];

  public function get_type()
  {
    return 'restaurant_food_scan';
  }

  public function get_log()
  {
    $texts = [];
    $arr = $this->get_texts(['text_id_only' => 1]);
    if (count($arr)) {
      $texts = $arr->toArray();
      $texts = array_map('current', $texts);
    }

    $missings = [];
    $arr = $this->get_ingredients_missing();
    if (count($arr)) {
      foreach ($arr as $key => $itm) {
        $missings[] = [
          'id' => $itm->id,
          'quantity' => $itm->ingredient_quantity,
        ];

        $a1[$key] = $itm->id;
        $a2[$key] = $itm->ingredient_quantity;
      }

      array_multisort($a1, SORT_ASC, $a2, SORT_DESC, $missings);
    }

    return [
      'food_id' => $this->food_id,
      'note' => $this->note,
      'texts' => $texts,
      'missings' => $missings,
    ];
  }

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

  public function predict_reset()
  {
    $this->update([
      'food_id' => 0,
      'food_category_id' => 0,
      'confidence' => 0,
      'sys_predict' => 0,
      'sys_confidence' => 0,
      'usr_predict' => 0,
      'rbf_predict' => 0,
      'rbf_confidence' => 0,
      'rbf_retrain' => 0,
      'usr_edited' => 0,
      'deleted' => 0,

      'found_by' => NULL,
      'status' => 'scanned',
      'missing_ids' => NULL,
      'missing_texts' => NULL,
    ]);
  }

  public function predict_food($pars = [])
  {
    $api_core = new SysCore();

    $result = (array)json_decode($this->rbf_api, true);
    $notification = isset($pars['notification']) ? (bool)$pars['notification'] : true;
    $restaurant = $this->get_restaurant();

    if (count($result)) {

      $food = NULL;
      $foods = [];
      $ingredients_found = $api_core->sys_ingredients_found($result['predictions']);

      //find food
      $predictions = $result['predictions'];
      if (count($predictions)) {
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
      }

      $rbf_confidence = 0;
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
        $rbf_confidence = $foods['confidence'];
      }

      //found?
      if ($food) {
        $this->update([
          'food_id' => $food->id,
          'confidence' => $rbf_confidence,
          'rbf_confidence' => $rbf_confidence,
          'found_by' => 'rbf',
          'rbf_predict' => $food->id,

          'sys_predict' => 0,
          'sys_confidence' => 0,
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

      //refresh
      $food = Food::find($this->food_id);

      //other params
      $this->update([
        'food_category_id' => (int)$this->find_food_category($food),
        'total_seconds' => $result['time'],
        'status' => 'checked',

        'time_end' => date('Y-m-d H:i:s'),
      ]);

      if (!$food) {
        $this->update([
          'status' => 'failed',
        ]);
      }

      //find missing ingredients
      if ($food) {

        $ingredients_found = $food->get_ingredients_info([
          'restaurant_parent_id' => $restaurant->restaurant_parent_id,
          'ingredients' => $ingredients_found,
        ]);
        $ingredients_missing = $food->missing_ingredients([
          'restaurant_parent_id' => $restaurant->restaurant_parent_id,
          'ingredients' => $ingredients_found,
        ]);
        $this->add_ingredients_missing($food, $ingredients_missing, $notification);
      }
    }
  }

  public function find_food_category($food)
  {
    $count = 0;
    //check later
    return $count;

    if ($food) {
      $count = RestaurantFood::where('restaurant_id', $this->restaurant_id)
        ->where('food_id', $food->id)
        ->where('deleted', 0)
        ->pluck('food_category_id')
        ->first();
    }

    return (int)$count;
  }

  public function add_ingredients_missing($food, $ingredients = [], $notification = true)
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
    }

    $this->update_ingredients_missing_text();

    //notify
    if (count($ingredients) && $notification) {
      $users = $this->get_restaurant()->get_users();
      if (count($users)) {
        foreach ($users as $user) {

          //live_group
          $valid_group = true;
          if ($food->live_group > 1) {
            $valid_group = false;
          }
          if ($user->is_admin()) {
            $valid_group = true;
          }

          //user_setting
          $valid_setting = false;
          if ((int)$user->get_setting('missing_ingredient_receive')) {
            $valid_setting = true;
          }

          if (!$valid_group || !$valid_setting) {
            continue;
          }

          Notification::sendNow($user, new IngredientMissing([
            'type' => 'ingredient_missing',
            'restaurant_food_scan_id' => $this->id,
            'user' => $user,
          ]), ['database']);
        }
      }
    }
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

  public function get_comment($user = null)
  {
    $text = '';

    if ($user) {
      $row = Comment::where('deleted', 0)
        ->where('user_id', $user->id)
        ->where('object_id', $this->id)
        ->where('object_type', $this->get_type())
        ->first();
      if ($row) {
        $text = $row->content;
      }
    }

    return $text;
  }

  public function get_comments($pars = [])
  {
    $select = Comment::where('deleted', 0)
      ->where('object_id', $this->id)
      ->where('object_type', $this->get_type());

    if (count($pars) && isset($pars['order'])) {

    } else {
      $select->orderBy('id', 'asc');
    }

    return $select->get();
  }

  public function get_texts($pars = [])
  {
    $select = RestaurantFoodScanText::query('restaurant_food_scan_texts')
      ->where('restaurant_food_scan_texts.restaurant_food_scan_id', $this->id);

    if (count($pars)) {
      if (isset($pars['text_id_only'])) {
        $select->select('texts.id')
          ->leftJoin('texts', 'texts.id', '=', 'restaurant_food_scan_texts.text_id');
      }

      if (isset($pars['text_name_only'])) {
        $select->select('texts.name')
          ->leftJoin('texts', 'texts.id', '=', 'restaurant_food_scan_texts.text_id');
      }
    }

    return $select->get();
  }

  public function update_text_notes()
  {
    $ids = [];
    $texts = NULL;

    $select = RestaurantFoodScanText::query('restaurant_food_scan_texts')
      ->select('texts.id', 'texts.name')
      ->leftJoin('texts', 'texts.id', '=', 'restaurant_food_scan_texts.text_id')
      ->where('restaurant_food_scan_texts.restaurant_food_scan_id', $this->id);
    $notes = $select->get();
    if (count($notes)) {
      foreach ($notes as $note) {
        $ids[] = (int)$note['id'];
        $texts .= $note['name'] . ' &nbsp ';
      }
    }

    sort($ids);

    $this->update([
      'text_ids' => count($ids) ? $ids : NULL,
      'text_texts' => $texts,
    ]);
  }
}
