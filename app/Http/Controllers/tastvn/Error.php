<?php

namespace App\Http\Controllers\tastvn;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;
use App\Models\User;

class Error extends Controller
{
  public function index()
  {
    $pageConfigs = ['myLayout' => 'blank'];
    return view('tastvn.pages.error', ['pageConfigs' => $pageConfigs]);
  }

  public function tester()
  {
    $api_key = "uYUCzsUbWxWRrO15iar5"; // Set API Key
    $model_endpoint = "-testing-cargo/1"; // Set model endpoint (Found in Dataset URL)
    $img_url = "https://genk.mediacdn.vn/139269124445442048/2020/5/17/photo-1-15896851249361295832765.jpg";

// URL for Http Request
    $url =  "https://detect.roboflow.com/" . $model_endpoint
      . "?api_key=" . $api_key
      . "&image=" . urlencode($img_url);

// Setup + Send Http request
    $options = array(
      'http' => array (
        'header' => "Content-type: application/x-www-form-urlencoded\r\n",
        'method'  => 'POST'
      ));

    $context  = stream_context_create($options);
    $result = file_get_contents($url, false, $context);
    echo $result;

  }
}
