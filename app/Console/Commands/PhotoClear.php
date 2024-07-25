<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

use App\Api\SysApp;
use App\Api\SysRobo;
use App\Models\RestaurantFoodScan;

class PhotoClear extends Command
{
  protected $signature = 'web:photo-clear';
  protected $description = 'Command: photo clear...';

  public function handle()
  {

    SysRobo::photo_clear([

    ]);
  }
}
