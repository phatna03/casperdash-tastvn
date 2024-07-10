<?php

namespace App\Api;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Storage;
//lib
use Zalo\Zalo;
use App\Api\SysCore;
use App\Models\RestaurantFoodScan;

class SysZalo
{
  public const _URL_API = 'https://openapi.zalo.me';
  public const _APP_SECRET_KEY = '5N9dmSO007UHfm8415gI';
  public const _APP_ID = '1735239634616456366';

  public static function zalo_token($pars = [])
  {
    $datas = SysZalo::daily_access_token();
    if (count($datas) && isset($datas['access_token'])) {
      SysCore::set_sys_setting('zalo_token_refresh', $datas['refresh_token']);
      SysCore::set_sys_setting('zalo_token_access', $datas['access_token']);
    }
  }

  public static function daily_access_token()
  {

    $ch = curl_init();
    $url_header = [
      'Accept: application/json',
      'Content-Type: application/x-www-form-urlencoded',
      'secret_key: ' . SysZalo::_APP_SECRET_KEY,
    ];
    $url_api = 'https://oauth.zaloapp.com/v4/oa/access_token';

    $url_params = 'app_id=' . SysZalo::_APP_ID
      . '&grant_type=refresh_token&refresh_token='
      . SysCore::get_sys_setting('zalo_token_refresh');

    curl_setopt($ch, CURLOPT_URL, $url_api);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $url_header);

    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $url_params);

    $result = curl_exec($ch);
    $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    curl_close($ch);

    return (array)json_decode($result);
  }

  public static function access_token()
  {
    return SysCore::get_sys_setting('zalo_token_access');
  }

  public static function user_list($pars = [])
  {
    $offset = isset($pars['offset']) ? (int)$pars['offset'] : 0;
    $count = 50; //max
    $is_follower = isset($pars['follower']) ? (bool)$pars['follower'] : true;
    $period = isset($pars['period']) ? $pars['period'] : '';

    $url_params = [
      'offset' => $offset,
      'count' => $count,
      'is_follower' => $is_follower,
      'last_interaction_period' => $period,
    ];
    $url_params = json_encode($url_params);

    $ch = curl_init();
    $url_header = [
      'Accept: application/json',
      'access_token: ' . SysZalo::access_token(),
    ];
    $url_api = SysZalo::_URL_API . '/v3.0/oa/user/getlist?data=' . $url_params;

    curl_setopt($ch, CURLOPT_URL, $url_api);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $url_header);
    $result = curl_exec($ch);
    $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    curl_close($ch);

    return (array)json_decode($result);
  }

  public static function user_detail($user_id)
  {
    $url_params = [
      'user_id' => $user_id,
    ];
    $url_params = json_encode($url_params);

    $ch = curl_init();
    $url_header = [
      'Accept: application/json',
      'access_token: ' . SysZalo::access_token(),
    ];
    $url_api = SysZalo::_URL_API . '/v3.0/oa/user/detail?data=' . $url_params;

    curl_setopt($ch, CURLOPT_URL, $url_api);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $url_header);
    $result = curl_exec($ch);
    $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    curl_close($ch);

    return (array)json_decode($result);
  }

  public static function send_request_info($user_id)
  {
    $url_params = '{
  "recipient": {
    "user_id": "' . $user_id . '"
  },
  "message": {
    "attachment": {
      "payload": {
        "elements": [
          {
            "image_url": "https://tastevietnam.asia/sites/default/files/taste-vietnam_logo.svg",
            "subtitle": "Website TasteVN đang yêu cầu thông tin từ bạn! Bấm vào đây để xem chi tiết!",
            "title": "[TasteVN] Zalo OA Permission"
          }
        ],
        "template_type": "request_user_info"
      },
      "type": "template"
    }
  }
}';

//    var_dump($url_params);

    $ch = curl_init();
    $url_header = [
      'Accept: application/json',
      'Content-Type: application/json',
      'access_token: ' . SysZalo::access_token(),
    ];
    $url_api = SysZalo::_URL_API . '/v3.0/oa/message/cs';

    curl_setopt($ch, CURLOPT_URL, $url_api);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $url_header);

    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $url_params);

    $result = curl_exec($ch);
    $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    curl_close($ch);

    return (array)json_decode($result);
  }

  public static function send_text_only($user_id, $message)
  {
    $specialChars = array("\r", "\n");
    $replaceChars = array(" ", " ");

    $message = str_replace($specialChars, $replaceChars, $message);

    $url_params = '{
  "recipient": {
    "user_id": "' . $user_id . '"
  },
  "message": {
    "text": "' . $message . '"
  }
}';

//    var_dump($url_params);

    $ch = curl_init();
    $url_header = [
      'Accept: application/json',
      'Content-Type: application/json',
      'access_token: ' . SysZalo::access_token(),
    ];
    $url_api = SysZalo::_URL_API . '/v3.0/oa/message/cs';

    curl_setopt($ch, CURLOPT_URL, $url_api);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $url_header);

    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $url_params);

    $result = curl_exec($ch);
    $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    curl_close($ch);

    return (array)json_decode($result);
  }

  public static function send_rfs_note($user_id, $type, RestaurantFoodScan $rfs, $pars = [])
  {
    $img_url = $rfs->get_photo();

    switch ($type) {
      case 'photo_comment':

        $message = '+ Photo ID: ' . $rfs->id . ' \n';

        if (!empty($rfs->note)) {
          $note = $rfs->note;
          $text_note = preg_replace("/[\n\r]/","", $note);

          $text_noter = '';
          $noter = $rfs->get_noter();

          $message .= '\n+ MAIN NOTE: \n' . $text_note;

          if ($noter) {
            $text_noter = '(last edited by @ ' . $noter->name . ')';

            $message .= '\n' . $text_noter;
          }
        }

        $cmts = $rfs->get_comments();
        if (count($cmts)) {
          foreach ($cmts as $cmt) {
            $time = date('d/m/Y H:i:s', strtotime($cmt->created_at));
            $text_content = preg_replace("/[\n\r]/","", $cmt->content);

            $message .= '\n\n+ ' . $time . ' - ' .
              '@' . $cmt->owner->name . ': \n' .
              $text_content
            ;
          }
        }

        $btn_url = 'https://ai.block8910.com/admin/photos?photo=52375';

        $url_params = '{
  "recipient": {
    "user_id": "' . $user_id . '"
  },
  "message": {
    "text": "' . $message . '",
    "attachment": {
        "type": "template",
        "payload": {
            "template_type": "media",
            "elements": [
              {
                  "media_type": "image",
                  "url": "' . $img_url . '"
              }
            ],
            "buttons": [
              {
                  "title": "Go to Website",
                  "payload": {
                      "url": "' . $btn_url . '"
                  },
                  "type": "oa.open.url"
              }
            ]
        }
    }
  }
}';

        break;

      case 'ingredient_missing':

      $ingredients_missing_text = '';
      if (!empty($rfs->missing_texts)) {
        $temps = array_filter(explode('&nbsp', $rfs->missing_texts));
        if (count($temps)) {
          foreach ($temps as $text) {
            $text = trim($text);
            if (!empty($text)) {
              $ingredients_missing_text .= '- ' . $text . '\n';
            }
          }
        }

      }

      $message = '+ Photo ID: ' . $rfs->id . ' \n' .
        '\n+ Ingredients Missing: \n' .
        $ingredients_missing_text
      ;

        $btn_url = url('admin/photos/?photo=' . $rfs->id);
        $btn_url = 'https://ai.block8910.com/admin/photos?photo=52375';

      $url_params = '{
  "recipient": {
    "user_id": "' . $user_id . '"
  },
  "message": {
    "text": "' . $message . '",
    "attachment": {
        "type": "template",
        "payload": {
            "template_type": "media",
            "elements": [{
                "media_type": "image",
                "url": "' . $img_url . '"
            }],
            "buttons": [
              {
                  "title": "Go to Website",
                  "payload": {
                      "url": "' . $btn_url . '"
                  },
                  "type": "oa.open.url"
              }
            ]
        }
    }
  }
}';

      break;

      default:
        $url_params = '';
    }

//    var_dump($url_params);
//    var_dump((array)json_decode($url_params, true));die;

    if (empty($url_params)) {
      die('no data...');
    }

    $ch = curl_init();
    $url_header = [
      'Accept: application/json',
      'Content-Type: application/json',
      'access_token: ' . SysZalo::access_token(),
    ];
    $url_api = SysZalo::_URL_API . '/v3.0/oa/message/cs';

    curl_setopt($ch, CURLOPT_URL, $url_api);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $url_header);

    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $url_params);

    $result = curl_exec($ch);
    $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    curl_close($ch);

    var_dump($result);

    return (array)json_decode($result);
  }
}
