<?php

namespace App\Console\Commands;
use Illuminate\Console\Command;
//lib
use App\Api\SysKas;

class KasCheck extends Command
{
  protected $signature = 'kas:bill-check';
  protected $description = 'Command: bill check...';

  public function handle()
  {

    SysKas::bill_check([

    ]);
  }

}
