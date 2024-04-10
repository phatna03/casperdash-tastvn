<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class KasWebhook extends Model
{
  use HasFactory;

  public $table = 'kas_webhooks';

  protected $fillable = [
    'restaurant_id',
    'type',
    'params',
  ];

  public function get_type()
  {
    return 'kas_webhook';
  }

  public function get_log()
  {
    return [

    ];
  }

}
