<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Api\SysCore;

class TodoAPI extends Command
{
  /**
   * The name and signature of the console command.
   *
   * @var string
   */
  protected $signature = 'tastevn:s3todo {limit} {page}';

  /**
   * The console command description.
   *
   * @var string
   */
  protected $description = 'Command: get photos from aws s3 buckets';

  /**
   * Execute the console command.
   */
  public function handle()
  {
    $api_core = new SysCore();

    $limit = $this->argument('limit');
    $page = $this->argument('page');

    $api_core->v3_s3_todo([
      'limit' => $limit,
      'page' => $page,
    ]);
  }
}
