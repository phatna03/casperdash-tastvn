@php
$total_kas_dishes = 0;
$total_web_dishes = 0;
@endphp

<div class="row">
  @if (count($stats))
  <div class="col-lg-12 mb-1 acm-clearfix acm-height-200-max acm-overflow-y-auto">
    <table class="table table-bordered table-sm">
      <thead>
      <tr>
        <td class="text-dark bg-secondary-subtle fw-bold">Dish name</td>
        <td class="text-dark bg-secondary-subtle fw-bold">Total quantity from KAS POS <b class="d-none text-bg-danger p-2"  id="total_dishes_kas"></b></td>
        <td class="text-dark bg-secondary-subtle fw-bold">Total quantity from Sensor <b class="d-none text-bg-danger p-2"  id="total_dishes_web"></b></td>
      </tr>
      </thead>
      <tbody>
      @php
      $arr = [];
      foreach($stats as $stat):
      if (in_array($stat['item_code'], $arr)) {
          continue;
      }

      $arr[] = $stat['item_code'];
      $total_kas_dishes += $stat['total_quantity_kas'];

      if (isset($stat['total_quantity_web']) && (int)$stat['total_quantity_web']) {
        $total_web_dishes += $stat['total_quantity_web'];
      }

      @endphp
        <tr>
          <td>
            <div class="text-dark">
              <div>{{$stat['item_name'] != $stat['food_name'] ? $stat['item_code'] . ' - ' . $stat['item_name'] : $stat['item_code'] . ' - ' . $stat['food_name']}}</div>
            </div>
          </td>
          <td>
            <div class="text-dark text-center fnumber">{{$stat['total_quantity_kas']}}</div>
          </td>
          <td>
            <div class="text-dark text-center fnumber">{{isset($stat['total_quantity_web']) && (int)$stat['total_quantity_web'] ? (int)$stat['total_quantity_web'] : ''}}</div>
          </td>
        </tr>
      @endforeach
      <tr>
        <td class="acm-text-right">
          <b>Tổng cộng: </b>
        </td>
        <td>
          <div class="text-danger text-center fnumber fw-bold">{{$total_kas_dishes}}</div>
        </td>
        <td>
          <div class="text-danger text-center fnumber fw-bold">{{$total_web_dishes}}</div>
        </td>
      </tr>
      </tbody>
    </table>
  </div>
  @endif

  <div class="col-lg-6 mb-1 acm-clearfix" id="wrap_hour_bill">
    <div class="acm-float-left acm-mr-px-5 acm-text-right">
      <div class="mb-1">
        <div class="badge bg-primary">HOUR</div>
      </div>
      @foreach($hour1s as $hour1)
        <div class="hour_bill_hour hour_bill_hour_{{$hour1->hour}}  mb-1">
          <button type="button" onclick="kas_date_check_restaurant_data_hour_bill('{{$hour1->hour}}', '{{$date}}', '{{$restaurant->id}}')"
                  class="btn btn-sm w-100 btn-secondary">{{$hour1->hour}}</button>
        </div>
      @endforeach
    </div>
    <div class="acm-border-css overflow-hidden p-1">
      <div class="w-auto p-1 hour_bill_datas">

      </div>
    </div>
  </div>

  <div class="col-lg-6 mb-1 acm-clearfix" id="wrap_hour_photo">
    <div class="acm-float-right acm-ml-px-5 acm-text-right">
      <div class="mb-1">
        <div class="badge bg-primary">HOUR</div>
      </div>
      @foreach($hour2s as $hour2)
        <div class="hour_photo_hour hour_photo_hour_{{$hour2->hour}}  mb-1">
          <button type="button" onclick="kas_date_check_restaurant_data_hour_photo('{{$hour2->hour}}', '{{$date}}', '{{$restaurant->id}}')"
                  class="btn btn-sm w-100 btn-secondary">{{$hour2->hour}}</button>
        </div>
      @endforeach
    </div>
    <div class="acm-border-css overflow-hidden p-1">
      <div class="text-center w-auto p-1 hour_photo_datas">

      </div>
    </div>
  </div>
</div>

<script type="text/javascript">
  @if($total_kas_dishes)
  $('#total_dishes_kas').text({{$total_kas_dishes}}).removeClass('d-none');
  @endif

  @if($total_web_dishes)
  $('#total_dishes_web').text({{$total_web_dishes}}).removeClass('d-none');
  @endif
</script>
