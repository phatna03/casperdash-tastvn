<?php
if (!isset($items) || !count($items)) {
  return;
}

?>

<table>
  <tr>
    <td>Photo URL</td>
    <td>Restaurant</td>
    <td>Time photo screenshot</td>
    <td>Time photo uploaded to S3</td>
    <td></td>
    <td>Time photo stored on the web</td>
    <td>Time photo scanned by Roboflow</td>
    <td>Time system predict dish</td>
    <td>(second)Screenshot to S3</td>
    <td>(second)S3 to Web</td>
    <td>(second)Web to Roboflow</td>
    <td>(second)system predict</td>
    <td>(second)total</td>
  </tr>
  @foreach($items as $item)
    <tr>
      <td>{{$item['id'] . ' - ' . $item['photo_url']}}</td>
      <td>{{$item['restaurant_name']}}</td>
      <td>{{$item['time_photo']}}</td>
      <td>{{$item['time_s3']}}</td>
      <td>{{$item['error_s3']}}</td>
      <td>{{$item['time_web']}}</td>
      <td>{{$item['time_scan']}}</td>
      <td>{{$item['time_end']}}</td>
      <td>{{$item['time_1']}}</td>
      <td>{{$item['time_2']}}</td>
      <td>{{$item['time_3']}}</td>
      <td>{{$item['time_4']}}</td>
      <td>{{$item['time_5']}}</td>
    </tr>
  @endforeach
</table>
