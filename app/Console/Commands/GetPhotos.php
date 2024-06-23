<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Api\SysRobo;

class GetPhotos extends Command
{
  protected $signature = 'local:check-images {limit} {page}';
  protected $description = 'Command: get photos from sensors';

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
