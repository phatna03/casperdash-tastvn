<?php

namespace App\Console\Commands;
use Illuminate\Console\Command;
//lib
use App\Api\SysDev;

class DevPhotoCheck extends Command
{
  protected $signature = 'dev:photo-check';
  protected $description = 'Command: devsite sync data from livesite: photo local to s3';

  public function handle()
  {
    SysDev::photo_check([

    ]);
  }
}
