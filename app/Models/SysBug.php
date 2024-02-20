<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SysBug extends Model
{
  use HasFactory;

  public $table = 'bugs';

  protected $fillable = [
    'type',
    'file', 'line', 'message',
    'params',
  ];
}
