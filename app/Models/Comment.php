<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

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
}
