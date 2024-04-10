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

    $rows = RestaurantFoodScan::where('deleted', 0)
      ->where('status', 'checked')
      ->whereMonth('created_at', (int)date('m'))
      ->whereYear('created_at', (int)date('Y'))
      ->get();
    if (count($rows)) {
      foreach ($rows as $row) {

        $row->update([
          'found_by' => NULL,
          'missing_ids' => NULL,
          'missing_texts' => NULL,
          'food_id' => 0,
          'confidence' => 0,
          'sys_predict' => 0,
          'sys_confidence' => 0,
          'rbf_predict' => 0,
          'rbf_confidence' => 0,
        ]);

        $this->predict_food_clone($row);
      }
    }

    //init db testing
//    $this->add_food();
//    $this->add_ingredient();
//    $this->add_food_ingredients();
//    $this->add_settings();

    echo '<br />';
    die('test ok...');
  }

  protected function predict_food_clone($row)
  {
    $result = (array)json_decode($row->rbf_api, true);
    $api_core = new SysCore();

    if (count($result)) {

      $food = NULL;
      $ingredients_found = $api_core->sys_ingredients_found($result['predictions']);

      $foods = [];
      //find food
      $predictions = $result['predictions'];
      if (count($predictions)) {
        foreach ($predictions as $prediction) {
          $prediction = (array)$prediction;

          $food = Food::whereRaw('LOWER(name) LIKE ?', strtolower(trim($prediction['class'])))
            ->first();
          if ($food) {
            $foods[] = [
              'food' => $food->id,
              'confidence' => (int)($prediction['confidence'] * 100),
            ];
          }
        }
      }
      $rbf_confidence = 0;
      if (count($foods)) {
        if (count($foods) > 1) {
          $a1 = [];
          $a2 = [];
          foreach ($foods as $key => $row) {
            $a1[$key] = $row['confidence'];
            $a2[$key] = $row['food'];
          }
          array_multisort($a1, SORT_DESC, $a2, SORT_DESC, $foods);

          $foods = $foods[0];
        }

        $food = Food::find($foods['food']);
        $rbf_confidence = $foods['confidence'];
      }
      //found?
      if ($food) {
        $row->update([
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
          $row->update([
            'food_id' => $predict['food'],
            'confidence' => (int)$predict['confidence'],
            'sys_confidence' => (int)$predict['confidence'],
            'found_by' => 'sys',
            'sys_predict' => $predict['food'],
          ]);
        }
      }

      $food = Food::find($row->food_id);
      //find missing ingredients
      if ($food) {

        $ingredients_found = $food->get_ingredients_info($ingredients_found);
        $ingredients_missing = $food->missing_ingredients($ingredients_found);
        $row->add_ingredients_missing($ingredients_missing, false);
      }

      //other params
      $row->update([
        'food_category_id' => (int)$row->find_food_category($food),
        'total_seconds' => $result['time'],
        'status' => 'checked',
        //temporary off
        'time_scan' => $row->time_photo,
      ]);

      if (!$food) {
        $row->update([
          'status' => 'failed',
        ]);
      }

    }
  }

  protected function get_s3_bucket_files()
  {
    $s3_region = 'ap-southeast-1';
    $s3_bucket = 'cargo.tastevietnam.asia';
    $s3_address = '58-5b-69-19-ad-67/SENSOR/1';

    $scan_date = date("2023-12-09");
    $scan_hour = 19;

    $s3_api = new S3Client([
      'version' => 'latest',
      'region' => $s3_region,
      'credentials' => array(
        'key' => 'AKIASACMVORFIBZC3RRL',
        'secret' => '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'
      )
    ]);
    $s3_objects = $s3_api->ListObjects([
      'Bucket' => $s3_bucket,
      'Delimiter' => '/',
//      'Prefix' => '58-5b-69-19-ad-67/SENSOR/1/2023-11-30/11/',
      'Prefix' => "{$s3_address}/{$scan_date}/{$scan_hour}/",

    ]);
//    var_dump($s3_objects);

    if ($s3_objects && isset($s3_objects['Contents']) && count($s3_objects['Contents'])) {
      foreach ($s3_objects['Contents'] as $content) {
        echo '=========================================================================================';
        echo '<br />';
//      echo $content['Key'] . "\n";

//      $URL = "https://s3.ap-southeast-1.amazonaws.com/cargo.tastevietnam.asia/58-5b-69-19-ad-67/SENSOR/1/2023-12-09/20/SENSOR_2023-12-09-20-00-15-376_056.jpg";
        $URL = "https://s3.{$s3_region}.amazonaws.com/{$s3_bucket}/{$content['Key']}";
        if (@getimagesize($URL)) {
          var_dump($URL);

          var_dump($content['LastModified']->__toString());

          var_dump(date('Y-m-d H:i:s', strtotime($content['LastModified']->__toString())));

          $exts = explode('.', $content['Key']);
          var_dump($exts[1]);
        }

      }
    }

    die;
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

  protected function add_food_ingredients()
  {
    $foods = [
      'American Breakfast' => [
        [
          'name' => 'Sour Bread',
          'type' => 'core',
          'quantity' => 1,
        ],
        [
          'name' => 'Green Lettuce',
          'type' => '',
          'quantity' => 1,
        ],
        [
          'name' => 'Purple lettuce',
          'type' => '',
          'quantity' => 1,
        ],
        [
          'name' => 'Hashbrown',
          'type' => 'core',
          'quantity' => 1,
        ],
        [
          'name' => 'Pork Sausage',
          'type' => 'core',
          'quantity' => 1,
        ],
        [
          'name' => 'Bacon',
          'type' => 'core',
          'quantity' => 1,
        ],
        [
          'name' => 'Mashed Avocado',
          'type' => '',
          'quantity' => 1,
        ],
        [
          'name' => 'Fried egg',
          'type' => 'core',
          'quantity' => 2,
        ],
        [
          'name' => 'Sauteed Mushroom',
          'type' => '',
          'quantity' => 1,
        ],
        [
          'name' => 'Salsa sauce',
          'type' => '',
          'quantity' => 1,
        ],
        [
          'name' => 'Grilled Tomato',
          'type' => '',
          'quantity' => 1,
        ],
      ]
    ];
    foreach ($foods as $food => $ingredients) {
      $row1 = Food::where('name', $food)
        ->first();

      if ($row1) {
        foreach ($ingredients as $ingredient) {
          $row2 = Ingredient::where('name', $ingredient['name'])
            ->first();
          if ($row2) {
            FoodIngredient::create([
              'food_id' => $row1->id,
              'ingredient_id' => $row2->id,
              'ingredient_type' => $ingredient['type'] == 'core' ? 'core' : 'additive',
              'ingredient_quantity' => $ingredient['quantity'],
            ]);
          }
        }
      }
    }

    $foods = [
      'Breakfast Stack' => [
        [
          'name' => 'Potato Rosti',
          'type' => 'core',
          'quantity' => 1,
        ],
        [
          'name' => 'Bacon Bit',
          'type' => 'core',
          'quantity' => 1,
        ],
        [
          'name' => 'Parsley',
          'type' => '',
          'quantity' => 1,
        ],
        [
          'name' => 'Sunny side up egg',
          'type' => 'core',
          'quantity' => 1,
        ],
      ],
    ];
    foreach ($foods as $food => $ingredients) {
      $row1 = Food::where('name', $food)
        ->first();

      if ($row1) {
        foreach ($ingredients as $ingredient) {
          $row2 = Ingredient::where('name', $ingredient['name'])
            ->first();
          if ($row2) {
            FoodIngredient::create([
              'food_id' => $row1->id,
              'ingredient_id' => $row2->id,
              'ingredient_type' => $ingredient['type'] == 'core' ? 'core' : 'additive',
              'ingredient_quantity' => $ingredient['quantity'],
            ]);
          }
        }
      }
    }

    $foods = [
      'Healthy Breakfast' => [
        [
          'name' => 'Sour Bread',
          'type' => 'core',
          'quantity' => 1,
        ],
        [
          'name' => 'Green Lettuce',
          'type' => '',
          'quantity' => 1,
        ],
        [
          'name' => 'Purple lettuce',
          'type' => '',
          'quantity' => 1,
        ],
        [
          'name' => 'Grilled Tomato',
          'type' => '',
          'quantity' => 2,
        ],
        [
          'name' => 'Sliced Avocado',
          'type' => '',
          'quantity' => 1,
        ],
        [
          'name' => 'Poached eggs',
          'type' => 'core',
          'quantity' => 2,
        ],
        [
          'name' => 'Holandaise sauce',
          'type' => 'core',
          'quantity' => 1,
        ],
        [
          'name' => 'Mixed Fruits',
          'type' => 'core',
          'quantity' => 1,
        ],
        [
          'name' => 'Yogurt',
          'type' => '',
          'quantity' => 1,
        ],
        [
          'name' => 'Salsa sauce',
          'type' => '',
          'quantity' => 1,
        ],
      ],
    ];
    foreach ($foods as $food => $ingredients) {
      $row1 = Food::where('name', $food)
        ->first();

      if ($row1) {
        foreach ($ingredients as $ingredient) {
          $row2 = Ingredient::where('name', $ingredient['name'])
            ->first();
          if ($row2) {
            FoodIngredient::create([
              'food_id' => $row1->id,
              'ingredient_id' => $row2->id,
              'ingredient_type' => $ingredient['type'] == 'core' ? 'core' : 'additive',
              'ingredient_quantity' => $ingredient['quantity'],
            ]);
          }
        }
      }
    }

    $foods = [
      'Scrambled Eggs' => [
        [
          'name' => 'Sour Bread',
          'type' => '',
          'quantity' => 1,
        ],
        [
          'name' => 'Green Lettuce',
          'type' => '',
          'quantity' => 1,
        ],
        [
          'name' => 'Purple lettuce',
          'type' => '',
          'quantity' => 1,
        ],
        [
          'name' => 'Grilled Tomato',
          'type' => '',
          'quantity' => 2,
        ],
        [
          'name' => 'Scramble egg',
          'type' => 'core',
          'quantity' => 1,
        ],
        [
          'name' => 'Salsa sauce',
          'type' => '',
          'quantity' => 1,
        ],
      ],
    ];
    foreach ($foods as $food => $ingredients) {
      $row1 = Food::where('name', $food)
        ->first();

      if ($row1) {
        foreach ($ingredients as $ingredient) {
          $row2 = Ingredient::where('name', $ingredient['name'])
            ->first();
          if ($row2) {
            FoodIngredient::create([
              'food_id' => $row1->id,
              'ingredient_id' => $row2->id,
              'ingredient_type' => $ingredient['type'] == 'core' ? 'core' : 'additive',
              'ingredient_quantity' => $ingredient['quantity'],
            ]);
          }
        }
      }
    }

    $foods = [
      'Prawn Salad' => [
        [
          'name' => 'Mixed Salad',
          'type' => '',
          'quantity' => 1,
        ],
        [
          'name' => 'Avocado Cut',
          'type' => '',
          'quantity' => 1,
        ],
        [
          'name' => 'Orange segment',
          'type' => '',
          'quantity' => 1,
        ],
        [
          'name' => 'Cut black olive ',
          'type' => '',
          'quantity' => 1,
        ],
        [
          'name' => 'Grilled prawn ',
          'type' => '',
          'quantity' => 1,
        ],
        [
          'name' => 'Sundried tomato',
          'type' => '',
          'quantity' => 1,
        ],
        [
          'name' => 'Red onion sliced',
          'type' => '',
          'quantity' => 1,
        ],
        [
          'name' => 'Dill',
          'type' => '',
          'quantity' => 1,
        ],
        [
          'name' => 'Salad Dressing',
          'type' => '',
          'quantity' => 1,
        ],
      ],
    ];
    foreach ($foods as $food => $ingredients) {
      $row1 = Food::where('name', $food)
        ->first();

      if ($row1) {
        foreach ($ingredients as $ingredient) {
          $row2 = Ingredient::where('name', $ingredient['name'])
            ->first();
          if ($row2) {
            FoodIngredient::create([
              'food_id' => $row1->id,
              'ingredient_id' => $row2->id,
              'ingredient_type' => $ingredient['type'] == 'core' ? 'core' : 'additive',
              'ingredient_quantity' => $ingredient['quantity'],
            ]);
          }
        }
      }
    }

    $foods = [
      'Pizza Frutti Di Mare' => [
        [
          'name' => 'Đế Pizza',
          'type' => '',
          'quantity' => 1,
        ],
        [
          'name' => 'Mozzarella Pizza',
          'type' => '',
          'quantity' => 1,
        ],
        [
          'name' => 'Squid sliced',
          'type' => '',
          'quantity' => 1,
        ],
        [
          'name' => 'Prawn pelled cut 1/2',
          'type' => '',
          'quantity' => 1,
        ],
        [
          'name' => 'Cherry Tomatoes 1/2 cut',
          'type' => '',
          'quantity' => 1,
        ],
        [
          'name' => 'Garlic pelled ',
          'type' => '',
          'quantity' => 1,
        ],
        [
          'name' => 'Chopped Parsley',
          'type' => '',
          'quantity' => 1,
        ],
      ],
    ];
    foreach ($foods as $food => $ingredients) {
      $row1 = Food::where('name', $food)
        ->first();

      if ($row1) {
        foreach ($ingredients as $ingredient) {
          $row2 = Ingredient::where('name', $ingredient['name'])
            ->first();
          if ($row2) {
            FoodIngredient::create([
              'food_id' => $row1->id,
              'ingredient_id' => $row2->id,
              'ingredient_type' => $ingredient['type'] == 'core' ? 'core' : 'additive',
              'ingredient_quantity' => $ingredient['quantity'],
            ]);
          }
        }
      }
    }

    $foods = [
      'Mexicana Pizza' => [
        [
          'name' => 'Đế Pizza',
          'type' => '',
          'quantity' => 1,
        ],
        [
          'name' => 'Tomato jar',
          'type' => '',
          'quantity' => 1,
        ],
        [
          'name' => 'Mozzarella Pizza',
          'type' => '',
          'quantity' => 1,
        ],
        [
          'name' => 'Salami Napoli sliced',
          'type' => '',
          'quantity' => 1,
        ],
        [
          'name' => 'Japaleno chili sliced',
          'type' => '',
          'quantity' => 1,
        ],
        [
          'name' => 'Basil leaf',
          'type' => '',
          'quantity' => 1,
        ],
        [
          'name' => 'Basil leaf',
          'type' => '',
          'quantity' => 1,
        ],
      ],
    ];
    foreach ($foods as $food => $ingredients) {
      $row1 = Food::where('name', $food)
        ->first();

      if ($row1) {
        foreach ($ingredients as $ingredient) {
          $row2 = Ingredient::where('name', $ingredient['name'])
            ->first();
          if ($row2) {
            FoodIngredient::create([
              'food_id' => $row1->id,
              'ingredient_id' => $row2->id,
              'ingredient_type' => $ingredient['type'] == 'core' ? 'core' : 'additive',
              'ingredient_quantity' => $ingredient['quantity'],
            ]);
          }
        }
      }
    }

    $foods = [
      'Angus Striploin Steak' => [
        [
          'name' => 'Grilled striploin',
          'type' => '',
          'quantity' => 1,
        ],
        [
          'name' => 'Pepper sauce',
          'type' => '',
          'quantity' => 1,
        ],
        [
          'name' => 'Mixed capsicum onion',
          'type' => '',
          'quantity' => 1,
        ],
        [
          'name' => 'French fries',
          'type' => '',
          'quantity' => 1,
        ],
      ],
    ];
    foreach ($foods as $food => $ingredients) {
      $row1 = Food::where('name', $food)
        ->first();

      if ($row1) {
        foreach ($ingredients as $ingredient) {
          $row2 = Ingredient::where('name', $ingredient['name'])
            ->first();
          if ($row2) {
            FoodIngredient::create([
              'food_id' => $row1->id,
              'ingredient_id' => $row2->id,
              'ingredient_type' => $ingredient['type'] == 'core' ? 'core' : 'additive',
              'ingredient_quantity' => $ingredient['quantity'],
            ]);
          }
        }
      }
    }

    $foods = [
      'Chicken Parmigiana' => [
        [
          'name' => 'French fries',
          'type' => '',
          'quantity' => 1,
        ],
        [
          'name' => 'Mixed salad',
          'type' => '',
          'quantity' => 1,
        ],
        [
          'name' => 'Baked chicken',
          'type' => '',
          'quantity' => 1,
        ],
      ],
    ];
    foreach ($foods as $food => $ingredients) {
      $row1 = Food::where('name', $food)
        ->first();

      if ($row1) {
        foreach ($ingredients as $ingredient) {
          $row2 = Ingredient::where('name', $ingredient['name'])
            ->first();
          if ($row2) {
            FoodIngredient::create([
              'food_id' => $row1->id,
              'ingredient_id' => $row2->id,
              'ingredient_type' => $ingredient['type'] == 'core' ? 'core' : 'additive',
              'ingredient_quantity' => $ingredient['quantity'],
            ]);
          }
        }
      }
    }

    $foods = [
      'King Prawn Salad' => [
        [
          'name' => 'Mixed Salad',
          'type' => '',
          'quantity' => 1,
        ],
        [
          'name' => 'Avocado cut',
          'type' => '',
          'quantity' => 1,
        ],
        [
          'name' => 'Orange segment',
          'type' => '',
          'quantity' => 1,
        ],
        [
          'name' => 'Cut black olive',
          'type' => '',
          'quantity' => 1,
        ],
        [
          'name' => 'Grilled prawn',
          'type' => '',
          'quantity' => 1,
        ],
        [
          'name' => 'Sundried tomato',
          'type' => '',
          'quantity' => 1,
        ],
        [
          'name' => 'Red onion sliced',
          'type' => '',
          'quantity' => 1,
        ],
        [
          'name' => 'Dill',
          'type' => '',
          'quantity' => 1,
        ],
        [
          'name' => 'Salad Dressing',
          'type' => '',
          'quantity' => 1,
        ],
      ],
    ];
    foreach ($foods as $food => $ingredients) {
      $row1 = Food::where('name', $food)
        ->first();

      if ($row1) {
        foreach ($ingredients as $ingredient) {
          $row2 = Ingredient::where('name', $ingredient['name'])
            ->first();
          if ($row2) {
            FoodIngredient::create([
              'food_id' => $row1->id,
              'ingredient_id' => $row2->id,
              'ingredient_type' => $ingredient['type'] == 'core' ? 'core' : 'additive',
              'ingredient_quantity' => $ingredient['quantity'],
            ]);
          }
        }
      }
    }

  }

  protected function add_food()
  {
    $items = [
      'American Breakfast', 'Breakfast Stack', 'Healthy Breakfast',
      'Scrambled Eggs', 'Prawn Salad', 'Pizza Frutti Di Mare',
      'Mexicana Pizza', 'Angus Striploin Steak', 'Chicken Parmigiana',
      'King Prawn Salad',
    ];

    foreach ($items as $item) {
      Food::create([
        'name' => $item,
      ]);
    }
  }

  protected function add_ingredient()
  {
    $items = [
      [
        'name' => 'Sour Bread',
        'name_vi' => 'Bánh mì chua',
      ],
      [
        'name' => 'Green Lettuce',
        'name_vi' => 'Xà lách xanh',
      ],
      [
        'name' => 'Purple lettuce',
        'name_vi' => 'Xà lách tím',
      ],
      [
        'name' => 'Hashbrown',
        'name_vi' => 'bánh khoai tây',
      ],
      [
        'name' => 'Pork Sausage',
        'name_vi' => 'xúc xích heo',
      ],
      [
        'name' => 'Bacon',
        'name_vi' => 'ba chỉ xông khói',
      ],
      [
        'name' => 'Mashed Avocado',
        'name_vi' => 'bơ dằm',
      ],

      [
        'name' => 'Fried egg',
        'name_vi' => 'Trứng chiên',
      ],
      [
        'name' => 'Sauteed Mushroom',
        'name_vi' => 'Nấm Xào',
      ],
      [
        'name' => 'Salsa sauce',
        'name_vi' => 'Sốt salsa',
      ],
      [
        'name' => 'Grilled Tomato',
        'name_vi' => 'Cà Chua Nướng',
      ],
      [
        'name' => 'Potato Rosti',
        'name_vi' => 'Bánh khoai tây',
      ],
      [
        'name' => 'Bacon Bit',
        'name_vi' => 'thịt xông khói vụn ',
      ],
      [
        'name' => 'Parsley',
        'name_vi' => 'Ngò tây',
      ],
      [
        'name' => 'Sunny side up egg',
        'name_vi' => 'Trứng ốp la',
      ],
      [
        'name' => 'Poached eggs',
        'name_vi' => 'Trứng trần',
      ],
      [
        'name' => 'Holandaise sauce',
        'name_vi' => 'Sốt bơ hollandaise',
      ],
      [
        'name' => 'Mixed Fruits',
        'name_vi' => 'Trái cây trộn',
      ],
      [
        'name' => 'Yogurt',
        'name_vi' => 'Ya-ua',
      ],
      [
        'name' => 'Scramble egg',
        'name_vi' => 'Trứng chiên khuấy',
      ],
      [
        'name' => 'Mixed Salad',
        'name_vi' => 'Xà lách hỗn hợp',
      ],
      [
        'name' => 'Avocado Cut',
        'name_vi' => 'Bơ cắt',
      ],
      [
        'name' => 'Orange segment',
        'name_vi' => 'Cam cắt múi cau',
      ],
      [
        'name' => 'Cut black olive ',
        'name_vi' => 'Trái oliu cắt',
      ],
      [
        'name' => 'Grilled prawn ',
        'name_vi' => 'Tôm nướng',
      ],
      [
        'name' => 'Sundried tomato',
        'name_vi' => 'Cà chua khô',
      ],
      [
        'name' => 'Red onion sliced',
        'name_vi' => 'Hành tây tím cắt khoanh',
      ],
      [
        'name' => 'Dill',
        'name_vi' => 'Thì là',
      ],
      [
        'name' => 'Salad Dressing ',
        'name_vi' => 'Sốt salad',
      ],
      [
        'name' => 'Đế Pizza',
        'name_vi' => 'Đế Pizza',
      ],
      [
        'name' => 'Mozzarella Pizza',
        'name_vi' => 'Phô mai mozzarella',
      ],
      [
        'name' => 'Squid sliced',
        'name_vi' => 'Mực ống lát',
      ],
      [
        'name' => 'Prawn pelled cut 1/2',
        'name_vi' => 'Tôm tươi cắt 1/2',
      ],
      [
        'name' => 'Cherry Tomatoes 1/2 cut',
        'name_vi' => 'Cà chua bi cắt 1/2',
      ],
      [
        'name' => 'Garlic pelled',
        'name_vi' => 'Tỏi nhánh lột',
      ],
      [
        'name' => 'Chopped Parsley',
        'name_vi' => 'Ngò tây bằm',
      ],
      [
        'name' => 'Tomato jar',
        'name_vi' => 'Cà chua hộp',
      ],
      [
        'name' => 'Salami Napoli sliced',
        'name_vi' => 'Salami bào',
      ],
      [
        'name' => 'Japaleno chili sliced',
        'name_vi' => 'Ớt japanleno cắt lát',
      ],
      [
        'name' => 'Basil leaf',
        'name_vi' => 'Lá quế tây',
      ],
      [
        'name' => 'Black olives sliced',
        'name_vi' => 'Oliu đen cắt',
      ],
      [
        'name' => 'Grilled striploin',
        'name_vi' => 'Thịt bò thăn ngoại nướng',
      ],
      [
        'name' => 'Pepper sauce',
        'name_vi' => 'Sốt tiêu',
      ],
      [
        'name' => 'Mixed capsicum onion',
        'name_vi' => 'Hành ớt trộn',
      ],
      [
        'name' => 'French fries',
        'name_vi' => 'Khoai tây chiên',
      ],
      [
        'name' => 'Baked chicken',
        'name_vi' => 'Gà đút lò',
      ],
    ];

    foreach ($items as $item) {
      Ingredient::create([
        'name' => $item['name'],
        'name_vi' => $item['name_vi'],
      ]);
    }
  }
}
