<?php

namespace App\Http\Controllers\tastevn\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

use Validator;
use App\Models\User;
use App\Models\Restaurant;
use App\Models\RestaurantAccess;
use App\Models\Food;
use App\Models\Ingredient;
use App\Models\FoodIngredient;
use App\Models\SysSetting;
use App\Models\RestaurantFood;
use App\Models\RestaurantFoodScan;

use Illuminate\Support\Facades\Notification;
use App\Notifications\IngredientMissing;

use Aws\S3\S3Client;

use App\Api\SysCore;

class TesterController extends Controller
{

  public function index(Request $request)
  {
    echo '<pre>';
    $api_core = new SysCore();



    //init db testing
//    $this->add_settings();

    echo '<br />';
    die('test ok...');
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
