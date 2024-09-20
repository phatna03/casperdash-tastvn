<div class="row">
  @if (count($stats))
  <div class="col-lg-12 mb-1 acm-clearfix">
    <table class="table table-bordered table-sm">
      <thead>
      <tr>
        <td class="text-dark bg-secondary-subtle fw-bold">Dish name</td>
        <td class="text-dark bg-secondary-subtle fw-bold">Total quantity from KAS</td>
        <td class="text-dark bg-secondary-subtle fw-bold">Total quantity from Sensor</td>
      </tr>
      </thead>
      <tbody>
      @foreach($stats as $stat)
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
