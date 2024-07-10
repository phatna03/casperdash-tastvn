<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

use App\Api\SysApp;
use App\Api\SysRobo;
use App\Models\RestaurantFoodScan;

class ClearLocalImages extends Command
{
  protected $signature = 'local:clear-images';
  protected $description = 'Command: clear local photos';

  public function handle()
  {

    SysRobo::photo_clear([

    ]);
  }
}
