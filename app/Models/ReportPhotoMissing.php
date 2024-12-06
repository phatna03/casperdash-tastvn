<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReportPhotoMissing extends Model
{
  use HasFactory;

  public $table = 'report_photo_missings';

  protected $fillable = [
    'report_photo_id',
    'ingredient_id',
    'quantity',
  ];

  public function get_type()
  {
    return 'report_photo_missing';
  }


}
