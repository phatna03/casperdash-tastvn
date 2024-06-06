<table class="table-responsive table-bordered w-100">
  <tr>
    <th class="text-center align-middle" rowspan="4">Dishes</th>
    <th class="text-center align-middle" colspan="5">Robot found dishes</th>
    <th class="text-center align-middle" rowspan="4">Robot not<br/> found dish</th>
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
    <th class="text-center align-middle">Total photos</th>
    <th class="text-center align-middle">Points achieved</th>
    <th class="text-center align-middle">Points deducted</th>
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
  <tr>
    <td class="text-dark">{{$stt . '. ' . $item['food_name']}}</td>
    <td class="text-center">
      @if($item['ing_full'])
        <div class="fnumber text-dark">{{$item['ing_full']}}</div>
      @endif
    </td>
    <td class="text-center">
      @if($item['ing_miss_right'])
        <div class="fnumber text-dark">{{$item['ing_miss_right']}}</div>
      @endif
    </td>
    <td class="text-center">
      @if($item['ing_miss_wrong_total'])
        <div class="fnumber text-dark">{{$item['ing_miss_wrong_total']}}</div>
      @endif
    </td>
    <td class="text-center">
      @if($item['ing_miss_wrong_point'])
        <div class="fnumber text-dark">{{$item['ing_miss_wrong_point']}}</div>
      @endif
    </td>
    <td class="text-center">
      @if($item['ing_miss_wrong_failed'])
        <div class="fnumber text-dark">{{$item['ing_miss_wrong_failed']}}</div>
      @endif
    </td>
    <td class="text-center">
      @if($item['not_found'])
        <div class="fnumber text-dark">{{$item['not_found']}}</div>
      @endif
    </td>
    <td class="text-center">
      @if($item['total_points'])
        <div class="fnumber text-dark">{{$item['total_points']}}</div>
      @endif
    </td>
    <td class="text-center">
      @if($item['total_points'])
        <div class="nfnumber text-dark">{{$item['point']}}</div>
      @endif
    </td>
    <td class="text-center">
      @if($item['total_points'])
        <div class="nfnumber text-dark">{{$rate}}</div>
      @endif
    </td>
  </tr>
  @endforeach
</table>
