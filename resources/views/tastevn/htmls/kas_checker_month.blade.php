@php
for($d = $total_days; $d > 0; $d--):
  $day = \App\Api\SysCore::str_format_hour($d);
  $m = \App\Api\SysCore::str_format_hour($month);

  $date = $year . '-' . $m . '-' . $day;
  if ($date > date('Y-m-d')) {
      continue;
  }
@endphp
<tr>
  <td class="text-center">
    <div>{{$day . '/' . $m . '/' . $year}}</div>
  </td>
  @php
  foreach($restaurants as $restaurant):
  $datas['total_orders'] = 0;
  $datas['total_photos'] = 0;

  $dnone = 'd-none';
  @endphp
    <td class="td_restaurant td_restaurant_{{$restaurant->id}}" data-value="{{$restaurant->id}}">
      <button type="button" onclick="kas_date_check_restaurant_data({{$restaurant->id}})" class="btn btn-sm btn-secondary {{$dnone}}">
        <div>
          Total Orders: <b class="total_orders">{{$datas['total_orders']}}</b>
        </div>
        <div>
          Total Photos: <b class="total_photos">{{$datas['total_photos']}}</b>
        </div>
      </button>
    </td>
  @endforeach
</tr>
@endfor
