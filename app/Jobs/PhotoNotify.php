<?php

namespace App\Jobs;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
//lib
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use App\Notifications\IngredientMissing;
use App\Api\SysZalo;
use App\Models\SysNotification;
use App\Models\RestaurantFoodScan;

class PhotoNotify implements ShouldQueue
{
  use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

  protected $rfs; // you forgot of put this line

  /**
   * Create a new job instance.
   */
  public function __construct(RestaurantFoodScan $rfs)
  {
    $this->rfs = $rfs;
  }

  /**
   * Execute the job.
   */
  public function handle(): void
  {
    $rfs = $this->rfs;

    $food = $rfs->get_food();
    $sensor = $rfs->get_restaurant();
    $restaurant = $sensor->get_parent();
    $users = $sensor->get_users();

    $ingredients = $rfs->get_ingredients_missing();

    //notify
    if (count($ingredients) && count($users)) {
      $live_group = $restaurant->get_food_live_group($food);

      foreach ($users as $user) {

        //live_group
        $valid_group = true;
        if ($live_group > 1 || $rfs->confidence < 85) {
          $valid_group = false;
        }
        if ($live_group == 2 && count($ingredients) < 2 && $rfs->confidence > 85) {
//          $valid_group = true;
        }
//        if ($user->is_super_admin()) {
//          $valid_group = true;
//        }
        if (count($ingredients) >= 3) {
          $valid_group = false;
        }

        //isset notify
        $notify = DB::table('notifications')
          ->distinct()
          ->where('notifiable_type', 'App\Models\User')
          ->where('notifiable_id', $user->id)
          ->where('restaurant_food_scan_id', $rfs->id)
          ->whereIn('type', ['App\Notifications\IngredientMissing'])
          ->orderBy('id', 'desc')
          ->limit(1)
          ->first();

        if (!$valid_group || $notify) {
          continue;
        }

        //notify db
        Notification::sendNow($user, new IngredientMissing([
          'restaurant_food_scan_id' => $rfs->id,
        ]), ['database']);

        //temp off
        //notify mail
//        if ((int)$user->get_setting('missing_ingredient_alert_email')) {
//          $user->notify((new IngredientMissingMail([
//            'type' => 'ingredient_missing',
//            'restaurant_id' => $sensor->id,
//            'restaurant_food_scan_id' => $this->id,
//            'user' => $user,
//          ]))->delay([
//            'mail' => now()->addMinutes(5),
//          ]));
//        }

        //notify zalo
        SysZalo::send_rfs_note($user, 'ingredient_missing', $rfs);

        //notify db update
        $rows = $user->notifications()
          ->whereIn('type', ['App\Notifications\IngredientMissing'])
          ->where('data', 'LIKE', '%{"restaurant_food_scan_id":' . $rfs->id . '}%')
          ->where('restaurant_food_scan_id', 0)
          ->get();
        if (count($rows)) {
          foreach ($rows as $row) {
            $notify = SysNotification::find($row->id);
            if ($notify) {
              $notify->update([
                'restaurant_food_scan_id' => $rfs->id,
                'restaurant_id' => $sensor->id,
                'food_id' => $food ? $food->id : 0,
                'object_type' => 'restaurant_food_scan',
                'object_id' => $rfs->id,
                'data' => json_encode([
                  'status' => 'valid'
                ]),
              ]);
            }
          }
        }
      }
    }
  }
}
