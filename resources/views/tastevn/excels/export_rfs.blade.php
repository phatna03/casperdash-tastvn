<?php
if (!isset($items) || !count($items)) {
  return;
}

?>

<table>
  <tr>
    <td></td>
    <td></td>
    <td></td>
    <td></td>
    <td></td>
    <td></td>
    <td></td>
  </tr>
  @foreach($items as $item)
    <tr>
      <td>{{$item['photo_url']}}</td>
      <td>{{$item['time_photo']}}</td>
      <td>{{$item['time_scan']}}</td>
      <td>{{$item['updated_at']}}</td>
      <td>{{$item['time_1']}}</td>
      <td>{{$item['time_2']}}</td>
      <td>{{$item['time_3']}}</td>
    </tr>
  @endforeach
</table>
