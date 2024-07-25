<?php

namespace App\Console\Commands;
use Illuminate\Console\Command;
//lib
use App\Api\SysRobo;

class PhotoNotify extends Command
{
  protected $signature = 'web:photo-notify';
  protected $description = 'Command: photo notify...';

  public function handle()
  {

    SysRobo::photo_notify([

    ]);
  }
}
