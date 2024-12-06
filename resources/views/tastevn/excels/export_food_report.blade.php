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
  </tr>
  @foreach($items as $item)
    <tr>
      <td>{{$item['name']}}</td>
      <td>{{$item['total_photos']}}</td>
      <td>{{$item['photo_missing']}}</td>
    </tr>
  @endforeach
</table>
