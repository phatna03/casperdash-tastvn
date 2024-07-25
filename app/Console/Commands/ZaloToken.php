<?php

namespace App\Console\Commands;
use Illuminate\Console\Command;
//lib
use App\Api\SysZalo;

class ZaloToken extends Command
{
  protected $signature = 'zalo:token-access';
  protected $description = 'Command: zalo get access token (expire after 25 hours)';

  public function handle()
  {

    SysZalo::zalo_token([

    ]);
  }
}
