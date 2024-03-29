<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SysSetting extends Model
{
  use HasFactory;

  public $table = 'settings';

  protected $fillable = [
    'key',
    'value',
  ];

  public function get_type()
  {
    return 'setting';
  }
}
