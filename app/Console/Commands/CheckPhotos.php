<?php

namespace App\Console\Commands;
use Illuminate\Console\Command;
//lib
use App\Api\SysRobo;

class CheckPhotos extends Command
{
  protected $signature = 'local:check-status-images';
  protected $description = 'Command: check duplicated photos from sensors';

  public function handle()
  {

    SysRobo::photo_duplicate([

    ]);
  }

}
