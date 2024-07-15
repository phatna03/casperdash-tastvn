<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use App\Notifications\PhotoComment;
use App\Notifications\PhotoCommentMail;
//lib
use App\Api\SysApp;
use App\Api\SysZalo;

class Comment extends Model
{
  use HasFactory;

  public $table = 'comments';

  protected $fillable = [
    'user_id',
    'content',
    'object_type',
    'object_id',
    'edited',
    'deleted',
  ];

  public function get_type()
  {
    return 'comment';
  }

  public function get_log()
  {
    return [
      'content' => $this->content
    ];
  }

  public function owner()
  {
    return $this->belongsTo('App\Models\User', 'user_id');
  }

  public function get_object()
  {
    $sys_app = new SysApp();

    return !empty($this->object_type) ? $sys_app->get_item($this->object_id, $this->object_type) : null;
  }

  public function on_create_after()
  {
    //notify
    if ($this->object_type == 'restaurant_food_scan'
      && $this->get_object() && $this->get_object()->get_restaurant()
    ) {

      $rfs = RestaurantFoodScan::find($this->object_id);
      $sensor = $rfs->get_restaurant();
      $food = $rfs->get_food();

      $ids = [];

      //commenters
      $commenters = $rfs->get_comments();
      if (count($commenters)) {
        foreach ($commenters as $commenter) {
          $user = $commenter->owner;

          if (($user && $this->owner && $user == $this->owner)
            || in_array($user->id, $ids)
          ) {
            continue;
          }

          $ids[] = $user->id;

          //notify db
          Notification::send($user, new PhotoComment([
            'typed' => 'photo_reply_add',
            'restaurant_food_scan_id' => $rfs->id,
            'owner_id' => $this->owner->id,
            'comment_id' => $this->id,
          ]));

          //notify db update
          $rows = $user->notifications()
            ->whereIn('type', ['App\Notifications\PhotoComment'])
            ->where('data', 'LIKE', '%{"typed":"photo_reply_add","restaurant_food_scan_id":' . $rfs->id . ',%')
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
                  'object_type' => 'comment',
                  'object_id' => $this->id,
                  'data' => json_encode([
                    'status' => 'valid',
                    'typed' => 'photo_reply_add',
                    'owner_id' => $this->owner->id,
                    'content' => $this->content,
                  ]),
                ]);
              }
            }
          }

          //notify zalo
          SysZalo::send_rfs_note($user, 'photo_comment', $rfs);

        }
      }

      $users = $sensor->get_users();
      if (count($users)) {
        foreach ($users as $user) {
          if (($user && $this->owner && $user == $this->owner)
            || in_array($user->id, $ids)
          ) {
            continue;
          }

          //notify db
          Notification::send($user, new PhotoComment([
            'typed' => 'photo_comment_add',
            'restaurant_food_scan_id' => $rfs->id,
            'owner_id' => $this->owner->id,
            'comment_id' => $this->id,
          ]));

          //notify db update
          $rows = $user->notifications()
            ->whereIn('type', ['App\Notifications\PhotoComment'])
            ->where('data', 'LIKE', '%{"typed":"photo_comment_add","restaurant_food_scan_id":' . $rfs->id . ',%')
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
                  'object_type' => 'comment',
                  'object_id' => $this->id,
                  'data' => json_encode([
                    'status' => 'valid',
                    'typed' => 'photo_comment_add',
                    'owner_id' => $this->owner->id,
                    'content' => $this->content,
                  ]),
                ]);
              }
            }
          }

          //notify zalo
          SysZalo::send_rfs_note($user, 'photo_comment', $rfs);

          //temp off
          //notify mail
//            if ((int)$user->get_setting('photo_comment_alert_email')) {
//              $user->notify((new PhotoCommentMail([
//                'typed' => 'photo_comment_add',
//                'restaurant_food_scan_id' => $rfs->id,
//                'user' => $user,
//                'owner_id' => $this->owner->id,
//                'comment_id' => $this->id,
//              ]))->delay([
//                'mail' => now()->addMinutes(5),
//              ]));
//            }

        }
      }
    }
  }

  public function on_update_after()
  {
    //notify
    if ($this->object_type == 'restaurant_food_scan'
      && $this->get_object() && $this->get_object()->get_restaurant()
    ) {

      $rfs = RestaurantFoodScan::find($this->object_id);
      $sensor = $rfs->get_restaurant();
      $food = $rfs->get_food();

      $ids = [];

      //commenters
      $commenters = $rfs->get_comments();
      if (count($commenters)) {
        foreach ($commenters as $commenter) {
          $user = $commenter->owner;

          if (($user && $this->owner && $user == $this->owner)
            || in_array($user->id, $ids)
          ) {
            continue;
          }

          $ids[] = $user->id;

          //notify db
          Notification::send($user, new PhotoComment([
            'typed' => 'photo_reply_edit',
            'restaurant_food_scan_id' => $rfs->id,
            'owner_id' => $this->owner->id,
            'comment_id' => $this->id,
          ]));

          //notify db update
          $rows = $user->notifications()
            ->whereIn('type', ['App\Notifications\PhotoComment'])
            ->where('data', 'LIKE', '%{"typed":"photo_reply_edit","restaurant_food_scan_id":' . $rfs->id . ',%')
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
                  'object_type' => 'comment',
                  'object_id' => $this->id,
                  'data' => json_encode([
                    'status' => 'valid',
                    'typed' => 'photo_reply_edit',
                    'owner_id' => $this->owner->id,
                    'content' => $this->content,
                  ]),
                ]);
              }
            }
          }

          //notify zalo
          SysZalo::send_rfs_note($user, 'photo_comment', $rfs);

        }
      }

      $users = $sensor->get_users();
      if (count($users)) {
        foreach ($users as $user) {
          if (($user && $this->owner && $user == $this->owner)
            || in_array($user->id, $ids)
          ) {
            continue;
          }

          //notify db
          Notification::send($user, new PhotoComment([
            'typed' => 'photo_comment_edit',
            'restaurant_food_scan_id' => $rfs->id,
            'owner_id' => $this->owner->id,
            'comment_id' => $this->id,
          ]));

          //notify db update
          $rows = $user->notifications()
            ->whereIn('type', ['App\Notifications\PhotoComment'])
            ->where('data', 'LIKE', '%{"typed":"photo_comment_edit","restaurant_food_scan_id":' . $rfs->id . ',%')
            ->where('restaurant_food_scan_id', 0)
            ->get();
          if (count($rows)) {
            foreach ($rows as $row) {
              $notify = SysNotification::find($row->id);
              if ($notify) {
                $notify->update([
                  'restaurant_food_scan_id' => $rfs->id,
                  'restaurant_id' => $rfs->get_restaurant()->id,
                  'food_id' => $rfs->get_food() ? $rfs->get_food()->id : 0,
                  'object_type' => 'comment',
                  'object_id' => $this->id,
                  'data' => json_encode([
                    'status' => 'valid',
                    'typed' => 'photo_comment_edit',
                    'owner_id' => $this->owner->id,
                    'content' => $this->content,
                  ]),
                ]);
              }
            }
          }

          //notify zalo
          SysZalo::send_rfs_note($user, 'photo_comment', $rfs);

          //temp off
          //notify mail
//            if ((int)$user->get_setting('photo_comment_alert_email')) {
//              $user->notify((new PhotoCommentMail([
//                'typed' => 'photo_comment_edit',
//                'restaurant_food_scan_id' => $rfs->id,
//                'user' => $user,
//                'owner_id' => $this->owner->id,
//                'comment_id' => $this->id,
//              ]))->delay([
//                'mail' => now()->addMinutes(5),
//              ]));
//            }

        }
      }
    }
  }
}
