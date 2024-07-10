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
use App\Api\SysCore;
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
          'id' => $itm['id'],
          'quantity' => $itm['quantity'],
        ];

        $a1[$key] = $itm['id'];
        $a2[$key] = $itm['quantity'];
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

  public function get_noter()
  {
    return User::find($this->noter_id);
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

  //v3
  public function photo_sensor()
  {
    //s3 origin
    return $this->photo_url;
  }

  public function photo_1024_create()
  {
    $photo_url = url('sensors') . '/' . $this->photo_name;

    $file_photo = public_path('sensors') . '/' . $this->photo_name;
    $file_photo = SysCore::os_slash_file($file_photo);
    if (is_file($file_photo)) {

      //create 1024 from sensor photo
      $thumb_1024 = Image::make($file_photo);
      $thumb_1024->resize(1024, 1024, function ($constraint) {
        $constraint->aspectRatio();
      });

      $temps = array_filter(explode('/', $this->photo_name));
      $photo_name = $temps[count($temps) - 1];
      $photo_path = str_replace($photo_name, '', $this->photo_name);
      $photo_name_1024 = '1024_' . $photo_name;
      $path_1024 = $photo_path . $photo_name_1024;

      $file_1024 = public_path('sensors') . '/' . $path_1024;
      $file_1024 = SysCore::os_slash_file($file_1024);
      $thumb_1024->save($file_1024, 100);

      $photo_url = url('sensors') . '/' . $path_1024;
    }

    return $photo_url;
  }

  public function photo_1024()
  {
    $sensor = $this->get_restaurant();
    $photo_url = NULL;

    $temps = array_filter(explode('/', $this->photo_name));
    $photo_name = $temps[count($temps) - 1];
    $photo_path = str_replace($photo_name, '', $this->photo_name);
    $photo_name_1024 = '1024_' . $photo_name;
    $path_1024 = $photo_path . $photo_name_1024;

    if ($this->local_storage) {
      $file_1024 = public_path('sensors') . '/' . $path_1024;
      $file_1024 = SysCore::os_slash_file($file_1024);

      if (is_file($file_1024)) {
        $photo_url = url('sensors') . '/' . $path_1024;
      }
      else {
        $photo_url = $this->photo_1024_create();
      }
    }
    else {

      $photo_url = $this->photo_sensor();

      $photo_path = str_replace($photo_name, '', $photo_url);
      $url_1024 = $photo_path . $photo_name_1024;

      if (@getimagesize($url_1024)) {
        $photo_url = $url_1024;
      }
    }

    return $photo_url;
  }

  public function rfs_photo_scan_before()
  {
    //keep
    //time_photo
    //time_scan
    //time_end

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

  public function rfs_photo_scan($pars = [])
  {
    $this->rfs_photo_scan_before();

    //model 1
    $api_key = SysCore::get_sys_setting('rbf_api_key');
    $dataset = SysCore::str_trim_slash(SysCore::get_sys_setting('rbf_dataset_scan'));
    $version = SysCore::get_sys_setting('rbf_dataset_ver');

    $debug = false;

    //img_1024
    $img_url = $this->photo_1024();

    //time_scan
    if (empty($this->time_scan)) {
      $this->update([
        'time_scan' => date('Y-m-d H:i:s'),
      ]);
    }

    $datas = SysRobo::photo_scan([
      'img_url' => $img_url,

      'api_key' => $api_key,
      'dataset' => $dataset,
      'version' => $version,

      'confidence' => SysRobo::_RBF_CONFIDENCE,
      'overlap' => SysRobo::_RBF_OVERLAP,
      'max_objects' => SysRobo::_RBF_MAX_OBJECTS,

      'debug' => $debug,
    ]);
//    var_dump($datas);

    $no_data = false;
    if (!count($datas) || !$datas['status']
      || ($datas['status'] && (!isset($datas['result']['predictions'])) || !count($datas['result']['predictions']))) {
      $no_data = true;
    }

    $this->update([
      'status' => $no_data ? 'failed' : 'scanned',
      'total_seconds' => isset($datas['result']['time']) ? $datas['result']['time'] : $this->total_seconds,
      'rbf_api' => json_encode($datas),
      'rbf_version' => json_encode([
        'dataset' => $dataset,
        'version' => $version,
      ]),
    ]);

    if (!$no_data) {
      $this->rfs_photo_predict($pars);
    }

    //time_end
    if (empty($this->time_end)) {
      $this->update([
        'time_end' => date('Y-m-d H:i:s'),
      ]);
    }
  }

  public function rfs_photo_scan_after()
  {

  }

  public function rfs_photo_predict_before()
  {
    //keep
    //time_photo
    //time_scan
    //time_end

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

//      'status' => 'new',
//      'rbf_api' => NULL,
//      'rbf_api_js' => NULL,
//      'rbf_version' => NULL,
//      'rbf_model' => 0,
//      'rbf_api_1' => NULL,
//      'rbf_api_2' => NULL,
    ]);

    //notify
    DB::table('notifications')
      ->distinct()
      ->where('notifiable_type', 'App\Models\User')
      ->whereIn('type', ['App\Notifications\IngredientMissing'])
      ->where('restaurant_food_scan_id', $this->id)
      ->delete();

  }

  public function rfs_photo_predict($pars = [])
  {
    $this->rfs_photo_predict_before();

    //model 1
    $api_result = (array)json_decode($this->rbf_api, true);
    $predictions = isset($api_result['result']) && isset($api_result['result']['predictions'])
      ? (array)$api_result['result']['predictions'] : [];
    if (!count($predictions)) {
      //old
      $predictions = isset($api_result['predictions']) && isset($api_result['predictions'])
        ? (array)$api_result['predictions'] : [];
    }

    $notification = isset($pars['notification']) ? (bool)$pars['notification'] : true;
    $sensor = $this->get_restaurant();
    $debug = false;

    //find foods
    $foods = SysRobo::foods_find([
      'predictions' => $predictions,
      'restaurant_parent_id' => $sensor->restaurant_parent_id,

      'debug' => $debug,
    ]);
//    var_dump($foods);

    $no_food = true;

    if (count($foods)) {
      //find food 1
      $foods = SysRobo::foods_valid($foods, [
        'predictions' => $predictions,

        'debug' => $debug,
      ]);
//      var_dump($foods);

      if (count($foods)) {
        $no_food = false;

        //find category
        $food = Food::find($foods['food']);

        $food_category = $food->get_category([
          'restaurant_parent_id' => $sensor->restaurant_parent_id,
        ]);

        //find ingredients found
        $ingredients_found = SysRobo::ingredients_found($food, [
          'predictions' => $predictions,
          'restaurant_parent_id' => $sensor->restaurant_parent_id,

          'debug' => $debug
        ]);
//        var_dump($ingredients_found);

        //find ingredients missing
        $ingredients_missing = SysRobo::ingredients_missing($food, [
          'predictions' => $predictions,
          'restaurant_parent_id' => $sensor->restaurant_parent_id,
          'ingredients_found' => $ingredients_found,

          'debug' => $debug
        ]);
//        var_dump($ingredients_missing);

        $this->update([
          'status' => 'checked',

          'food_id' => $food->id,
          'food_category_id' => $food_category ? $food_category->id : 0,
          'confidence' => $foods['confidence'],
          'rbf_confidence' => $foods['confidence'],
          'found_by' => 'rbf',
          'rbf_predict' => $food->id,
        ]);

        $this->rfs_ingredients_missing($food, $ingredients_missing, $notification);
      }
    }

//    var_dump($no_food);
    if ($no_food) {
      $this->update([
        'status' => 'failed',
      ]);
    }
  }

  public function rfs_photo_predict_after()
  {

  }

  public function rfs_ingredients_missing($food, $ingredients = [], $notification = true)
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

    $this->rfs_ingredients_missing_text($ingredients);

    $food = $this->get_food();
    $sensor = $this->get_restaurant();
    $restaurant = $sensor->get_parent();
    $users = $sensor->get_users();

    //notify
    if (count($ingredients) && count($users) && $notification) {
      $live_group = $restaurant->get_food_live_group($food);

      foreach ($users as $user) {

        //live_group
        $valid_group = true;
        if ($live_group > 1 || $this->confidence < 90) {
          $valid_group = false;
        }
        if ($user->is_super_admin()) {
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
            'restaurant_id' => $sensor->id,
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
                'restaurant_id' => $sensor->id,
                'food_id' => $food ? $food->id : 0,
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

  public function rfs_ingredients_missing_text($ingredients = [])
  {
    $ids = [];
    $texts = NULL;

    $ingredients = count($ingredients) ? $ingredients : $this->get_ingredients_missing();
    if (count($ingredients)) {
      foreach ($ingredients as $ingredient) {
        $text = $ingredient['quantity'] . ' ' . SysRobo::burger_ingredient_chicken_beef($ingredient['name']);
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

  public function get_ingredients_recipe()
  {
    $items = [];

    $sensor = $this->get_restaurant();
    $food = $this->get_food();
    if ($food) {
      $items = $food->get_recipes([
        'restaurant_parent_id' => $sensor->restaurant_parent_id,
      ]);
    }

    return $items;
  }

  public function get_ingredients_missing()
  {
    $table_1 = app(RestaurantFoodScanMissing::class)->getTable();
    $table_2 = app(Ingredient::class)->getTable();

    $rows = RestaurantFoodScanMissing::query($table_1)
      ->distinct()
      ->select("{$table_2}.id", "{$table_2}.name", "{$table_2}.name_vi",
        "{$table_1}.ingredient_quantity", "{$table_1}.ingredient_type"
      )
      ->leftJoin($table_2, "{$table_2}.id", "=", "{$table_1}.ingredient_id")
      ->where("$table_1.restaurant_food_scan_id", $this->id)
      ->orderBy("{$table_1}.ingredient_type", "asc")
      ->orderBy("{$table_1}.ingredient_quantity", "desc")
      ->orderBy("{$table_1}.id")
      ->get()
      ->toArray();

    //arr required
    $items = [];
    if (count($rows)) {
      foreach ($rows as $row) {
        $temp = (array)$row;

        $temp['quantity'] = $temp['ingredient_quantity'];
        $temp['name'] = SysRobo::burger_ingredient_chicken_beef($temp['name']);

        $items[$temp['id']] = $temp;
      }
    }

    return $items;
  }

  public function get_ingredients_found()
  {
    $food = $this->get_food();
    $sensor = $this->get_restaurant();

    $arr1s = $this->get_ingredients_missing();
    $arr2s = [];

    if ($food) {
      $rows = $food->get_ingredients([
        'restaurant_parent_id' => $sensor->restaurant_parent_id
      ]);
      if (count($rows)) {
        $rows = $rows->toArray();

        foreach ($rows as $row) {
          $temp = (array)$row;

          $temp['quantity'] = $temp['ingredient_quantity'];
          $temp['name'] = SysRobo::burger_ingredient_chicken_beef($temp['name']);

          $arr2s[$temp['id']] = $temp;
        }
      }

      if (count($arr1s)) {
        $arr2s = $this->arr_compact($arr1s, $arr2s);

        //group burger
        $burger1s = SysRobo::_SYS_BURGER_GROUP_1;
        $burger2s = SysRobo::_SYS_BURGER_GROUP_2;
        if (in_array($food->id, $burger1s) || in_array($food->id, $burger2s)) {
          $arr2s = $this->arr_burger_missing_compact($arr1s, $arr2s);
        }
      }
    }

    return $arr2s;
  }

  public function arr_burger_missing_compact($arr1s, $arr2s, $pars = [])
  {
    $debug = isset($pars['debug']) ? (bool)$pars['debug'] : false;
    $arr = [];

    //required column = id + quantity
    if (count($arr1s) && count($arr2s)) {

      $arr1s = SysCore::arr_sort_by_id_quantity($arr1s);
      $arr2s = SysCore::arr_sort_by_id_quantity($arr2s);

      if ($debug) {
        var_dump(SysCore::var_dump_break());
        var_dump($arr1s);
        var_dump(SysCore::var_dump_break());
        var_dump($arr2s);
      }

      $ids = [];
      foreach ($arr1s as $a1) {
        $ids[] = $a1['id'];
      }

      foreach ($arr2s as $a2) {
        $temp = (array)$a2;

//        SysRobo::_SYS_BURGER_INGREDIENTS
        if ($temp['id'] == 114) {
          if (isset($arr[45])) {
            $arr[45]['quantity'] += $temp['quantity'];
          } else {
            $temp['id'] = 45;

            if (in_array($temp['id'], $ids)) {
              $temp['quantity'] = $temp['quantity'] - $arr1s[$temp['id']]['quantity'];
            }

            $arr[$temp['id']] = $temp;
          }

          continue;
        }

        if (in_array($temp['id'], $ids)) {
          $temp['quantity'] = $temp['quantity'] - $arr1s[$temp['id']]['quantity'];
        }

        if ($temp['quantity']) {
          $arr[$temp['id']] = $temp;
        }
      }
    }

    if ($debug) {
      var_dump(SysCore::var_dump_break());
      var_dump($arr);
    }

    return $arr;
  }

  public function arr_compact($arr1s, $arr2s, $pars = [])
  {
    $debug = isset($pars['debug']) ? (bool)$pars['debug'] : false;
    $arr = [];

    //required column = id + quantity
    if (count($arr1s) && count($arr2s)) {

      $arr1s = SysCore::arr_sort_by_id_quantity($arr1s);
      $arr2s = SysCore::arr_sort_by_id_quantity($arr2s);

      if ($debug) {
        var_dump(SysCore::var_dump_break());
        var_dump($arr1s);
        var_dump(SysCore::var_dump_break());
        var_dump($arr2s);
      }

      $ids = [];
      foreach ($arr1s as $a1) {
        $ids[] = $a1['id'];
      }

      foreach ($arr2s as $a2) {
        $temp = (array)$a2;

        if (in_array($temp['id'], $ids)) {
          $temp['quantity'] = $temp['quantity'] - $arr1s[$temp['id']]['quantity'];
        }

        if ($temp['quantity']) {
          $arr[$temp['id']] = $temp;
        }
      }
    }

    if ($debug) {
      var_dump(SysCore::var_dump_break());
      var_dump($arr);
    }

    return $arr;
  }
}
