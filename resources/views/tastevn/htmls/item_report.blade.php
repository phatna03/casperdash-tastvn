<table class="table-responsive acm-table table-bordered w-100">
  <tr>
    <th class="text-center align-middle" rowspan="4">Dishes</th>
    <th class="text-center align-middle" colspan="5">Robot found dishes</th>
    <th class="text-center align-middle" rowspan="3" colspan="3">Robot not<br/> found dishes</th>
    <th class="text-center align-middle" rowspan="4">Total<br/> points</th>
    <th class="text-center align-middle" rowspan="4">Points<br/> achieved</th>
    <th class="text-center align-middle" rowspan="4">Rate<br/> (%)</th>
  </tr>
  <tr>
    <th class="text-center align-middle" rowspan="3">Full<br/> ingredients</th>
    <th class="text-center align-middle" colspan="4">Missing ingredients</th>
  </tr>
  <tr>
    <th class="text-center align-middle" rowspan="2">Robot<br/> found right</th>
    <th class="text-center align-middle" colspan="3">Robot found wrong</th>
  </tr>
  <tr>
    <th class="text-center align-middle">Total<br/> photos</th>
    <th class="text-center align-middle">Points<br/> achieved</th>
    <th class="text-center align-middle">Points<br/> deducted</th>
    <th class="text-center align-middle">Total<br/> photos</th>
    <th class="text-center align-middle">Points<br/> achieved</th>
    <th class="text-center align-middle">Points<br/> deducted</th>
  </tr>
  @php
    $stt = 0;
    foreach($items as $item):
    $stt++;

    $rate = 0;
    if ($item['total_points']) {
        $rate = $item['point'] / $item['total_points'] * 100;
    }
    if ($rate < 100) {
    $rate = number_format($rate, 2, '.', '');
    }
  @endphp
  <tr class="food_item_report"
      data-food_id="{{$item['food_id']}}"
  >
    <td class="text-dark position-relative acm-clearfix">
      @if(count($item['ing_miss_items']))
      <div class="acm-float-right acm-ml-px-5">
        <button type="button" class="btn btn-primary p-0" onclick="$('.tr_sub_food_{{$item['food_id']}}').toggleClass('d-none');">
          <i class="mdi mdi-expand-all"></i>
        </button>
      </div>
      @endif
      <div>{{$stt . '. ' . $item['food_name']}}</div>
    </td>
    <td class="text-center">
      @if($item['ing_full'])
        <div class="fnumber text-primary cursor-pointer"
          onclick="report_photo_nf_full('{{$item['food_id']}}', 'full')"
        >{{$item['ing_full']}}</div>
      @endif
    </td>
    <td class="text-center">
      @if($item['ing_miss_right'])
        <div class="fnumber text-primary cursor-pointer"
             onclick="report_photo_nf_full('{{$item['food_id']}}', 'miss_right')"
        >{{$item['ing_miss_right']}}</div>
      @endif
    </td>
    <td class="text-center">
      @if($item['ing_miss_wrong_total'])
        <div class="fnumber text-dark cursor-pointer"
             onclick="report_photo_nf_full('{{$item['food_id']}}', 'miss_wrong')"
        >{{$item['ing_miss_wrong_total']}}</div>
      @endif
    </td>
    <td class="text-center">
      @if($item['ing_miss_wrong_point'] > 0)
        <div class="nfnumber text-primary cursor-pointer"
             onclick="report_photo_nf_full('{{$item['food_id']}}', 'miss_wrong')"
        >{{$item['ing_miss_wrong_point']}}</div>
      @endif
    </td>
    <td class="text-center">
      @if($item['ing_miss_wrong_failed'] > 0)
        <div class="nfnumber text-danger cursor-pointer"
             onclick="report_photo_nf_full('{{$item['food_id']}}', 'miss_wrong')"
        >{{$item['ing_miss_wrong_failed']}}</div>
      @endif
    </td>
    <td class="text-center">
      @if($item['not_found_total'])
        <div class="fnumber text-dark cursor-pointer"
             onclick="report_photo_nf_full('{{$item['food_id']}}', 'nf_wrong')"
        >{{$item['not_found_total']}}</div>
      @endif
    </td>
    <td class="text-center">
      @if($item['not_found_point'] > 0)
        <div class="nfnumber text-primary cursor-pointer"
             onclick="report_photo_nf_full('{{$item['food_id']}}', 'nf_wrong')"
        >{{$item['not_found_point']}}</div>
      @endif
    </td>
    <td class="text-center">
      @if($item['not_found_failed'] > 0)
        <div class="nfnumber text-danger cursor-pointer"
             onclick="report_photo_nf_full('{{$item['food_id']}}', 'nf_wrong')"
        >{{$item['not_found_failed']}}</div>
      @endif
    </td>
    <td class="text-center">
      @if($item['total_points'])
        <div class="fnumber text-dark fw-bold">{{$item['total_points']}}</div>
      @endif
    </td>
    <td class="text-center">
      @if($item['total_points'])
        <div class="nfnumber text-primary">{{$item['point']}}</div>
      @endif
    </td>
    <td class="text-center">
      @if($item['total_points'])
        <div class="nfnumber text-dark">{{$rate}}</div>
      @endif
    </td>
  </tr>
  @if(count($item['ing_miss_items']))
    <tr class="tr_sub_food tr_sub_food_{{$item['food_id']}} d-none">
      <td></td>
      <td></td>
      <td class="text-center align-middle text-dark bg-warning-subtle">Quantities</td>
      <td colspan="6" class="align-middle text-dark bg-warning-subtle">
        <div class="acm-ml-px-10">Missing ingredients</div>
      </td>
      <td></td>
      <td></td>
      <td></td>
    </tr>
    @foreach($item['ing_miss_items'] as $ing)
      <tr class="tr_sub_food tr_sub_food_{{$item['food_id']}} d-none">
        <td></td>
        <td></td>
        <td class="text-center">
          <div class="fnumber text-dark">{{$ing['ingredient_total']}}</div>
        </td>
        <td colspan="6">
          <div class="text-dark acm-ml-px-10">{{$ing['ingredient_name']}}</div>
        </td>
        <td></td>
        <td></td>
        <td></td>
      </tr>
    @endforeach
  @endif
  @endforeach
</table>
