<?php

namespace App\Http\Controllers\tastevn\api;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

use Illuminate\Support\Facades\Notification;
use App\Notifications\IngredientMissing;
use App\Notifications\IngredientMissingMail;

use Maatwebsite\Excel\Facades\Excel;
use App\Excel\ExportFoodIngredient;
use App\Excel\ExportFoodRecipe;

use Validator;
use Aws\S3\S3Client;
use App\Api\SysCore;

use App\Models\User;
use App\Models\Restaurant;
use App\Models\RestaurantAccess;
use App\Models\Food;
use App\Models\Ingredient;
use App\Models\FoodIngredient;
use App\Models\SysSetting;
use App\Models\RestaurantFood;
use App\Models\RestaurantFoodScan;
use App\Models\Comment;
use App\Models\FoodRecipe;
use App\Models\FoodCategory;
use App\Models\Log;
use App\Models\SysNotification;

class TesterController extends Controller
{
  public function index(Request $request)
  {
    echo '<pre>';
    $api_core = new SysCore();



    echo '<br />';
    die('test ok...');
  }

  protected function export_food($pars = [])
  {
    $restaurant_parent_id = isset($pars['rpid']) ? (int)$pars['rpid'] : 1;
    $type = isset($pars['type']) ? $pars['type'] : 'ingredient';

    $items = [];

    $foods = Food::where('deleted', 0)
      ->orderBy('id', 'asc')
      ->get();

    if (count($foods)) {
      foreach ($foods as $food) {

        $items[] = [
          'c1' => $food->name,
          'c2' => '',
          'c3' => '',
          'c4' => '',
        ];

        if ($type == 'recipe') {

          $rows = FoodRecipe::where('food_id', $food->id)
            ->where('restaurant_parent_id', $restaurant_parent_id)
            ->where('deleted', 0)
            ->orderBy('ingredient_quantity', 'desc')
            ->get();
          if (count($rows)) {
            foreach ($rows as $row) {

              $ingredient = $row->get_ingredient();

              $items[] = [
                'c1' => '',
                'c2' => $ingredient->name,
              ];
            }
          }

        }
        else {
          //ingredient
          $rows = FoodIngredient::where('food_id', $food->id)
            ->where('restaurant_parent_id', $restaurant_parent_id)
            ->where('deleted', 0)
            ->orderBy('ingredient_type', 'asc')
            ->orderBy('ingredient_quantity', 'desc')
            ->get();
          if (count($rows)) {
            foreach ($rows as $row) {

              $ingredient = $row->get_ingredient();

              $items[] = [
                'c1' => '',
                'c2' => $ingredient->name,
                'c3' => $row->ingredient_quantity,
                'c4' => $row->ingredient_type == 'core' ? 'core' : '',
              ];
            }
          }
        }


      }
    }

    if (!count($items)) {
      die('no data');
    }


    return $items;
  }

  protected function s3_list_object()
  {
    $api_core = new SysCore();

    $s3_region = $api_core->get_setting('s3_region');
    $s3_api_key = $api_core->get_setting('s3_api_key');
    $s3_api_secret = $api_core->get_setting('s3_api_secret');

    $s3_bucket = 'market.tastevietnam.asisa';
    $s3_address = $api_core->parse_s3_bucket_address('58-5b-69-20-a8-f6/SENSOR/1/');

    $s3_api = new S3Client([
      'version' => 'latest',
      'region' => $s3_region,
      'credentials' => array(
        'key' => $s3_api_key,
        'secret' => $s3_api_secret
      )
    ]);

    $scan_date = '2024-05-02';
    $scan_hour = 15; //9 not 09

    $s3_objects = $s3_api->ListObjects([
      'Bucket' => $s3_bucket,
      'Delimiter' => '/',
//      'Prefix' => '58-5b-69-19-ad-67/SENSOR/1/2023-11-30/11/',
      'Prefix' => "{$s3_address}/{$scan_date}/{$scan_hour}/",
    ]);

    if ($s3_objects && isset($s3_objects['Contents']) && count($s3_objects['Contents'])) {

      //group
      $s3_contents = [];
      foreach ($s3_objects['Contents'] as $content) {

        var_dump('=========================================================');
        var_dump($content);
        var_dump('=========================');
        var_dump($content['LastModified']);
        var_dump('=========================');
        var_dump($content['LastModified']->format('Y-m-d H:i:s'));
        var_dump('=========================');
        $time_photo = date('Y-m-d H:i:s', strtotime($content['LastModified']->__toString()));
        var_dump($time_photo);

        $s3_contents[] = [
          'key' => $content['Key'],
          'date' => $content['LastModified']->format('Y-m-d H:i:s'),
        ];
      }

    }
  }

  protected function food_rbf_api_js()
  {
    $statuses = ['checked', 'edited'];

    $rows = RestaurantFoodScan::where('deleted', 0)
      ->whereIn('status', $statuses)
      ->where('missing_ids', '<>', NULL)
      ->whereDate('time_photo', '>=', date('Y-m-20'))
      ->whereDate('time_photo', '<', date('Y-m-21'))
      ->where('rbf_api', '<>', NULL)
      ->where('rbf_api_js', NULL)
      ->get();

    var_dump(count($rows));

    die('invalid date range...');
  }

  protected function food_rbf_api()
  {
    $statuses = ['checked', 'edited'];

    $rows = RestaurantFoodScan::where('deleted', 0)
      ->whereIn('status', $statuses)
      ->where('missing_ids', '<>', NULL)
//      ->whereDate('time_photo', '>=', date('Y-m-08'))
//      ->whereDate('time_photo', '>=', date('Y-m-01'))
//      ->whereDate('time_photo', '<', date('Y-m-06'))
      ->where('rbf_api', '<>', NULL)
      ->get();

    var_dump(count($rows));

    die('invalid date range...');

    foreach ($rows as $row) {

      $status = $row->status;
      $time_end = $row->time_end;
      $usr_edited = $row->usr_edited;
      $usr_predict = $row->usr_predict;

      $row->update([
        'food_id' => 0,
        'confidence' => 0,
        'found_by' => NULL,
        'missing_ids' => NULL,
        'missing_texts' => NULL,
        'sys_predict' => 0,
        'sys_confidence' => 0,
        'rbf_predict' => 0,
        'rbf_confidence' => 0,
      ]);

      $row->predict_food([
        'notification' => false,
      ]);

      if (in_array($row->status, $statuses)) {
        $row->update([
          'status' => $status == 'edited' ? $status : $row->status,
          'usr_edited' => $status == 'edited' ? $usr_edited : $row->usr_edited,
          'usr_predict' => $status == 'edited' ? $usr_predict : $row->usr_predict,
          'found_by' => $status == 'edited' ? 'usr' : $row->found_by,
        ]);
      }

      $row->update([
        'time_end' => $time_end,
      ]);
    }
  }

  protected function add_settings()
  {
    SysSetting::create([
      'key' => 's3_region',
      'value' => 'ap-southeast-1',
    ]);
    SysSetting::create([
      'key' => 's3_api_key',
      'value' => 'AKIASACMVORFIBZC3RRL',
    ]);
    SysSetting::create([
      'key' => 's3_api_secret',
      'value' => '7JIoo20VKKvhxZ456Gf4LSBCxlPKweVDTX0NiX+9',
    ]);
    SysSetting::create([
      'key' => 'rbf_api_key',
      'value' => 'uYUCzsUbWxWRrO15iar5',
    ]);
    SysSetting::create([
      'key' => 'rbf_dataset_scan',
      'value' => 'missing-dish-ingredients/7',
    ]);
    SysSetting::create([
      'key' => 'rbf_dataset_upload',
      'value' => 'missing-dish-ingredients',
    ]);
  }

}
