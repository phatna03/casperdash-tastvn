<?php

namespace App\Console\Commands;
use Illuminate\Console\Command;
//lib
use App\Api\CronAws;

class PhotoSync extends Command
{
  protected $signature = 'web:photo-sync';
  protected $description = 'Command: photo sync...';

  public function handle()
  {

    CronAws::photo_sync([

    ]);
  }
}
