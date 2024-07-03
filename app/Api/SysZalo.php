<?php

namespace App\Api;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Storage;
//lib
use Zalo\Zalo;
use App\Api\SysApp;

class SysZalo
{
  public const _TOKEN_ACCESS = 'RB3oSV_PZ6nci_DsyDw9R6MqzdJpnlO50lk2HCdGgp8Opu5DmiIAJWRUZLJWmQup4DA7PzBZZXeusF1uwzBG6WZ7v7NIrfet5UY5Qlo3f204fOjHqRoNEJk5b4_ecgqvCOEMSlkQaoeAYPPsx-suQoc5XYpuheP1D8g33kUFkdaYeeC2phU3MZ-5za6izCmSPUFcMloEdnDgg-LFhf7R33wVvaZMaVuqEAgAQSAdd3GjYAPPmhdU44-kqsQwt_L_MTlQDAworKLRlDKdehBmNs2KunhpaVjW6Pl30_3m-LK8_DnMvP73AXgauMRkd-SXUgZeReEmtH1asVTRiD230tl8gc7shPKVPfQiMxQljHb9kunJtgcbEpgWXc_KdOSEGwYIUP2alG5MmDn3iAhU86Q2zL58GQ1SJ2xumeze';
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

}
