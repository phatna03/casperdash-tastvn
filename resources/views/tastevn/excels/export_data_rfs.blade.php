@php
$items = isset($items) ? (array)$items: [];
if (!count($items)) {
    return;
}
@endphp

<table>
  <tbody>
  <tr>
    <td></td>
    <td></td>
    <td>Time Photo</td>
    <td>Time Scan</td>
    <td>Time End</td>
    <td>Total(seconds)</td>
  </tr>
  @foreach($items as $item)
    <tr>
      <td>{{$item['id']}}</td>
      <td>{{$item['photo_url']}}</td>
      <td>{{$item['time_photo']}}</td>
      <td>{{$item['time_scan']}}</td>
      <td>{{$item['time_end']}}</td>
      <td>{{!empty($item['time_end'])
        ? (int)date('s', strtotime($item['time_end']) - strtotime($item['time_photo'])) : 0}}</td>
    </tr>
  @endforeach
  </tbody>
</table>
