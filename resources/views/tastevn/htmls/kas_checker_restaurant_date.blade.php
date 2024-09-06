<div class="row">
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
        <div class="hour_photo_item hour_photo_item_{{$hour2->hour}}  mb-1">
          <button type="button" onclick="kas_date_check_restaurant_data_hour_photo('{{$hour2->hour}}', '{{$date}}', '{{$restaurant->id}}')"
                  class="btn btn-sm w-100 btn-secondary">{{$hour2->hour}}</button>
        </div>
      @endforeach
    </div>
    <div class="acm-border-css overflow-hidden p-1">
      <div class="text-center w-auto p-1">

      </div>
    </div>
  </div>
</div>
