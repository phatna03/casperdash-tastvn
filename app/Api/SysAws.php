<?php

namespace App\Api;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
//lib
use App\Api\SysCore;
//aws
use Aws\Polly\PollyClient;
use Aws\S3\S3Client;

class SysAws
{
  public static function s3_polly($pars = [])
  {
    //pars
    $tester = isset($pars['tester']) ? (int)$pars['tester'] : 0;
    $text_rate = isset($pars['text_rate']) && !empty($pars['text_rate']) ? $pars['text_rate'] : 'medium';
    $text_to_speak = isset($pars['text_to_speak']) && !empty($pars['text_to_speak']) ? $pars['text_to_speak'] : NULL;

    $user = Auth::user();
    $debug = isset($pars['debug']) ? (bool)$pars['debug'] : false;
    $file_log = 'public/logs/s3_polly.log';

    //configs
    $s3_polly_configs = [
      'version' => 'latest',
      'region' => SysCore::get_sys_setting('s3_region'),
      'credentials' => [
        'key' => SysCore::get_sys_setting('s3_api_key'),
        'secret' => SysCore::get_sys_setting('s3_api_secret'),
      ]
    ];
    $s3_bucket = 'cargo.tastevietnam.asia';
    $s3_file_path = 'casperdash/user_' . $user->id . '/speaker_notify.mp3';

    $debug ? Storage::append($file_log, 'TODO_AT_' . date('d_M_Y_H_i_s')) : SysCore::log_sys_failed();

    if ($tester) {

      $s3_file_path = 'casperdash/user_' . $user->id . '/speaker_tester.mp3';
      $s3_file_test = 'https://s3.' . $s3_polly_configs['region'] . '.amazonaws.com/' . $s3_bucket . '/' . $s3_file_path;

      if (SysCore::file_url_existed($s3_file_test)) {
        return false;
      }

      $debug ? Storage::append($file_log, 'TESTER - ' . $user->id . ' - ' . $user->name) : SysCore::log_sys_failed();

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

        SysCore::log_sys_bug([
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

      $debug ? Storage::append($file_log, 'NOTIFY - ' . $user->id . ' - ' . $user->name) : SysCore::log_sys_failed();

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
        SysCore::log_sys_bug([
          'type' => 's3_polly_notify',
          'line' => $e->getLine(),
          'file' => $e->getFile(),
          'message' => $e->getMessage(),
          'params' => json_encode($e),
        ]);
      }
    }

  }

}
