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



//    $URL1 = 'http://192.168.1.22:9001';
//    $image = 'https://s3.ap-southeast-1.amazonaws.com/cargo.tastevietnam.asia/58-5b-69-19-ad-67/SENSOR/1/2024-05-26/19/SENSOR_2024-05-26-19-59-04-857_065.jpg';
//
//    $URL = $URL1 . '/missing-dish-ingredients/29?api_key=uYUCzsUbWxWRrO15iar5&image=' . $image;
//
//    $curl = curl_init();
//
//    curl_setopt_array($curl, Array(
//      CURLOPT_URL            => $URL,
//      CURLOPT_RETURNTRANSFER => TRUE,
//      CURLOPT_ENCODING       => 'UTF-8'
//    ));
//
//    $data = curl_exec($curl);
//    curl_close($curl);
//
//    $data = (array)json_decode($data, true);
//
//    var_dump($data);

    echo '<br />';
    die('test ok...');
  }

}
