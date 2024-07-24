<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
//lib
use App\Api\SysZalo;

class ZaloUserSend extends Model
{
  use HasFactory;

  public $table = 'zalo_user_sends';

  protected $fillable = [
    'user_id',
    'zalo_user_id',
    'type',
    'resend',
    'status',
    'params',
    'datas',
  ];

  public function get_type()
  {
    return 'zalo_user_send';
  }

  public function get_log()
  {
    return [

    ];
  }

}
