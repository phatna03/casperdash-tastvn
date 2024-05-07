<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

use Aws\S3\S3Client;
use App\Api\SysCore;

class RestaurantParent extends Model
{
  use HasFactory;

  public $table = 'restaurant_parents';

  protected $fillable = [
    'name',
    'deleted',
  ];

  public function get_type()
  {
    return 'restaurant_parent';
  }

}
