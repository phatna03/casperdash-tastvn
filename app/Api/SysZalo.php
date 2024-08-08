<?php

namespace App\Api;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Storage;
//lib
use Zalo\Zalo;
use App\Api\SysCore;
use App\Models\User;
use App\Models\RestaurantFoodScan;
use App\Models\ZaloUserSend;

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

    return $datas;
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

  public static function send_request_info(User $user, $pars = [])
  {
    $zaloer = $user ? $user->get_zalo() : NULL;

    if (!$user) {

      SysCore::log_sys_bug([
        'type' => 'zalo_send_request_info',
        'message' => 'Invalid params...',
        'params' => json_encode(array_merge($pars, [
          'user' => $user,
          'zaloer' => $zaloer,
        ])),
      ]);

      return [];
    }

    $params = [
      'user_id' => $user->id,
      'zalo_user_id' => $zaloer->zalo_user_id,
      'type' => 'send_request_info',
      'params' => $pars,
    ];

    $datas = [];
    $status = 0;

    try {

      $url_params = '{
  "recipient": {
    "user_id": "' . $zaloer->zalo_user_id . '"
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

      $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
      $result = curl_exec($ch);
      $datas = (array)json_decode($result);

      curl_close($ch);

    } catch (\Exception $e) {

      SysCore::log_sys_bug([
        'type' => 'zalo_send_request_info',
        'line' => $e->getLine(),
        'file' => $e->getFile(),
        'message' => $e->getMessage(),
        'params' => json_encode(array_merge($params, $datas)),
      ]);
    }

    if (count($datas) && isset($datas['data'])) {
      $obj = (array)$datas['data'];
      if (isset($obj['message_id'])) {
        $status = 1;
      }
    }
    $params['status'] = $status;
    $params['params'] = json_encode($params);
    $params['datas'] = json_encode($datas);

    ZaloUserSend::create($params);

    return $datas;
  }

  public static function send_text_only(User $user, $message, $pars = [])
  {
    $zaloer = $user ? $user->get_zalo() : NULL;

    if (!$user || empty($message)) {

      SysCore::log_sys_bug([
        'type' => 'zalo_send_text_only',
        'message' => 'Invalid params...',
        'params' => json_encode(array_merge($pars, [
          'user' => $user,
          'zaloer' => $zaloer,
          'message' => $message,
        ])),
      ]);

      return [];
    }

    $params = [
      'user_id' => $user->id,
      'zalo_user_id' => $zaloer->zalo_user_id,
      'type' => 'zalo_send_text_only',
      'params' => $pars,
    ];

    $datas = [];
    $status = 0;

    try {

      $specialChars = array("\r", "\n");
      $replaceChars = array(" ", " ");

      $message = str_replace($specialChars, $replaceChars, $message);

      $url_params = '{
  "recipient": {
    "user_id": "' . $zaloer->zalo_user_id . '"
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

      $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
      $result = curl_exec($ch);
      $datas = (array)json_decode($result);

      curl_close($ch);

    } catch (\Exception $e) {

      SysCore::log_sys_bug([
        'type' => 'zalo_send_text_only',
        'line' => $e->getLine(),
        'file' => $e->getFile(),
        'message' => $e->getMessage(),
        'params' => json_encode(array_merge($params, $datas)),
      ]);
    }

    if (count($datas) && isset($datas['data'])) {
      $obj = (array)$datas['data'];
      if (isset($obj['message_id'])) {
        $status = 1;
      }
    }
    $params['status'] = $status;
    $params['params'] = json_encode($params);
    $params['datas'] = json_encode($datas);

    ZaloUserSend::create($params);

    return $datas;
  }

  public static function send_rfs_note(User $user, $type, RestaurantFoodScan $rfs, $pars = [])
  {
    $zaloer = $user ? $user->get_zalo() : NULL;

    $zalo_log = isset($pars['zalo_no_log']) ? (int)$pars['zalo_no_log'] : 1;

    //tester
//    return false;

    if (!$user || !$rfs || !$zaloer || ($zaloer && empty($zaloer->zalo_user_id))) {

      SysCore::log_sys_bug([
        'type' => 'zalo_' . $type,
        'message' => 'Invalid params...',
        'params' => json_encode(array_merge($pars, [
          'user_id' => $user ? $user->id : 0,
          'zaloer_user_id' => $zaloer ? $zaloer->user_id : 0,
          'rfs_id' => $rfs ? $rfs->id : 0,
        ])),
      ]);

      return [];
    }

    $sensor = $rfs->get_restaurant();

    $img_url = $rfs->photo_1024();
    if (App::environment() == 'local') {
      $img_url = SysCore::local_img_url();
    }

    $params = [
      'user_id' => $user->id,
      'zalo_user_id' => $zaloer->zalo_user_id,
      'type' => $type,
      'rfs' => $rfs->id,
      'params' => $pars,
    ];

    $datas = [];
    $status = 0;

    try {

      switch ($type) {
        case 'photo_comment':

          $message = '+ Restaurant: ' . $sensor->name . ' \n'
            . '+ Photo ID: ' . $rfs->id . ' at ' . date('d/m/Y H:i:s', strtotime($rfs->time_photo)) . ' \n';

          if ($rfs->customer_requested) {
            $message .= '+ Customer Requested \n';
          }

          if ($rfs->count_foods > 1) {
            $message .= '+ Multiple Dishes: ' . $rfs->count_foods . ' \n';
          }

          if (!empty($rfs->note)) {
            $note = $rfs->note;
            $text_note = preg_replace("/[\n\r]/", "", $note);

            $text_noter = '';
            $noter = $rfs->get_noter();

            $message .= '+ MAIN NOTE: \n' . $text_note;

            if ($noter) {
              $text_noter = '(last edited by @' . $noter->name . ')';

              $message .= '\n' . $text_noter;
            }
          }

          $cmts = $rfs->get_comments();
          if (count($cmts)) {
            foreach ($cmts as $cmt) {
              $time = date('d/m/Y H:i:s', strtotime($cmt->created_at));
              $text_content = preg_replace("/[\n\r]/", "", $cmt->content);

              $message .= '\n+ ' . $time . ' - ' .
                '@' . $cmt->owner->name . ': \n' .
                $text_content;
            }
          }

          $btn_url = url('admin/photos/?photo=' . $rfs->id);
          if (App::environment() == 'local') {
            $btn_url = 'https://ai.block8910.com/admin/photos?photo=' . $rfs->id;
          }

          $url_params = '{
  "recipient": {
    "user_id": "' . $zaloer->zalo_user_id . '"
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

          $message = '+ Restaurant: ' . $sensor->name . ' \n' .
            '+ Photo ID: ' . $rfs->id . ' at ' . date('d/m/Y H:i:s', strtotime($rfs->time_photo)) . ' \n' .
            '+ Please double check: \n' .
            $ingredients_missing_text;

          $btn_url = url('admin/photos/?photo=' . $rfs->id);
          if (App::environment() == 'local') {
            $btn_url = 'https://ai.block8910.com/admin/photos?photo=' . $rfs->id;
          }

          $url_params = '{
  "recipient": {
    "user_id": "' . $zaloer->zalo_user_id . '"
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

      if (!empty($url_params)) {
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

        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $result = curl_exec($ch);
        $datas = (array)json_decode($result);

        curl_close($ch);
      }

    } catch (\Exception $e) {

      SysCore::log_sys_bug([
        'type' => 'zalo_' . $type,
        'line' => $e->getLine(),
        'file' => $e->getFile(),
        'message' => $e->getMessage(),
        'params' => json_encode(array_merge($params, $datas)),
      ]);
    }

    if (count($datas) && isset($datas['data'])) {
      $obj = (array)$datas['data'];
      if (isset($obj['message_id'])) {
        $status = 1;
      }
    }
    $params['status'] = $status;
    $params['params'] = json_encode($params);
    $params['datas'] = json_encode($datas);

    if ($zalo_log) {
      ZaloUserSend::create($params);
    }

    return $datas;
  }

  public static function send_message_transaction(User $user, $type, RestaurantFoodScan $rfs, $pars = [])
  {
    $zaloer = $user ? $user->get_zalo() : NULL;

    $zalo_log = isset($pars['zalo_no_log']) ? (int)$pars['zalo_no_log'] : 1;

    //tester
//    return false;

    if (!$user || !$rfs || !$zaloer || ($zaloer && empty($zaloer->zalo_user_id))) {

      SysCore::log_sys_bug([
        'type' => 'zalo_' . $type,
        'message' => 'Invalid params...',
        'params' => json_encode(array_merge($pars, [
          'user_id' => $user ? $user->id : 0,
          'zaloer_user_id' => $zaloer ? $zaloer->user_id : 0,
          'rfs_id' => $rfs ? $rfs->id : 0,
        ])),
      ]);

      return [];
    }

    $sensor = $rfs->get_restaurant();

    $img_url = $rfs->photo_1024();
    if (App::environment() == 'local') {
      $img_url = SysCore::local_img_url();
    }

    $params = [
      'user_id' => $user->id,
      'zalo_user_id' => $zaloer->zalo_user_id,
      'type' => $type,
      'rfs' => $rfs->id,
      'params' => $pars,
    ];

    $datas = [];
    $status = 0;

//    https://developers.zalo.me/docs/official-account/tin-nhan/tin-giao-dich/gui-tin-giao-dich
//    {"error":-201,"message":"table.content.value cannot exceed 100 characters"}

    try {

      switch ($type) {
        case 'photo_comment':

          $cmts = $rfs->get_comments();
          if (!count($cmts)) {
            break;
          }

          $btn_url = url('admin/photos/?photo=' . $rfs->id);
          if (App::environment() == 'local') {
            $btn_url = 'https://ai.block8910.com/admin/photos?photo=' . $rfs->id;
          }

          $table_content = '';

          if (!empty($rfs->note)) {
            $note = $rfs->note;
            $text_note = preg_replace("/[\n\r]/", "", $note);

            $noter = $rfs->get_noter();
            if ($noter) {
              $text_noter = '(last edited by @' . $noter->name . ')';

              $text_note .= '<br>' . $text_noter;
            }

            $table_content = ',{
                                "value": "' . $text_note . '",
                                "key": "MAIN NOTE"
                            }'
            ;
          }

          foreach ($cmts as $cmt) {
            $time = date('d/m/Y H:i:s', strtotime($cmt->created_at));
            $text_content = preg_replace("/[\n\r]/", "", $cmt->content);

            $text_content = $time . '<br>' . $text_content;

            $table_content .= ',{
                                "value": "' . $text_content . '",
                                "key": "@' . $cmt->owner->name . '"
                            }'
            ;
          }

          $url_params = '{
    "recipient": {
        "user_id": "' . $zaloer->zalo_user_id . '"
    },
    "message": {
        "attachment": {
            "type": "template",
            "payload": {
                "template_type": "transaction_event",
                "language": "EN",
                "elements": [
                    {
                        "image_url": "' . $img_url . '",
                        "type": "banner"
                    },
                    {
                        "type": "header",
                        "content": "Restaurant: ' . $sensor->name . '",
                        "align": "left"
                    },
                    {
                        "type": "text",
                        "align": "left",
                        "content": "Photo ID: ' . $rfs->id . ' at ' . date('d/m/Y H:i:s', strtotime($rfs->time_photo)) . '"
                    },
                    {
                        "type": "table",
                        "content": [
                            {
                                "value": "' . $rfs->id . '",
                                "key": "Code"
                            }' . $table_content . '
                        ]
                    }
                ],
                "buttons": [
                    {
                        "title": "Go to Website",
                        "image_icon": "",
                        "type": "oa.open.url",
                        "payload": {
                            "url": "' . $btn_url . '"
                        }
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
                  $ingredients_missing_text .= '- ' . $text . '<br>';
                }
              }
            }

          }

          $btn_url = url('admin/photos/?photo=' . $rfs->id);
          if (App::environment() == 'local') {
            $btn_url = 'https://ai.block8910.com/admin/photos?photo=' . $rfs->id;
          }

          $url_params = '{
    "recipient": {
        "user_id": "' . $zaloer->zalo_user_id . '"
    },
    "message": {
        "attachment": {
            "type": "template",
            "payload": {
                "template_type": "transaction_event",
                "language": "EN",
                "elements": [
                    {
                        "image_url": "' . $img_url . '",
                        "type": "banner"
                    },
                    {
                        "type": "header",
                        "content": "Restaurant: ' . $sensor->name . '",
                        "align": "left"
                    },
                    {
                        "type": "text",
                        "align": "left",
                        "content": "Photo ID: ' . $rfs->id . ' at ' . date('d/m/Y H:i:s', strtotime($rfs->time_photo)) . '"
                    },
                    {
                        "type": "table",
                        "content": [
                            {
                                "value": "' . $rfs->id . '",
                                "key": "Code"
                            },
                            {
                                "value": "' . $ingredients_missing_text . '",
                                "key": "Please double check"
                            }
                        ]
                    }
                ],
                "buttons": [
                    {
                        "title": "Go to Website",
                        "image_icon": "",
                        "type": "oa.open.url",
                        "payload": {
                            "url": "' . $btn_url . '"
                        }
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

      if (!empty($url_params)) {
        $ch = curl_init();
        $url_header = [
          'Accept: application/json',
          'Content-Type: application/json',
          'access_token: ' . SysZalo::access_token(),
        ];
        $url_api = SysZalo::_URL_API . '/v3.0/oa/message/transaction';

        curl_setopt($ch, CURLOPT_URL, $url_api);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $url_header);

        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $url_params);

        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $result = curl_exec($ch);
        $datas = (array)json_decode($result);

        curl_close($ch);
      }

    } catch (\Exception $e) {

      SysCore::log_sys_bug([
        'type' => 'zalo_' . $type,
        'line' => $e->getLine(),
        'file' => $e->getFile(),
        'message' => $e->getMessage(),
        'params' => json_encode(array_merge($params, $datas)),
      ]);
    }

    if (count($datas) && isset($datas['data'])) {
      $obj = (array)$datas['data'];
      if (isset($obj['message_id'])) {
        $status = 1;
      }
    }
    $params['status'] = $status;
    $params['params'] = json_encode($params);
    $params['datas'] = json_encode($datas);

    if ($zalo_log) {
      ZaloUserSend::create($params);
    }

    return $datas;
  }
}
