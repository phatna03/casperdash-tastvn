<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use App\Api\SysCore;
use App\Models\Restaurant;

class PhotoScan implements ShouldQueue
{
  use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

  public $restaurant;

  /**
   * Create a new job instance.
   */
  public function __construct($restaurant)
  {
    $this->restaurant = $restaurant;
  }

  public function uniqueId()
  {
    return $this->restaurant->id;
  }

  /**
   * Execute the job.
   */
  public function handle(): void
  {
    $api_core = new SysCore();
    $api_core->rbf_scan_photos([
      'restaurant_id' => $this->restaurant->id,
    ]);
  }
}
