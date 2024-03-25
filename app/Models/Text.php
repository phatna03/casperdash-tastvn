<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Text extends Model
{
  use HasFactory;

  public $table = 'texts';

  protected $fillable = [
    'name',
    'creator_id',
    'deleted',
  ];

}
