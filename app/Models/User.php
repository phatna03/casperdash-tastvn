<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

use Laravel\Sanctum\HasApiTokens;

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

    'ips_printer',
  ];

  protected $hidden = [
    'password',
    'remember_token',
  ];

  protected $casts = [
    'email_verified_at' => 'datetime',
    'password' => 'hashed',
  ];

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

  public function can_access_restaurant($restaurant)
  {
    $permission = true;

    if ($this->role == 'moderator' && !$this->access_full) {

      $row = RestaurantAccess::where('user_id', $this->id)
        ->where('restaurant_id', $restaurant->id)
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

    $roles = ['superadmin', 'admin', 'user'];

    if ($this->access_full || in_array($this->role, $roles)) {

      $this->update([
        'access_ids' => null,
        'access_texts' => 'All',
      ]);

    } else {

      $tblRestaurant = app(Restaurant::class)->getTable();
      $tblRestaurantAccess = app(RestaurantAccess::class)->getTable();

      $rows = RestaurantAccess::query($tblRestaurantAccess)
        ->select("$tblRestaurant.id", "{$tblRestaurant}.name", "{$tblRestaurant}.deleted")
        ->leftJoin($tblRestaurant, "{$tblRestaurant}.id", "=", "{$tblRestaurantAccess}.restaurant_id")
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
}
