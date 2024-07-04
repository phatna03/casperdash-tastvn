<?php

namespace App\Console\Commands;
use Illuminate\Console\Command;
//lib
use App\Api\SysApp;
use App\Api\SysZalo;

class ZaloToken extends Command
{
  protected $signature = 'thirdparty:zalo-token-access';
  protected $description = 'Third Party: Zalo get access token (expire after 25 hours)';

  public function handle()
  {
    $sys_app = new SysApp();

    $datas = SysZalo::daily_access_token();
    if (count($datas) && isset($datas['access_token'])) {
      $sys_app->set_setting('zalo_token_refresh', $datas['refresh_token']);
      $sys_app->set_setting('zalo_token_access', $datas['access_token']);
    }
  }
}
