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
  public const _DEBUG = true;
  public const _DEBUG_LOG_FOLDER = 'public/logs/';
  public const _DEBUG_LOG_FILE_S3_CALLBACK = 'public/logs/s3_callback.log';

  protected const _DEBUG_LOG_FILE_CRON = 'public/logs/cron_tastevn.log';
  protected const _DEBUG_LOG_FILE_S3_POLLY = 'public/logs/s3_polly.log';
  protected const _DEBUG_LOG_FILE_ROBOFLOW = 'public/logs/cron_tastevn_rbf_retrain.log';

  public function random_str($length = 8)
  {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
      $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
  }

  public function get_setting($key)
  {
    $row = SysSetting::where('key', $key)
      ->first();

    return $row ? $row->value : NULL;
  }

  public function bug_add($pars = [])
  {
    if (count($pars)) {
      SysBug::create($pars);
    }
  }

  public function parse_s3_bucket_address($text)
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

  public function parse_date_range($times = NULL)
  {
    $time_from = NULL;
    $time_to = NULL;

//    $times = "03/01/2024 01:30 - 28/02/2024 23:59";
    $times = array_filter(explode('-', $times));

    if (count($times) && !empty($times[0])) {
      $date_from = trim(substr(trim($times[0]), 0, 10));
      $time_from = trim(substr(trim($times[0]), 10));
      $hour_from = trim(substr(trim($time_from), 0, 2));
      $minute_from = trim(substr(trim($time_from), 3, 2));

      $time_from = date('Y-m-d', strtotime(str_replace('/', '-', $date_from))) . ' ' . $hour_from . ':' . $minute_from . ':00';
    }

    if (count($times) && !empty($times[1])) {
      $date_to = trim(substr(trim($times[1]), 0, 10));
      $time_to = trim(substr(trim($times[1]), 10));
      $hour_to = trim(substr(trim($time_to), 0, 2));
      $minute_to = trim(substr(trim($time_to), 3, 2));
      $second_to = '00';
      if ($minute_to == 59) {
        $second_to = 59;
      }
      $time_to = date('Y-m-d', strtotime(str_replace('/', '-', $date_to))) . ' ' . $hour_to . ':' . $minute_to . ':' . $second_to;
    }

    return [
      'time_from' => $time_from,
      'time_to' => $time_to,
    ];
  }

  public function parse_to_query($query)
  {
    return vsprintf(str_replace('?', '%s', $query->toSql()), collect($query->getBindings())->map(function ($binding) {
      $binding = addslashes($binding);
      return is_numeric($binding) ? $binding : "'{$binding}'";
    })->toArray());
  }

  public function log_failed()
  {

  }

  public function s3_todo()
  {
    //restaurants
    $select = Restaurant::where('deleted', 0)
      ->where('s3_bucket_name', '<>', NULL)
      ->where('s3_bucket_address', '<>', NULL)
      ->where('s3_checking', 0);
    $restaurants = $select->get();

    $this::_DEBUG ? Storage::append($this::_DEBUG_LOG_FILE_CRON, 'TODO_AT_' . date('d_M_Y_H_i_s')) : $this->log_failed();

    if (count($restaurants)) {
      foreach ($restaurants as $restaurant) {
        $this::_DEBUG ? Storage::append($this::_DEBUG_LOG_FILE_CRON,
          'RESTAURANT - ' . $restaurant->id . ' - ' . $restaurant->name) : $this->log_failed();
//        dispatch(new PhotoGet($restaurant));
      }
    }
  }

  public function s3_get_photos($pars = [])
  {
    //settings
    $s3_region = $this->get_setting('s3_region');
    $s3_api_key = $this->get_setting('s3_api_key');
    $s3_api_secret = $this->get_setting('s3_api_secret');
    //time
    $scan_date = date("Y-m-d");
    if (count($pars) && isset($pars['scan_date'])) {
      $scan_date = $pars['scan_date'];
    }
    $scan_hour = (int)date('H');
    if (count($pars) && isset($pars['scan_hour'])) {
      $scan_hour = $pars['scan_hour'];
    }
    //restaurants
    $select = Restaurant::where('deleted', 0)
      ->where('s3_bucket_name', '<>', NULL)
      ->where('s3_bucket_address', '<>', NULL)
      ->where('s3_checking', 0);

    if (count($pars) && isset($pars['restaurant_id'])) {
      $select->where('id', (int)$pars['restaurant_id']);
    }

    $restaurants = $select->get();

    $this::_DEBUG ? Storage::append($this::_DEBUG_LOG_FILE_CRON, 'GET_PHOTOS_' . date('d_M_Y_H_i_s')) : $this->log_failed();
    $this::_DEBUG ? Storage::append($this::_DEBUG_LOG_FILE_CRON, 'TIME_' . $scan_date . ' - ' . $scan_hour) : $this->log_failed();

    if (count($restaurants) && !empty($s3_region) && !empty($s3_api_key) && !empty($s3_api_secret)) {

      $this::_DEBUG ? Storage::append($this::_DEBUG_LOG_FILE_CRON, 'VALID_RESTAURANT_' . count($restaurants)) : $this->log_failed();

      foreach ($restaurants as $restaurant) {

        $s3_bucket = $restaurant->s3_bucket_name;
        $s3_address = $this->parse_s3_bucket_address($restaurant->s3_bucket_address);
        if (empty($s3_bucket) || empty($s3_address)) {
          continue;
        }

        $this::_DEBUG ? Storage::append($this::_DEBUG_LOG_FILE_CRON, 'RESTAURANT - ' . $restaurant->id . ' - ' . $restaurant->name) : $this->log_failed();

        $photo_new = 0;
        $restaurant->update([
          's3_checking' => 1,
        ]);

        try {

          $s3_api = new S3Client([
            'version' => 'latest',
            'region' => $s3_region,
            'credentials' => array(
              'key' => $s3_api_key,
              'secret' => $s3_api_secret
            )
          ]);

          $scan_hour = (int)$scan_hour; //9 not 09

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
              $s3_contents[] = [
                'key' => $content['Key'],
//                'date' => $content['LastModified']->format('Y-m-d H:i:s'), //UTC=0
                'date' => date('Y-m-d H:i:s', strtotime($content['LastModified']->__toString())),
              ];
            }

            //sort
            $a1 = [];
            $a2 = [];
            foreach ($s3_contents as $key => $row) {
              $a1[$key] = $row['date'];
              $a2[$key] = $row['key'];
            }
            array_multisort($a1, SORT_DESC, $a2, SORT_DESC, $s3_contents);

            //check
            foreach ($s3_contents as $content) {

              $URL = "https://s3.{$s3_region}.amazonaws.com/{$s3_bucket}/{$content['key']}";
              //valid photo
              if (@getimagesize($URL)) {

                $this::_DEBUG ? Storage::append($this::_DEBUG_LOG_FILE_CRON, 'KEY - ' . $content['key']) : $this->log_failed();
                $this::_DEBUG ? Storage::append($this::_DEBUG_LOG_FILE_CRON, 'URL - ' . $URL) : $this->log_failed();

                $row = RestaurantFoodScan::where('restaurant_id', $restaurant->id)
//                  ->where('deleted', 0)
                  ->where('photo_name', $content['key'])
                  ->first();
                if ($row) {
                  break;
                }

                $exts = explode('.', $content['key']);

                RestaurantFoodScan::create([
                  'restaurant_id' => $restaurant->id,
                  'photo_url' => $URL,
                  'photo_name' => $content['key'],
                  'photo_ext' => $exts[1],
                  'status' => 'new',
                  'time_photo' => $content['date'],
                ]);

                $photo_new = 1;
              }
            }
          }

          if ($photo_new) {
//            dispatch(new PhotoScan($restaurant));
          }

        } catch (\Exception $e) {
          $this->bug_add([
            'type' => 's3_photo_get',
            'line' => $e->getLine(),
            'file' => $e->getFile(),
            'message' => $e->getMessage(),
            'params' => json_encode($e),
          ]);
        }

        $restaurant->update([
          's3_checking' => 0,
        ]);
      }
    }
  }

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

  public function sys_predict_photos($pars = [])
  {
//scanned rows
    $select = RestaurantFoodScan::where('deleted', 0)
      ->where('status', 'scanned')
      ->limit(20)
      ->orderBy('id', 'desc');

    if (count($pars) && isset($pars['restaurant_id'])) {
      $select->where('restaurant_id', (int)$pars['restaurant_id']);
    }

    try {

      $rows = $select->get();
      if (count($rows)) {
        foreach ($rows as $row) {
          $row->predict_food();
        }
      }

    } catch (\Exception $e) {
      $this->bug_add([
        'type' => 'sys_photo_predict',
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

  public function remote_file_exists($url)
  {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_NOBODY, true);
    curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if ($httpCode == 200) {
      return true;
    }
    return false;
  }

  public function s3_polly($pars = [])
  {
    $user = Auth::user();

    //pars
    $tester = isset($pars['tester']) ? (int)$pars['tester'] : 0;
    $text_rate = isset($pars['text_rate']) && !empty($pars['text_rate']) ? $pars['text_rate'] : 'medium';
    $text_to_speak = isset($pars['text_to_speak']) && !empty($pars['text_to_speak']) ? $pars['text_to_speak'] : NULL;
    //configs
    $s3_polly_configs = [
      'version' => 'latest',
      'region' => $this->get_setting('s3_region'),
      'credentials' => [
        'key' => $this->get_setting('s3_api_key'),
        'secret' => $this->get_setting('s3_api_secret'),
      ]
    ];
    $s3_bucket = 'cargo.tastevietnam.asia';
    $s3_file_path = 'casperdash/user_' . $user->id . '/speaker_notify.mp3';

    $this::_DEBUG ? Storage::append($this::_DEBUG_LOG_FILE_S3_POLLY, 'TODO_AT_' . date('d_M_Y_H_i_s')) : $this->log_failed();

    if ($tester) {

      $s3_file_path = 'casperdash/user_' . $user->id . '/speaker_tester.mp3';
      $s3_file_test = 'https://s3.' . $s3_polly_configs['region'] . '.amazonaws.com/' . $s3_bucket . '/' . $s3_file_path;

      if ($this->remote_file_exists($s3_file_test)) {
        return false;
      }

      $this::_DEBUG ? Storage::append($this::_DEBUG_LOG_FILE_S3_POLLY, 'TESTER - ' . $user->id . ' - ' . $user->name) : $this->log_failed();

      try {

        $s3_polly_client = new PollyClient($s3_polly_configs);

        //text_rate = x-slow, slow, medium, fast, and x-fast
        $text_to_speak = "<speak>" .
          "<prosody rate='{$text_rate}'>" .
          "[Test Audio System] Cargo Restaurant," .
          "Ingredients Missing, 1 Sour Bread, 2 Grilled Tomatoes, 3 Avocado Sliced" .
          "</prosody>" .
          "</speak>";
        $s3_polly_args = [
          'OutputFormat' => 'mp3',
          'Text' => $text_to_speak,
          'TextType' => 'ssml',
          'VoiceId' => 'Joey', //pass preferred voice id here
        ];

        $result = $s3_polly_client->synthesizeSpeech($s3_polly_args);
        $polly_result = $result->get('AudioStream')->getContents();

        #Save MP3 to S3
        $credentials = new \Aws\Credentials\Credentials($s3_polly_configs['credentials']['key'], $s3_polly_configs['credentials']['secret']);
        $client_s3 = new S3Client([
          'version' => 'latest',
          'credentials' => $credentials,
          'region' => $s3_polly_configs['region']
        ]);

        $result_s3 = $client_s3->putObject([
          'Key' => $s3_file_path,
//        'ACL'         => 'public-read',
          'Body' => $polly_result,
          'Bucket' => $s3_bucket,
          'ContentType' => 'audio/mpeg',
          'SampleRate' => '8000'
        ]);

      } catch (Exception $e) {
        $this->bug_add([
          'type' => 's3_polly_tester',
          'line' => $e->getLine(),
          'file' => $e->getFile(),
          'message' => $e->getMessage(),
          'params' => json_encode($e),
        ]);
      }
    } else {
      //live
      if (!empty($text_to_speak)) {
        $text_to_speak = "<speak>" .
          "<prosody rate='{$text_rate}'>" .
          $text_to_speak .
          "</prosody>" .
          "</speak>";
      }

      $this::_DEBUG ? Storage::append($this::_DEBUG_LOG_FILE_S3_POLLY, 'NOTIFY - ' . $user->id . ' - ' . $user->name) : $this->log_failed();

      try {

        $s3_polly_client = new PollyClient($s3_polly_configs);

        $s3_polly_args = [
          'OutputFormat' => 'mp3',
          'Text' => $text_to_speak,
          'TextType' => 'ssml',
          'VoiceId' => 'Joey', //pass preferred voice id here
        ];

        $result = $s3_polly_client->synthesizeSpeech($s3_polly_args);
        $polly_result = $result->get('AudioStream')->getContents();

        #Save MP3 to S3
        $credentials = new \Aws\Credentials\Credentials($s3_polly_configs['credentials']['key'], $s3_polly_configs['credentials']['secret']);
        $client_s3 = new S3Client([
          'version' => 'latest',
          'credentials' => $credentials,
          'region' => $s3_polly_configs['region']
        ]);

        $result_s3 = $client_s3->putObject([
          'Key' => $s3_file_path,
//        'ACL'         => 'public-read',
          'Body' => $polly_result,
          'Bucket' => $s3_bucket,
          'ContentType' => 'audio/mpeg',
          'SampleRate' => '8000'
        ]);

      } catch (Exception $e) {
        $this->bug_add([
          'type' => 's3_polly_notify',
          'line' => $e->getLine(),
          'file' => $e->getFile(),
          'message' => $e->getMessage(),
          'params' => json_encode($e),
        ]);
      }
    }

  }

  public function get_item($item_id, $item_type)
  {
    $item = null;

    switch ($item_type) {
      case 'food_category':
        $item = FoodCategory::find((int)$item_id);
        break;
      case 'food':
        $item = Food::find((int)$item_id);
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

  public function get_notifications()
  {
    return [
      'missing_ingredient', 'photo_comment',
    ];
  }

  public function get_log_types()
  {
    return [
      'login' => 'Login',
      'logout' => 'Logout',
    ];
  }

  public function get_log_items()
  {
    return [
      'food_category' => 'Categories',
      'food' => 'Dishes',
      'ingredient' => 'Ingredients',
      'text' => 'Text notes',
      'user' => 'Users',
      'restaurant' => 'Restaurants',
      'restaurant_food_scan' => 'Photos',
    ];
  }

  public function get_log_settings()
  {
    return [
      'aws_s3' => [
        's3_region' => $this->get_setting('s3_region'),
        's3_api_key' => $this->get_setting('s3_api_key'),
        's3_api_secret' => $this->get_setting('s3_api_secret'),
      ],
      'roboflow' => [
        'rbf_api_key' => $this->get_setting('rbf_api_key'),
        'rbf_dataset_scan' => $this->get_setting('rbf_dataset_scan'),
        'rbf_dataset_upload' => $this->get_setting('rbf_dataset_upload'),
      ],
      'mail_server' => [
        'mail_mailer' => $this->get_setting('mail_mailer'),
        'mail_host' => $this->get_setting('mail_host'),
        'mail_username' => $this->get_setting('mail_username'),
        'mail_password' => $this->get_setting('mail_password'),
        'mail_port' => $this->get_setting('mail_port'),
        'mail_encryption' => $this->get_setting('mail_encryption'),
        'mail_from_address' => $this->get_setting('mail_from_address'),
        'mail_from_name' => $this->get_setting('mail_from_name'),
      ],
    ];
  }

  public function rfs_query_data($date, $restaurant_id)
  {
    $statuses = ['checked', 'failed'];

    $select_total = RestaurantFoodScan::selectRaw('COUNT(*) as total_photos')
      ->where('restaurant_id', $restaurant_id)
      ->whereIn('status', $statuses)
      ->whereDate('time_photo', $date)
      ->get()
      ->toArray();

    $select_failed = RestaurantFoodScan::selectRaw('COUNT(*) as total_photos')
      ->where('restaurant_id', $restaurant_id)
      ->where('status', 'failed')
      ->whereDate('time_photo', $date)
      ->get()
      ->toArray();

    $select_checked = RestaurantFoodScan::selectRaw('COUNT(*) as total_photos')
      ->where('restaurant_id', $restaurant_id)
      ->where('status', 'checked')
      ->whereDate('time_photo', $date)
      ->get()
      ->toArray();

    $select_checked_missing = RestaurantFoodScan::selectRaw('COUNT(*) as total_photos')
      ->where('restaurant_id', $restaurant_id)
      ->where('status', 'checked')
      ->whereDate('time_photo', $date)
      ->where('missing_ids', '<>', NULL)
      ->where('food_id', '<>', 0)
      ->get()
      ->toArray();

    $total_photos = (int)$select_total[0]['total_photos'];
    $total_failed = (int)$select_failed[0]['total_photos'];
    $total_checked = (int)$select_checked[0]['total_photos'];
    $total_checked_missing = (int)$select_checked_missing[0]['total_photos'];
    $total_checked_ok = (int)$select_checked[0]['total_photos'] - (int)$select_checked_missing[0]['total_photos'];

    $percent_checked_missing = $total_checked ? (int)($total_checked_missing / $total_checked * 100) : 0;

    return [
      'total_photos' => $total_photos,
      'total_failed' => $total_failed,
      'total_checked' => $total_checked,
      'total_checked_missing' => $total_checked_missing,
      'total_checked_ok' => $total_checked_ok,

      'percent_checked_missing' => $percent_checked_missing,
    ];
  }

  public function sys_stats_count()
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
