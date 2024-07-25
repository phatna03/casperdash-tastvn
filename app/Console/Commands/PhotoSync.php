<?php

namespace App\Console\Commands;
use Illuminate\Console\Command;
//lib
use App\Api\SysRobo;

class PhotoSync extends Command
{
  protected $signature = 'web:photo-sync';
  protected $description = 'Command: photo sync...';

  public function handle()
  {

    SysRobo::photo_sync([

    ]);
  }
}
