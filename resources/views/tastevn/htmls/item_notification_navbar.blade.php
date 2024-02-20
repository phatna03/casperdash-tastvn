@php
  if (!isset($notifications) || !count($notifications)) {
      return;
  }

    foreach($notifications as $notification):
    $item = App\Models\RestaurantFoodScan::find($notification->data['restaurant_food_scan_id']);
    if (!$item || empty($item->photo_url)) {
        continue;
    }
@endphp
<li class="list-group-item list-group-item-action dropdown-notifications-item cursor-pointer p-1 @if(!empty($notification->read_at)) @else bg-primary-subtle @endif "
    onclick="notification_read(this); restaurant_food_scan_result_info({{$notification->data['restaurant_food_scan_id']}})"
    data-itd="{{$notification->id}}"
>
  <div class="d-flex gap-2">
    <div class="flex-shrink-0">
      <div class="text-center w-px-50 h-px-50 me-1">
        <img class="w-100 h-100" style="border-radius: 50%;" src="{{$item->photo_url}}" />
      </div>
    </div>
    <div class="d-flex flex-column flex-grow-1 overflow-hidden w-px-200">
      <h6 class="mb-1 text-primary fw-bold acm-fs-13">{{$item->get_restaurant()->name}}</h6>
      <div class="text-dark acm-fs-13">
        @if($item->confidence)
          @if($item->get_food())
            Predicted Dish: <b><span class="acm-mr-px-5 text-danger">{{$item->confidence}}%</span><span
                class="acm-mr-px-5">{{$item->get_food()->name}}</span></b>
          @endif
        @else
          Predicted Dish: <b><span class="acm-mr-px-5">{{$item->get_food()->name}}</span></b>
        @endif
      </div>
      @php
        $texts = array_filter(explode('&nbsp', $item->missing_texts));
          if(!empty($item->missing_texts) && count($texts)):
      @endphp
      <div class="text-dark acm-fs-13">
        <div>Missing Ingredients:</div>
        @foreach($texts as $text)
          @if(!empty(trim($text)))
            <div>- {{$text}}</div>
          @endif
        @endforeach
      </div>
      @endif
    </div>
  </div>
  <div class="acm-text-right acm-fs-13">
    <small class="text-dark">{{date('d/m/Y H:i:s', strtotime($notification->created_at))}}</small>
  </div>
</li>
@endforeach
