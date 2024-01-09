<?php

namespace App\Http\Controllers\tastvn;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Aws\S3\S3Client;

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
//    $bucket_name = 'cargo.tastevietnam.asia';
//
//    $s3client = new S3Client(['region' => 'ap-southeast-1', 'version' => 'latest']);
//
//    try {
//      $contents = $s3client->listObjects([
//        'Bucket' => $bucket_name,
//      ]);
//      echo "The contents of your bucket are: \n";
//      foreach ($contents['Contents'] as $content) {
//        echo $content['Key'] . "\n";
//      }
//    } catch (Exception $exception) {
//      echo "Failed to list objects in $bucket_name with error: " . $exception->getMessage();
//      exit("Please fix error with listing objects before continuing.");
//    }
//die;

    //roboflow
    $api_key = "uYUCzsUbWxWRrO15iar5"; // Set API Key
    $model_endpoint = "-testing-cargo/1"; // Set model endpoint (Found in Dataset URL)
    $img_url = "https://s3.ap-southeast-1.amazonaws.com/cargo.tastevietnam.asia/58-5b-69-19-ad-83/SENSOR/1/2024-01-09/11/SENSOR_2024-01-09-11-34-51-568_333.jpg";

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
    echo '<pre>';
    var_dump($result);
    die;
  }
}
