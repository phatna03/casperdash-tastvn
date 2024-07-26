<?php

namespace App\Console\Commands;
use Illuminate\Console\Command;
//lib
use App\Api\SysRobo;

class PhotoScan extends Command
{
  protected $signature = 'web:photo-scan {limit} {page}';
  protected $description = 'Command: photo scan...';

  public function handle()
  {
    $limit = $this->argument('limit');
    $page = $this->argument('page');

    SysRobo::photo_handle([
      'limit' => $limit,
      'page' => $page,
    ]);
  }
}
