<?php

namespace App\Api;

use App\Models\Comment;
use App\Models\Food;
use App\Models\FoodCategory;
use App\Models\FoodIngredient;
use App\Models\Ingredient;
use App\Models\Log;
use App\Models\Restaurant;
use App\Models\RestaurantFoodScan;
use App\Models\RestaurantParent;
use App\Models\SysSetting;
use App\Models\Text;
use App\Models\User;

class SysApp
{

  public static function parse_s3_bucket_address($text)
  {
//    '58-5b-69-19-ad-67/SENSOR/1';

    if (!empty($text)) {
      $text = ltrim($text, '/');
    }
    if (!empty($text)) {
      $text = rtrim($text, '/');
    }

    return $text;
  }

  public static function str_rand($length = 8)
  {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
      $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
  }

  //db
  public static function get_item($item_id, $item_type)
  {
    $item = null;

    switch ($item_type) {
      case 'food_category':
        $item = FoodCategory::find((int)$item_id);
        break;
      case 'food':
        $item = Food::find((int)$item_id);
        break;
      case 'food_ingredients':
        $item = FoodIngredient::find((int)$item_id);
        break;
      case 'restaurant':
        $item = Restaurant::find((int)$item_id);
        break;
      case 'restaurant_parent':
        $item = RestaurantParent::find((int)$item_id);
        break;
      case 'restaurant_food_scan':
        $item = RestaurantFoodScan::find((int)$item_id);
        break;
      case 'ingredient':
        $item = Ingredient::find((int)$item_id);
        break;
      case 'log':
        $item = Log::find((int)$item_id);
        break;
      case 'comment':
        $item = Comment::find((int)$item_id);
        break;
      case 'user':
        $item = User::find((int)$item_id);
        break;
      case 'text':
        $item = Text::find((int)$item_id);
        break;
    }

    return $item;
  }

  public static function get_setting($key)
  {
    $row = SysSetting::where('key', $key)
      ->first();

    return $row ? $row->value : NULL;
  }

  public static function sys_stats_count()
  {
    //RestaurantParent
    //count_sensors, count_foods
    $rows = RestaurantParent::all();
    if (count($rows)) {
      foreach ($rows as $row) {
        $row->re_count();
      }
    }
  }


}
