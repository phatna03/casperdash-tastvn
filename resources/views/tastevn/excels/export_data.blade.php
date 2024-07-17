@php
$items = isset($items) ? (array)$items: [];
if (!count($items)) {
    return;
}
@endphp

<table>
  <tbody>
  @php
  $count = 0;
    foreach($items as $k => $temps):
  $count++;
  @endphp
    @if($count == 1)
      <tr>
        <td></td>
        @foreach($temps as $temp)
        <td colspan="2">{{$temp['sensor_id'] . ' - ' . $temp['sensor_name']}}</td>
        @endforeach
      </tr>
    @endif
    <tr>
      <td>{{date('d/m/Y', strtotime($k))}}</td>
      @foreach($temps as $temp)
      <td>{{$temp['photo_total']}}</td>
      <td>{{$temp['photo_valid']}}</td>
      @endforeach
    </tr>
  @endforeach
  </tbody>
</table>
