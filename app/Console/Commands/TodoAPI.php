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
  protected $signature = 'tastevn:todolist';

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

    // Run the logic every 30 seconds
//    if (now()->second % 30 == 0) {
      $api_core->s3_todo();
//    }
  }
}
