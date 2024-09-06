@foreach($items as $item)
<div class="hour_bill_content hour_bill_content_{{$item['bill_id']}} mb-1">
  <div class="acm-clearfix">
    <button type="button" onclick="kas_date_check_restaurant_data_hour_bill_item(this, '{{$item['bill_id']}}')"
            class="btn btn-sm btn-outline-primary">
      <span class="acm-fs-16">Bill {{$item['bill_kas_id']}} - (<b>{{count($item['orders'])}}</b> orders)</span>
      @if(!empty($item['bill_note']))
        - <span>{{$item['bill_note']}}</span>
      @endif
    </button>
  </div>

  <div class="acm-clearfix hour_bill_content_data hour_bill_content_data_{{$item['bill_id']}} d-none">
    @foreach($item['orders'] as $order)
      <div class="hour_bill_content_order">
        <div class="acm-ml-px-5 text-primary fw-bold">
          <span>+ Order {{$order['order_kas_id']}}</span>
          @if(!empty($item['order_note']))
            - <span>{{$item['order_note']}}</span>
          @endif
        </div>
        @if(count($order['order_items']))
          @foreach($order['order_items'] as $order_item)
            <div class="acm-ml-px-10 text-dark">
              @if($order_item['food_id'])
                <div class="acm-fs-18">
                  - <b>{{$order_item['quantity']}}</b> <span class="text-danger">{{$order_item['food_name']}}</span>
                  <span class="acm-text-italic">({{$order_item['item_name']}})</span>
                </div>
              @else
                <div class="acm-fs-18">- <b>{{$order_item['quantity']}}</b> {{$order_item['item_name']}}</div>
              @endif
              @if(!empty($item['item_note']))
                <div class="acm-text-italic">({{$item['item_note']}})</div>
              @endif
            </div>
          @endforeach
        @endif
      </div>
    @endforeach
  </div>
</div>
@endforeach
