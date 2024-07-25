<?php

namespace App\Console\Commands;
use Illuminate\Console\Command;
//lib
use App\Api\SysRobo;

class PhotoCheck extends Command
{
  protected $signature = 'web:photo-check';
  protected $description = 'Command: photo check...';

  public function handle()
  {

    SysRobo::photo_duplicate([

    ]);
  }

}
