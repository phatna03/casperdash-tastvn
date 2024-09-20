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

  $bill = isset($datas[$restaurant->id][$date]) ? $datas[$restaurant->id][$date]['total_bills'] : 0;
  $photo = isset($datas[$restaurant->id][$date]) ? $datas[$restaurant->id][$date]['total_photos'] : 0;

  $dnone2 = !$photo ? 'd-none' : '';
  $dnone1 = !$bill ? 'd-none' : '';
  @endphp
    <td class="td_restaurant p-1 td_restaurant_{{$restaurant->id}}" data-value="{{$restaurant->id}}">
      <button type="button" class="btn btn-sm btn-secondary p-1 w-50 acm-float-right total_photos {{$dnone2}}"
              onclick="kas_date_check_restaurant_data_photo({{$restaurant->id}}, '{{$datas[$restaurant->id][$date]['date_text']}}')">
        <div>
          Total Photos: <div class="total_photos">{{$photo}}</div>
        </div>
      </button>

      <button type="button" class="btn btn-sm btn-primary p-1 w-50 acm-float-left total_orders {{$dnone1}}"
              onclick="kas_date_check_restaurant_data({{$restaurant->id}}, '{{$datas[$restaurant->id][$date]['date_text']}}')" >
        <div>
          Total Bills: <div class="total_orders">{{$bill}}</div>
        </div>
      </button>
    </td>
  @endforeach
</tr>
@endfor
