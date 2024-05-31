<?php

namespace App\Api;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

use Aws\S3\S3Client;
use Aws\Polly\PollyClient;

use App\Jobs\PhotoGet;
use App\Jobs\PhotoScan;
use App\Jobs\PhotoPredict;

use App\Models\Restaurant;
use App\Models\RestaurantParent;
use App\Models\RestaurantFoodScan;
use App\Models\SysSetting;
use App\Models\SysBug;
use App\Models\Food;
use App\Models\Ingredient;
use App\Models\Comment;
use App\Models\Log;
use App\Models\Text;
use App\Models\User;
use App\Models\FoodCategory;

class SysCore
{

  public function rbf_retrain()
  {
//settings
    $rbf_dataset = $this->get_setting('rbf_dataset_upload');
    $rbf_api_key = $this->get_setting('rbf_api_key');

    //retrain rows
    $select = RestaurantFoodScan::where('deleted', 0)
      ->where('rbf_retrain', 1)
      ->orderBy('id', 'asc');

    $this::_DEBUG ? Storage::append($this::_DEBUG_LOG_FILE_ROBOFLOW, 'TODO_AT_' . date('d_M_Y_H_i_s')) : $this->log_failed();

    try {

      $rows = $select->get();

      if (count($rows)) {

        $count = 0;

        foreach ($rows as $row) {

          $this::_DEBUG ? Storage::append($this::_DEBUG_LOG_FILE_ROBOFLOW, 'ROW_' . $row->id . '_START_') : $this->log_failed();

          $count++;

          // URL for Http Request
          $url = "https://api.roboflow.com/dataset/"
            . $rbf_dataset . "/upload"
            . "?api_key=" . $rbf_api_key
            . "&name=re_training_" . date('Y_m_d_H_i_s') . "_" . $count . "." . $row->photo_ext
            . "&split=train"
            . "&image=" . urlencode($row->get_photo());

          // Setup + Send Http request
          $options = array(
            'http' => array(
              'header' => "Content-type: application/x-www-form-urlencoded\r\n",
              'method' => 'POST'
            ));

          $context = stream_context_create($options);
          $result = file_get_contents($url, false, $context);

          $this::_DEBUG ? Storage::append($this::_DEBUG_LOG_FILE_ROBOFLOW, 'ROW_' . $row->id . '_END_' . json_encode($result)) : $this->log_failed();

          if (!empty($result)) {
            $result = (array)json_decode($result);
          }

          $status = 3;
          if (count($result) && isset($result['id']) && !empty($result['id'])) {
            $status = 2;
          }

          $row->update([
            'rbf_retrain' => $status,
          ]);
        }
      }

    } catch (\Exception $e) {
      $this->bug_add([
        'type' => 'rbf_photo_retrain',
        'line' => $e->getLine(),
        'file' => $e->getFile(),
        'message' => $e->getMessage(),
        'params' => json_encode($e),
      ]);
    }

  }

  public function sys_ingredients_found($pars = [])
  {
    $arr = [];
    $existed = [];

    if (count($pars)) {
      foreach ($pars as $prediction) {
        $prediction = (array)$prediction;

        $ingredient = Ingredient::whereRaw('LOWER(name) LIKE ?', strtolower(trim($prediction['class'])))
          ->first();
        if ($ingredient) {

          if (in_array($ingredient->id, $existed)) {
            foreach ($arr as $k => $v) {
              if ($v['id'] == $ingredient->id) {
                $arr[$k]['quantity'] += 1;
              }
            }
          } else {
            $arr[] = [
              'id' => $ingredient->id,
              'quantity' => 1,
            ];
          }

          $existed[] = $ingredient->id;
        }
      }
    }

    return $arr;
  }

  public function sys_predict_foods_by_ingredients($ingredients = [], $one_food = false)
  {
    $arr = [];

    //no use anymore
    return $arr;

    $ingredients = array_map('current', $ingredients);

    //foods
    $foods = Food::where('deleted', 0)
      ->get();
    if (count($foods) && count($ingredients)) {
      foreach ($foods as $food) {
        $confidence = $food->check_food_confidence_by_ingredients($ingredients);
        if ($confidence && $confidence >= 80) {

          //check valid ingredient
          $valid_food = true;
          $food_ingredients = $food->get_ingredients();
          if (!count($food_ingredients)) {
            $valid_food = false;
          }

          //check core ingredient
          $valid_core = true;
          $core_ids = $food->get_ingredients_core([
            'ingredient_id_only' => 1,
          ]);
          if (count($core_ids)) {
            $found_ids = array_column($ingredients, 'id');
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
            $arr[] = [
              'food' => $food->id,
              'food_name' => $food->name,
              'confidence' => $confidence,
            ];
          }
        }
      }
    }

    if (count($arr)) {
      $a1 = [];
      $a2 = [];
      foreach ($arr as $key => $row) {
        $a1[$key] = $row['confidence'];
        $a2[$key] = $row['food'];
      }
      array_multisort($a1, SORT_DESC, $a2, SORT_DESC, $arr);

      if ($one_food) {
        $arr = $arr[0];
      }
    }

    return $arr;
  }

  public function get_notifications()
  {
    return [
      'missing_ingredient', 'photo_comment',
    ];
  }

}
