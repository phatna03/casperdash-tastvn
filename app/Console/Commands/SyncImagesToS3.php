<?php

namespace App\Console\Commands;
use Illuminate\Console\Command;
//lib
use App\Api\SysRobo;

class SyncImagesToS3 extends Command
{
  protected $signature = 'sync:images-to-s3';
  protected $description = 'Command: sync local photos to S3 bucket';

  public function handle()
  {

    SysRobo::photo_sync([

    ]);
  }
}
