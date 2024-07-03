<?php

namespace App\Api;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Storage;
//lib
use Zalo\Zalo;
use App\Api\SysApp;

class SysZalo
{
  public const _TOKEN_ACCESS = 'm2OrI4ORTYlA9JHFIHnpAyaYCKrA12fQbbqII1O005FrOpmoT2a-9RLGVH8qDbWpzGnXJdXZOswl85rNJb1rDvyrC2zVH2LEs0i7JWzNE53yCW95C4WGJFysBNamUIPQwIHYGXSUPcQFK59JOYqJJQG8MsLTLL9iWHWLMNX01pwY6q4UVa1T0uOkV107G5uBsYrz6XqTLYdTLcGC2IjYRlryAtS3EJOIsNaCA3iGAIBHUHKjC4OLAj4CA18dTY48-IyVRtqHGr66U0j4GHGsTvz8A6WISJfz_pKPUH1f71_lAmnxOaeEQfbpAdLSB1vyd442V4i59nJJ81y10KHP6lqyIt84R6LZY0jM2musKsl8Ub5P24fuO_SmJ5y4UMv6y2PqS1PlObVx42ioBti36CKyF6K2SZrRCBK8SL1107Kl';
  public const _TOKEN_REFRESH = 'tFMVT1bxXrpMzfvp5do2NDNnct4LPFK-eR2ASY4_aoEFx-vk62AKBRhEwdzx4AG0zz70VpHRhKcrrzHW6r_66DM5cdfMS-CecBt_P5XDjZB8ez0SN7d8Ak-phsOeITSFvxk4KaKjvGNIm_r92JEWFxo_tqOyGhubXwdSQI8beZorwD9H4mIr4DpWp4OO5hnitkl0FH0IerwMnyWqM32s6FxTx41BD9ClnE3xVGLhjY6IjTDO0cc_ABwbh7uRVCezeRUwOXDSnXUcfACy6cZGQeAOi2ay4jPPcDE_22a7f2U7ZVeE47EHOjUSy4zoGeqcvf_ZM7vF-olXbeHU9bh59gAjW4yz8Erd_V7nApq3Y0QXZUze15dn5SgexaHxQxDjmOBZEq4By1BPWfDh5dFq0tdskoeR7c24M0';

  public const _URL_API = 'https://openapi.zalo.me';

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
      'access_token: ' . SysZalo::_TOKEN_ACCESS,
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
      'access_token: ' . SysZalo::_TOKEN_ACCESS,
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
      'access_token: ' . SysZalo::_TOKEN_ACCESS,
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

    var_dump($url_params);

    $ch = curl_init();
    $url_header = [
      'Accept: application/json',
      'Content-Type: application/json',
      'access_token: ' . SysZalo::_TOKEN_ACCESS,
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
}
