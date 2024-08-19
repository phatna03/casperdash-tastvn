<?php

namespace App\Console\Commands;
use Illuminate\Console\Command;
//lib
use App\Api\SysDev;

class DevPhotoGet extends Command
{
  protected $signature = 'dev:photo-get';
  protected $description = 'Command: devsite sync data from livesite: photo missing';

  public function handle()
  {
    SysDev::photo_get([

    ]);
  }
}
