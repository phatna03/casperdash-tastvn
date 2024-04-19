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
<div
  class="acm-itm-notify itm_notify_{{$notification->data['restaurant_food_scan_id']}} position-relative m-1 p-1 @if(!empty($notification->read_at)) @else bg-primary-subtle @endif "
  onclick="notification_read(this); restaurant_food_scan_result_info({{$notification->data['restaurant_food_scan_id']}})"
  data-itd="{{$notification->id}}"
  data-rfs-id="{{$notification->data['restaurant_food_scan_id']}}"
>
  <div class="acm-float-right">
    <small>{{date('d/m/Y H:i:s', strtotime($notification->created_at))}}</small>
  </div>
  <div class="overflow-hidden position-relative">
    <div class="notify_img acm-float-left w-px-50 h-px-50" style="margin-right: 10px;">
      <img class="w-100 h-100" style="border-radius: 50%;" src="{{$item->photo_url}}"/>
    </div>
    <div class="notify_body acm-float-left" style="margin-right: 10px;">
      <h6 class="mb-1 text-primary fw-bold">{{$item->get_restaurant()->name}}</h6>

      @if($notification->type == 'App\Notifications\PhotoComment')
        @php
          $type = $notification->data['typed'];

          $owner = $api_core->get_item($notification->data['owner_id'], 'user');
          $comment = $api_core->get_item($notification->data['comment_id'], 'comment');

          $text1 = 'added new note for the photo with ID: ';
          if ($type == 'photo_comment_edit') {
              $text1 = 'updated their note for the photo with ID: ';
          }
        @endphp
        <div class="text-dark">
          <b><span class="acm-mr-px-5">{{$owner->name}}</span></b> {{$text1}} <b><span class="acm-ml-px-5">{{$item->id}}</span></b>
        </div>
        <div class="text-dark">
            <?php echo nl2br($comment->content);?>
        </div>
      @else
        <div class="text-dark">
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
        <div class="text-dark">
          <div>Ingredients Missing:</div>
          @foreach($texts as $text)
            @if(!empty(trim($text)))
              <div>- {{$text}}</div>
            @endif
          @endforeach
        </div>
        @endif
      @endif
    </div>
  </div>
</div>
@endforeach
