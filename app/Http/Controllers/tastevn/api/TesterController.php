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

    $row = RestaurantFoodScan::find(30451);
    $result1s = (array)json_decode($row->rbf_api, true);

    var_dump($result1s);

    echo '<br />';
    die('test ok...');
  }

}
