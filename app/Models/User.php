<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

use Laravel\Sanctum\HasApiTokens;

use Carbon\Carbon;

class User extends Authenticatable
{
  use HasApiTokens, HasFactory, Notifiable;

  protected $fillable = [
    'name',
    'email',
    'password',

    'photo',
    'phone',
    'role',
    'status',
    'note',
    'time_notification',
    'creator_id',
    'deleted',

    'access_full',
    'access_ids',
    'access_texts',
  ];

  protected $hidden = [
    'password',
    'remember_token',
  ];

  protected $casts = [
    'email_verified_at' => 'datetime',
    'password' => 'hashed',
  ];

  public function get_type()
  {
    return 'user';
  }

  public function get_log()
  {
    return [
      'name' => $this->name,
      'email' => $this->email,
      'phone' => $this->phone,
      'status' => $this->status,
      'role' => $this->role,
      'note' => $this->note,
      'access_full' => $this->access_full,
      'access_ids' => $this->access_ids,
    ];
  }

  public function get_photo()
  {
    $photo = asset('assets/img/avatars/1.png');

    $row = ZaloUser::where('user_id', $this->id)
      ->first();
    if ($row && !empty($row->avatar)) {
      $photo = $row->avatar;
    }

    return $photo;
  }

  public function get_zalo()
  {
    $select = ZaloUser::where('user_id', $this->id);

    return $select->first();
  }

  public function row_setting($key)
  {
    $row = UserSetting::where('user_id', $this->id)
      ->where('key', $key)
      ->first();

    if (!$row) {
      $row = UserSetting::create([
        'user_id' => $this->id,
        'key' => $key,
      ]);
    }

    return $row;
  }

  public function set_setting($key, $value)
  {
    $row = $this->row_setting($key);

    $row->update([
      'value' => $value,
    ]);

    return $row;
  }

  public function get_setting($key)
  {
    $row = $this->row_setting($key);

    return $row ? $row->value : NULL;
  }

  public function create_restaurants()
  {
    return $this->hasMany('App\Models\Restaurant', 'creator_id');
  }

  public function info_public()
  {
    return [
      'name' => $this->name,
      'email' => $this->email,
      'photo' => $this->photo,
      'phone' => $this->phone,
      'role' => $this->role,
    ];
  }

  public function can_access_restaurant($sensor)
  {
    $permission = true;

    $roles = ['user', 'moderator'];

    if (in_array($this->role, $roles) && !$this->access_full) {

      $row = RestaurantAccess::where('user_id', $this->id)
        ->where('restaurant_parent_id', $sensor->restaurant_parent_id)
        ->first();

      if (!$row) {
        $permission = false;
      }
    }

    return $permission;
  }

  public function access_restaurants()
  {
    $this->update([
      'access_ids' => null,
      'access_texts' => '',
    ]);

    $roles = ['superadmin', 'admin'];

    if ($this->access_full || in_array($this->role, $roles)) {

      $this->update([
        'access_ids' => null,
        'access_texts' => 'All',
      ]);

    } else {

      $tblRestaurantParent = app(RestaurantParent::class)->getTable();
      $tblRestaurantAccess = app(RestaurantAccess::class)->getTable();

      $rows = RestaurantAccess::query($tblRestaurantAccess)
        ->select("$tblRestaurantParent.id", "{$tblRestaurantParent}.name", "{$tblRestaurantParent}.deleted")
        ->leftJoin($tblRestaurantParent, "{$tblRestaurantParent}.id", "=", "{$tblRestaurantAccess}.restaurant_parent_id")
        ->where("{$tblRestaurantAccess}.user_id", $this->id)
        ->get();
      if (count($rows)) {

        $ids = [];
        $texts = '';
        $count = 0;

        foreach ($rows as $row) {
          if ($row->deleted) {
            continue;
          }

          $count++;
          $ids[] = $row->id;

          if (count($rows) == $count) {
            $texts .= $row->name;
          } else {
            $texts .= $row->name . ', ';
          }
        }

        sort($ids);

        $this->update([
          'access_ids' => $ids,
          'access_texts' => $texts,
        ]);
      }
    }
  }

  public function get_restaurants()
  {
    $roles = ['superadmin', 'admin'];

    if ($this->access_full || in_array($this->role, $roles)) {
      $rows = RestaurantParent::distinct()
        ->select('id', 'name')
        ->where('deleted', 0)
        ->get();
    }
    else {

      $rows = RestaurantParent::distinct()
        ->select('id', 'name')
        ->whereIn('id', function ($q) {
          $q->select('restaurant_parent_id')
            ->from('restaurant_access')
            ->where('user_id', $this->id);
        })
        ->where('deleted', 0)
        ->get();
    }
    return $rows;
  }

  public function add_log($pars = [])
  {
    if (count($pars)) {

      $pars['user_id'] = isset($pars['user_id']) ? (int)$pars['user_id'] : $this->id;

      //check spam action
      $minutes = 3;
      $type1s = [
        'login', 'logout', 'view_profile_info', 'view_profile_setting', 'view_dashboard',
        'view_listing_notification', 'view_listing_restaurant', 'view_listing_user',
        'view_listing_food_category', 'view_listing_ingredient', 'view_listing_sensor',
        'view_listing_food', 'view_listing_photo',
        'view_listing_text', 'view_listing_setting',
        'view_listing_log', 'view_modal_testing',
      ];
      $type2s = [
        'view_item_restaurant', 'view_item_food',
        'view_item_restaurant_food_scan', 'view_item_photo',
      ];

      if (in_array($pars['type'], $type1s)) {

        $row = Log::where('user_id', $this->id)
          ->where('type', $pars['type'])
          ->where('created_at', '>=', Carbon::now()->subMinutes($minutes)->toDateTimeString())
          ->orderByDesc('id')
          ->limit(1)
          ->first();

        if ($row) {
          $row->update([
            'created_at' => date('Y-m-d H:i:s')
          ]);

          return $row;
        }

      } elseif (in_array($pars['type'], $type2s)) {

        $select = Log::where('user_id', $this->id)
          ->where('type', $pars['type'])
          ->where('created_at', '>=', Carbon::now()->subMinutes($minutes)->toDateTimeString());
        if (isset($pars['restaurant_id'])) {
          $select->where('restaurant_id', (int)$pars['restaurant_id']);
        }
        if (isset($pars['item_id'])) {
          $select->where('item_id', (int)$pars['item_id'])
            ->where('item_type', $pars['item_type']);
        }

        $row = $select->orderByDesc('id')
          ->limit(1)
          ->first();
        if ($row) {
          $row->update([
            'created_at' => date('Y-m-d H:i:s')
          ]);

          return $row;
        }

      }

      $row = Log::create($pars);

      $row->set_text();

      return $row;
    }

    return null;
  }

  public function is_moderator()
  {
    return $this->role == 'moderator' || $this->is_admin();
  }

  public function is_admin()
  {
    return $this->role == 'admin' || $this->is_super_admin() || $this->is_dev();
  }

  public function is_super_admin()
  {
    return $this->role == 'superadmin' || $this->is_dev();
  }

  public function is_dev()
  {
    return $this->role == 'dev';
  }

  public function is_chef()
  {
    return $this->role == 'chef';
  }

}
