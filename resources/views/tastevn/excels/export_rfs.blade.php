<?php
if (!isset($items) || !count($items)) {
  return;
}

?>

<table>
  <tr>
    <td>Photo URL</td>
    <td>Time photo uploaded to S3</td>
    <td>Time photo stored on the web</td>
    <td>Time photo scanned by Roboflow</td>
    <td>Time system predict dish</td>
    <td>(second)S3 to Web</td>
    <td>(second)Web to Roboflow</td>
    <td>(second)system predict</td>
    <td>(second)total</td>
  </tr>
  @foreach($items as $item)
    <tr>
      <td>{{$item['id'] . ' - ' . $item['photo_url']}}</td>
      <td>{{$item['time_photo']}}</td>
      <td>{{$item['time_save']}}</td>
      <td>{{$item['time_scan']}}</td>
      <td>{{$item['time_end']}}</td>
      <td>{{$item['time_1']}}</td>
      <td>{{$item['time_2']}}</td>
      <td>{{$item['time_3']}}</td>
      <td>{{$item['time_4']}}</td>
    </tr>
  @endforeach
</table>
