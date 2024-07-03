<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
//lib
use App\Api\SysZalo;

class ZaloUser extends Model
{
  use HasFactory;

  public $table = 'zalo_users';

  protected $fillable = [
    'user_id',
    'zalo_user_id',
    'user_id_by_app',
    'display_name',
    'user_alias',
    'user_phone',
    'avatar',
    'is_follower',
    'shared_info',
    'datas',
  ];

  public function get_type()
  {
    return 'zalo_user';
  }

  public function get_log()
  {
    return [

    ];
  }

  public function get_detail($pars = [])
  {
    $force = isset($pars['force']) ? (bool)$pars['force'] : false;

    if ($force || !$this->user_id_by_app) {
      $datas = SysZalo::user_detail($this->zalo_user_id);

      if (count($datas)) {
        $temps = (array)$datas['data'];

        if (count($temps)) {

//          var_dump($temps);

          $shareds = isset($temps['shared_info']) ? (array)$temps['shared_info'] : [];

          $this->update([
            'user_id_by_app' => $temps['user_id_by_app'],
            'display_name' => $temps['display_name'],
            'user_alias' => $temps['user_alias'],
            'user_phone' => count($shareds) ? $shareds['phone'] : $this->user_phone,
            'avatar' => $temps['avatar'],
            'is_follower' => $temps['user_is_follower'] ? 1 : 0,
            'shared_info' => count($shareds) ? 1 : 0,
            'datas' => json_encode($temps),
          ]);
        }
      }
    }

    return $this;
  }
}
