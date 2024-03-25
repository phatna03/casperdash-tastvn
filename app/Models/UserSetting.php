<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class UserSetting extends Model
{
  use HasFactory;

  public $table = 'user_settings';

  protected $fillable = [
    'user_id',
    'key',
    'value',
  ];


}
