<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
//lib
use App\Notifications\IngredientMissing;
use App\Notifications\IngredientMissingMail;
use App\Notifications\PhotoNote;
use Intervention\Image\ImageManagerStatic as Image;
use App\Api\SysApp;
use App\Api\SysRobo;

class RestaurantFoodScan extends Model
{
  use HasFactory;

  public $table = 'restaurant_food_scans';

  protected $fillable = [
    'restaurant_id',
    'food_category_id',
    'food_id',

    'local_storage',
    'photo_url',
    'photo_name',
    'photo_ext',
    'photo_main',

    'confidence',
    'found_by',
    'status',
    'note',
    'noter_id',

    'customer_requested',
    'count_foods',

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

    'is_marked',
    'is_resolved',

    'rbf_predict',
    'rbf_confidence',
    'rbf_retrain', //1= need retrain //2= retrain success //3= retrain failed
    'rbf_error',

    'rbf_api',
    'rbf_api_js',
    'rbf_version',
    'rbf_model',
    'rbf_api_1',
    'rbf_api_2',

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

  public function get_photo()
  {
    $photo = $this->photo_url;
    if ($this->local_storage || empty($photo)) {
      $photo = url('sensors') . '/' . $this->photo_name;
    }

    return $photo;
  }

  public function get_food()
  {
    return Food::find($this->food_id);
  }

  public function get_food_rbf()
  {
    return Food::find($this->rbf_predict);
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

  public function get_noter()
  {
    return User::find($this->noter_id);
  }

  //photooo
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
    if ($this->rbf_model) {

      $this->predict_food_2($pars);

      return false;
    }

    $sys_app = new SysApp();

    //reset
    $this->predict_reset();

    $debug = isset($pars['debug']) ? (bool)$pars['debug'] : false;
    $api_recall = isset($pars['api_recall']) ? (bool)$pars['api_recall'] : false;
    $notification = isset($pars['notification']) ? (bool)$pars['notification'] : true;
    $restaurant = $this->get_restaurant();

    $result1s = (array)json_decode($this->rbf_api, true);
    $result2s = (array)json_decode($this->rbf_api_js, true);

    $rbf_time = 0;
    if (count($result1s)) {
      $rbf_time = $result1s['time'];
    }

    $predictions = count($result1s) ? (array)$result1s['predictions'] : [];
    if (!count($predictions) && count($result2s)) {
      $predictions = $result2s;
    }

    if ($debug) {
      var_dump('====================================================== START ID= ' . $this->id);
    }

    if (!count($predictions)) {

      $this->update([
        'status' => 'failed',
        'time_end' => date('Y-m-d H:i:s'),
      ]);

      if ($debug) {
        var_dump('====== NO DATA...');
      }

      return false;
    }

    if ($debug) {
      var_dump('====== CLASSES= ' . json_encode($predictions));
    }

    //find foods
    $foods = SysRobo::foods_find([
      'predictions' => $predictions,
      'restaurant_parent_id' => $restaurant->restaurant_parent_id,
      'restaurant_id' => $restaurant->id,

      'debug' => $debug,
    ]);

    if ($debug) {
      var_dump('====== FOODS FIND?');
      var_dump($foods);
    }

    //food highest confidence
    $foods = SysRobo::foods_valid($foods, [
      'debug' => $debug,
      'predictions' => $predictions,
    ]);

    if ($debug) {
      var_dump('====== FOODS COMPACT?');
      var_dump($foods);
    }

    //pars
    $food = null;
    $status = 'failed';
    $end_time = date('Y-m-d H:i:s');

    if (count($foods) && $foods['food']) {

      $status = 'checked';
      $food = Food::find($foods['food']);

      $food_category = $food->get_category([
        'restaurant_parent_id' => $restaurant->restaurant_parent_id
      ]);

      $this->update([
        'food_id' => $foods['food'],
        'food_category_id' => $food_category ? $food_category->id : 0,
        'confidence' => $foods['confidence'],
        'rbf_confidence' => $foods['confidence'],
        'found_by' => 'rbf',
        'rbf_predict' => $foods['food'],
      ]);

      if ($debug) {
        var_dump('====== FOODS FOUND?');
        var_dump($foods);
      }
    }

    $this->update([
      'status' => $status,
      'total_seconds' => $rbf_time,
      'time_end' => !$api_recall ? $end_time : $this->time_end,
    ]);

    //find missing ingredients
    if ($food) {

      $ingredients_found = $food->get_ingredients_info([
        'restaurant_parent_id' => $restaurant->restaurant_parent_id,
        'predictions' => $predictions,

        'debug' => $debug,
      ]);

      if ($debug) {
        var_dump('====== INGREDIENT FOUND?');
        var_dump($ingredients_found);
      }

      $ingredients_missing = $food->missing_ingredients([
        'restaurant_parent_id' => $restaurant->restaurant_parent_id,
        'ingredients' => $ingredients_found,
      ]);

      if ($debug) {
        var_dump('====== INGREDIENT MISSING?');
        var_dump($ingredients_missing);
      }

      $this->add_ingredients_missing($food, $ingredients_missing, $notification);
    }
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

    $restaurant = $this->get_restaurant();
    $restaurant_parent = $this->get_restaurant()->get_parent();
    $live_group = $restaurant_parent->get_food_live_group($food);

    //notify
    if (count($ingredients) && $notification) {
      $users = $this->get_restaurant()->get_users();
      if (count($users)) {
        foreach ($users as $user) {

          //live_group
          $valid_group = true;
          if ($live_group > 1 || $this->confidence < 90) {
            $valid_group = false;
          }
          if ($user->is_super_admin() || $user->is_dev()) {
            $valid_group = true;
          }

          //isset notify
          $notify = DB::table('notifications')
            ->distinct()
            ->where('notifiable_type', 'App\Models\User')
            ->where('notifiable_id', $user->id)
            ->where('restaurant_food_scan_id', $this->id)
            ->whereIn('type', ['App\Notifications\IngredientMissing'])
            ->orderBy('id', 'desc')
            ->limit(1)
            ->first();

          if (!$valid_group || $notify) {
            continue;
          }

          //notify db
          Notification::sendNow($user, new IngredientMissing([
            'restaurant_food_scan_id' => $this->id,
          ]), ['database']);

          //notify mail
          if ((int)$user->get_setting('missing_ingredient_alert_email')) {
            $user->notify((new IngredientMissingMail([
              'type' => 'ingredient_missing',
              'restaurant_id' => $this->get_restaurant()->id,
              'restaurant_food_scan_id' => $this->id,
              'user' => $user,
            ]))->delay([
              'mail' => now()->addMinutes(5),
            ]));
          }

          //notify db update
          $rows = $user->notifications()
            ->whereIn('type', ['App\Notifications\IngredientMissing'])
            ->where('data', 'LIKE', '%{"restaurant_food_scan_id":' . $this->id . '}%')
            ->where('restaurant_food_scan_id', 0)
            ->get();
          if (count($rows)) {
            foreach ($rows as $row) {
              $notify = SysNotification::find($row->id);
              if ($notify) {
                $notify->update([
                  'restaurant_food_scan_id' => $this->id,
                  'restaurant_id' => $this->get_restaurant()->id,
                  'food_id' => $this->get_food() ? $this->get_food()->id : 0,
                  'object_type' => 'restaurant_food_scan',
                  'object_id' => $this->id,
                  'data' => json_encode([
                    'status' => 'valid'
                  ]),
                ]);
              }
            }
          }
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

  public function img_1024()
  {
    $sys_app = new SysApp();

    $sensor = $this->get_restaurant();

    $temps = array_filter(explode('/', $this->photo_name));
    $photo_name = $temps[count($temps) - 1];
    $photo_path = str_replace($photo_name, '', $this->photo_name);

    $path_1024 = $photo_path . '1024_' . $photo_name;
    $path_1024 = public_path('sensors') . '/' . $path_1024;
    $file_1024 = $sys_app->os_slash_file($path_1024);
    if (is_file($file_1024)) {
      return url('sensors') . '/' . $photo_path . '1024_' . $photo_name;
    }

    if (!$sensor->img_1024) {
      $path_img = public_path('sensors') . '/' . $this->photo_name;
      $file_img = $sys_app->os_slash_file($path_img);
      if (is_file($file_img)) {

        $thumb_1024 = Image::make($file_img);
        $thumb_1024->resize(1024, 1024, function ($constraint) {
          $constraint->aspectRatio();
        });

        $path_1024 = public_path('sensors') . '/' . $photo_path . '1024_' . $photo_name;
        $path_1024 = $sys_app->os_slash_file($path_1024);
        $thumb_1024->save($path_1024, 100);

        return url('sensors') . '/' . $photo_path . '1024_' . $photo_name;
      }
    }

    return $this->get_photo();
  }

  public function model_reset()
  {
    $this->update([

      'food_category_id' => 0,
      'food_id' => 0,
      'confidence' => 0,
      'found_by' => NULL,
      'total_seconds' => 0,
      'missing_ids' => NULL,
      'missing_texts' => NULL,

      'sys_predict' => 0,
      'sys_confidence' => 0,
      'usr_predict' => 0,
      'rbf_predict' => 0,
      'rbf_confidence' => 0,
      'usr_edited' => NULL,

      'status' => 'new',
      'rbf_api' => NULL,
      'rbf_api_js' => NULL,
      'rbf_version' => NULL,
      'rbf_model' => 0,
      'rbf_api_1' => NULL,
      'rbf_api_2' => NULL,
    ]);
  }

  public function model_api_1($pars = [])
  {
    $sys_app = new SysApp();

    $debug = isset($pars['debug']) ? (bool)$pars['debug'] : false;
    $api_recall = isset($pars['api_recall']) ? (bool)$pars['api_recall'] : false;
    $dataset = isset($pars['dataset']) ? $pars['dataset'] : $sys_app->get_setting('rbf_dataset_scan');
    $version = isset($pars['version']) ? $pars['version'] : $sys_app->get_setting('rbf_dataset_ver');

    $this->model_reset();

    $this->update([
      'rbf_model' => 3, //running
      'time_scan' => !$api_recall || empty($this->time_scan) ? date('Y-m-d H:i:s') : $this->time_scan,
    ]);

    if ($debug) {
      var_dump('====================================================== START ID= ' . $this->id);
    }

    $rbf_version = [
      'dataset' => $dataset,
      'version' => $version,
    ];

    $arr = SysRobo::photo_scan($this, $pars);

    $this->update([
      'status' => $arr['status'] ? 'scanned' : 'failed',
      'rbf_api' => $arr['status'] ? json_encode($arr['result']) : NULL,
      'rbf_version' => json_encode($rbf_version),
      'rbf_model' => 0,
    ]);

    if ($debug) {
      var_dump('====================================================== API CALL= ');
      var_dump($arr);
    }
  }

  public function model_api_2($pars = [])
  {
    $sys_app = new SysApp();

    $debug = isset($pars['debug']) ? (bool)$pars['debug'] : false;
    $api_recall = isset($pars['api_recall']) ? (bool)$pars['api_recall'] : false;
    $dataset = isset($pars['dataset']) ? $pars['dataset'] : $sys_app->get_setting('rbf_dataset_scan');
    $version = isset($pars['version']) ? $pars['version'] : $sys_app->get_setting('rbf_dataset_ver');

    $sensor = $this->get_restaurant();

    $img_no_resize = false;
    if (in_array($sensor->id, [8, 9, 10])) {
      $img_no_resize = true;
    }

    $this->model_reset();

    $this->update([
      'rbf_model' => 3, //running
      'time_scan' => !$api_recall || empty($this->time_scan) ? date('Y-m-d H:i:s') : $this->time_scan,
    ]);

    if ($debug) {
      var_dump('====================================================== START ID= ' . $this->id);
    }

    $rbf_version = [];
    $rbf_version[] = [
      'dataset' => $dataset,
      'version' => $version,
    ];

    $arr = SysRobo::photo_scan($this, [
      'dataset' => $dataset,
      'version' => $version,

      'confidence' => SysRobo::_SCAN_CONFIDENCE,
      'overlap' => SysRobo::_SCAN_OVERLAP,

      'img_no_resize' => $img_no_resize,
    ]);

    if ($debug) {
      var_dump('====================================================== FIRST CALL= ');
      var_dump($arr);
    }

    $this->update([
      'time_end' => date('Y-m-d H:i:s'),
      'status' => $arr['status'] ? 'scanned' : 'failed',
      'rbf_api_1' => $arr['status'] ? json_encode($arr['result']) : NULL,
      'rbf_version' => json_encode($rbf_version),
      'rbf_model' => 1,
    ]);

    if ($arr['status'] && count($arr['result'])) {

      if ($debug) {
        var_dump('====================================================== FIRST RESTAURANT= ' . $sensor->get_parent()->name);
        var_dump('====================================================== FIRST PREDICTIONS= ');
        var_dump($arr['result']['predictions']);
      }

      //find foods
      $foods = SysRobo::foods_find([
        'predictions' => $arr['result']['predictions'],
        'restaurant_parent_id' => $sensor->restaurant_parent_id,
        'restaurant_id' => $sensor->id,

        'food_only' => true,

        'debug' => $debug,
      ]);

      if ($debug) {
        var_dump('====================================================== FIRST FOODS= ');
        var_dump($foods);
      }

      //food highest confidence
      $foods = SysRobo::foods_valid($foods, [
        'debug' => $debug,
        'predictions' => $arr['result']['predictions'],
      ]);

      if ($debug) {
        var_dump('====================================================== FIRST FOOD= ');
        var_dump($foods);
      }

      if (count($foods) && $foods['food']) {

        $this->update([
          'rbf_model' => 3, //running
        ]);

        $food = Food::find($foods['food']);
        $model2_name = $food->get_model_name();
        $model2_version = $food->get_model_version();

        if ($debug) {
          var_dump('====================================================== FOOD NAME= ' . $food->name);
          var_dump('====================================================== FOOD CONFIDENCE= ' . $foods['confidence']);
          var_dump('====================================================== FOOD MODEL= ' . $model2_name . ' / ' . $model2_version);
        }

        $dataset = !empty($model2_name) ? $model2_name : $sys_app->get_setting('rbf_dataset_scan');
        $version = !empty($model2_version) ? $model2_version : $sys_app->get_setting('rbf_dataset_ver');

        $rbf_version[] = [
          'dataset' => $dataset,
          'version' => $version,
        ];

        $arr = SysRobo::photo_scan($this, [
          'dataset' => $dataset,
          'version' => $version,

          'confidence' => SysRobo::_SCAN_CONFIDENCE,
          'overlap' => SysRobo::_SCAN_OVERLAP,
        ]);

        if ($debug) {
          var_dump('====================================================== SECOND CALL= ');
          var_dump($arr);
        }

        $this->update([
          'time_end' => date('Y-m-d H:i:s'),
          'status' => $arr['status'] ? 'scanned' : 'failed',
          'rbf_api_2' => $arr['status'] ? json_encode($arr['result']) : NULL,
          'rbf_model' => 2,
          'rbf_version' => json_encode($rbf_version),
        ]);


      }
    }

  }

  public function predict_food_2($pars = [])
  {
    $sys_app = new SysApp();

    //reset
    $this->predict_reset();

    $debug = isset($pars['debug']) ? (bool)$pars['debug'] : false;
    $api_recall = isset($pars['api_recall']) ? (bool)$pars['api_recall'] : false;
    $notification = isset($pars['notification']) ? (bool)$pars['notification'] : true;
    $restaurant = $this->get_restaurant();

    $result1s = (array)json_decode($this->rbf_api_1, true);
    $result2s = (array)json_decode($this->rbf_api_2, true);

    $rbf_time = 0;
    if (count($result1s)) {
//      $rbf_time = $result1s['time'];
    }

    $prediction1s = count($result1s) ? (array)$result1s['predictions'] : [];
    $prediction2s = count($result2s) ? (array)$result2s['predictions'] : [];

    if ($debug) {
      var_dump('====================================================== START ID= ' . $this->id);
    }

    if (!count($prediction1s)) {

      $this->update([
        'status' => 'failed',
        'time_end' => date('Y-m-d H:i:s'),
      ]);

      if ($debug) {
        var_dump('====== NO DATA...');
      }

      return false;
    }

    if ($debug) {
      var_dump('====== PREDICTION 1= ');
      var_dump($prediction1s);
    }

    //find foods
    $foods = SysRobo::foods_find([
      'predictions' => $prediction1s,
      'restaurant_parent_id' => $restaurant->restaurant_parent_id,
      'restaurant_id' => $restaurant->id,

      'food_only' => true,

      'debug' => $debug,
    ]);

    if ($debug) {
      var_dump('====== FOODS FIND?');
      var_dump($foods);
    }

    //food highest confidence
    $foods = SysRobo::foods_valid($foods, [
      'debug' => $debug,
      'predictions' => $prediction1s,
    ]);

    if ($debug) {
      var_dump('====== FOODS COMPACT?');
      var_dump($foods);
    }

    //pars
    $food = null;
    $status = 'failed';
    $end_time = date('Y-m-d H:i:s');

    if (count($foods) && $foods['food']) {

      $status = 'checked';
      $food = Food::find($foods['food']);

      $food_category = $food->get_category([
        'restaurant_parent_id' => $restaurant->restaurant_parent_id
      ]);

      $this->update([
        'food_id' => $foods['food'],
        'food_category_id' => $food_category ? $food_category->id : 0,
        'confidence' => $foods['confidence'],
        'rbf_confidence' => $foods['confidence'],
        'found_by' => 'rbf',
        'rbf_predict' => $foods['food'],
      ]);

      if ($debug) {
        var_dump('====== FOODS FOUND?');
        var_dump($foods);
      }
    }

    $this->update([
      'status' => $status,
      'total_seconds' => $rbf_time,
      'time_end' => !$api_recall ? $end_time : $this->time_end,
    ]);

    if ($debug) {
      var_dump('====== PREDICTION 2= ');
      var_dump($prediction2s);
    }

    //find missing ingredients
    if ($food) {

      $ingredients_found = $food->get_ingredients_info([
        'restaurant_parent_id' => $restaurant->restaurant_parent_id,
        'predictions' => $prediction2s,

        'debug' => $debug,
      ]);

      if ($debug) {
        var_dump('====== INGREDIENT FOUND?');
        var_dump($ingredients_found);
      }

      $ingredients_missing = $food->missing_ingredients([
        'restaurant_parent_id' => $restaurant->restaurant_parent_id,
        'ingredients' => $ingredients_found,
      ]);

      if ($debug) {
        var_dump('====== INGREDIENT MISSING?');
        var_dump($ingredients_missing);
      }

      $this->add_ingredients_missing($food, $ingredients_missing, $notification);
    }
  }

  public function update_main_note($owner)
  {
    if ($owner) {
      $this->update([
        'noter_id' => $owner->id,
      ]);

      $users = $this->get_restaurant()->get_users();
      if (count($users)) {
        foreach ($users as $user) {
          //notify db
          Notification::send($user, new PhotoNote([
            'typed' => 'photo_note_update',
            'restaurant_food_scan_id' => $this->id,
            'owner_id' => $owner->id,
            'noted' => $this->note,
          ]));

          //notify db update
          $rows = $user->notifications()
            ->whereIn('type', ['App\Notifications\PhotoNote'])
            ->where('data', 'LIKE', '%{"typed":"photo_note_update","restaurant_food_scan_id":' . $this->id . ',%')
            ->where('restaurant_food_scan_id', 0)
            ->get();
          if (count($rows)) {
            foreach ($rows as $row) {
              $notify = SysNotification::find($row->id);
              if ($notify) {
                $notify->update([
                  'restaurant_food_scan_id' => $this->id,
                  'restaurant_id' => $this->get_restaurant()->id,
                  'food_id' => $this->get_food() ? $this->get_food()->id : 0,
                  'data' => json_encode([
                    'status' => 'valid',
                    'typed' => 'photo_comment_edit',
                    'owner_id' => $owner->id,
                    'noted' => $this->note,
                  ]),
                ]);
              }
            }
          }
        }
      }
    }
  }

  //cmt
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

  public function count_comments()
  {
    $count = count($this->get_comments());

    if (!empty($this->note)) {
      $count++;
    }

    return $count;
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
