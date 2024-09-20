<?php

namespace App\Api;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
//aws
use Aws\Polly\PollyClient;
use Aws\S3\S3Client;
//model
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
use App\Models\SysBug;

class SysApp
{
  public const _DEBUG = true;
  public const _DEBUG_LOG_FOLDER = 'public/logs/';
  public const _DEBUG_LOG_FILE_S3_CALLBACK = 'public/logs/s3_callback.log';

  public const _DEBUG_BREAK = '===========================================================================*****************************************************************************************===========================================================================';

  protected const _DEBUG_LOG_FILE_CRON = 'public/logs/cron_tastevn.log';
  protected const _DEBUG_LOG_FILE_S3_POLLY = 'public/logs/s3_polly.log';
  protected const _DEBUG_LOG_FILE_ROBOFLOW = 'public/logs/cron_tastevn_rbf_retrain.log';

  public function parse_to_query($query)
  {
    return vsprintf(str_replace('?', '%s', $query->toSql()), collect($query->getBindings())->map(function ($binding) {
      $binding = addslashes($binding);
      return is_numeric($binding) ? $binding : "'{$binding}'";
    })->toArray());
  }

  public function parse_date_range($times = NULL)
  {
    $time_from = NULL;
    $time_to = NULL;

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

      $time_to = date('Y-m-d', strtotime(str_replace('/', '-', $date_to))) . ' ' . $hour_to . ':' . $minute_to . ':00';
    }

    return [
      'time_from' => $time_from,
      'time_to' => $time_to,
    ];
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

  public function parse_hour_format($hour)
  {
    if ((int)$hour < 10) {
      $hour = '0' . $hour;
    }

    return $hour;
  }

  public function str_rand($length = 8)
  {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
      $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
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

  public function os_slash_file($path)
  {
    if (strtoupper(substr(PHP_OS, 0, 3)) == 'WIN') {
      $path = str_replace('/', '\\', $path);
    }
    return $path;
  }

  //aws
  public function aws_s3_polly($pars = [])
  {
    $user = Auth::user();

    //pars
    $tester = isset($pars['tester']) ? (int)$pars['tester'] : 0;
    $text_rate = isset($pars['text_rate']) && !empty($pars['text_rate']) ? $pars['text_rate'] : 'medium';
    $text_to_speak = isset($pars['text_to_speak']) && !empty($pars['text_to_speak']) ? $pars['text_to_speak'] : NULL;
    //configs
    $s3_polly_configs = [
      'version' => 'latest',
      'region' => $this::get_setting('s3_region'),
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

  //db
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

  public function get_setting($key)
  {
    $row = SysSetting::where('key', $key)
      ->first();

    return $row ? $row->value : NULL;
  }

  public function set_setting($key, $val)
  {
    $row = SysSetting::where('key', $key)
      ->first();

    if (!$row) {
      $row = SysSetting::create([
        'key' => $key,
      ]);
    }

    $row->update([
      'value' => $val,
    ]);

    return $row;
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
      'restaurant' => 'Sensors',
      'restaurant_parent' => 'Restaurants',
      'restaurant_food_scan' => 'Photos',
    ];
  }

  public function get_log_settings()
  {
    return [
      'aws_s3' => [
        's3_region' => $this->get_setting('s3_region'),
//        's3_api_key' => $this->get_setting('s3_api_key'),
//        's3_api_secret' => $this->get_setting('s3_api_secret'),
      ],
      'roboflow' => [
        'rbf_api_key' => $this->get_setting('rbf_api_key'),
        'rbf_dataset_scan' => $this->get_setting('rbf_dataset_scan'),
        'rbf_dataset_ver' => $this->get_setting('rbf_dataset_ver'),
        'rbf_food_confidence' => $this->get_setting('rbf_food_confidence'),
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

  public function bug_add($pars = [])
  {
    if (count($pars)) {
      SysBug::create($pars);
    }
  }

  public function log_failed()
  {

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

}
