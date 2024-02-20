<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PasswordResetToken extends Model
{
  use HasFactory;

  public $table = 'password_reset_tokens';

  protected $fillable = [
    'email',
    'token',
  ];

  protected $primaryKey = null;
  public $incrementing = false;
  public $timestamps = false;
}
