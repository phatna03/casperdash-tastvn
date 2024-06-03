<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Api\SysRobo;

class GetPhotos extends Command
{
  /**
   * The name and signature of the console command.
   *
   * @var string
   */
  protected $signature = 'local:check-images {limit} {page}';

  /**
   * The console command description.
   *
   * @var string
   */
  protected $description = 'Command: get photos from sensors';

  /**
   * Execute the console command.
   */
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
