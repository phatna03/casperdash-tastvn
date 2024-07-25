<?php

namespace App\Console\Commands;
use Illuminate\Console\Command;
//lib
use App\Api\SysRobo;

class PhotoGet extends Command
{
  protected $signature = 'web:photo-get {limit} {page}';
  protected $description = 'Command: photo get...';

  public function handle()
  {
    $limit = $this->argument('limit');
    $page = $this->argument('page');

    SysRobo::photo_get([
      'limit' => $limit,
      'page' => $page,
    ]);
  }
}
