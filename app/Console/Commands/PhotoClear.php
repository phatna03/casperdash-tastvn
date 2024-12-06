<?php

namespace App\Console\Commands;
use Illuminate\Console\Command;
//lib
use App\Api\LocalPhoto;

class PhotoClear extends Command
{
  protected $signature = 'web:photo-clear';
  protected $description = 'Command: photo clear...';

  public function handle()
  {

    LocalPhoto::photo_clear([

    ]);
  }
}
