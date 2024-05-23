@php
  if (!isset($notifications) || !count($notifications)) {
      return;
  }

  $type1s = ['App\Notifications\PhotoComment'];
  $type2s = ['App\Notifications\IngredientMissing'];

    foreach($notifications as $notification):
    $rfs = $api_core->get_item($notification->restaurant_food_scan_id, 'restaurant_food_scan');
    if (!$rfs) {
        continue;
    }
@endphp
<div
  class="acm-itm-notify itm_notify_{{$rfs->id}} position-relative m-1 p-1 @if(!empty($notification->read_at)) @else bg-primary-subtle @endif "
  onclick="notification_read(this); sensor_food_scan_info({{$rfs->id}})"
  data-itd="{{$notification->id}}"
  data-rfs-id="{{$rfs->id}}"
>
  <div class="acm-float-right">
    <small>{{date('d/m/Y H:i:s', strtotime($notification->created_at))}}</small>
  </div>
  <div class="overflow-hidden position-relative">
    <div class="notify_img acm-float-left w-px-50 h-px-50" style="margin-right: 10px;">
      <img class="w-100 h-100" style="border-radius: 50%;" src="{{$rfs->get_photo()}}"/>
    </div>
    <div class="notify_body acm-float-left" style="margin-right: 10px;">
      <h6 class="mb-1 text-primary fw-bold">{{$rfs->get_restaurant()->name}}</h6>

      @if(in_array($notification->type, $type1s))
        @php
          $type = $notification->data['typed'];

          $owner = $api_core->get_item($notification->data['owner_id'], 'user');
          $comment = $api_core->get_item($notification->object_id, $notification->object_type);

          $text1 = 'added new note for the photo with ID: ';
          if ($type == 'photo_comment_edit') {
              $text1 = 'updated their note for the photo with ID: ';
          }
        @endphp
        <div class="text-dark">
          <b><span class="acm-mr-px-5">{{$owner->name}}</span></b> {{$text1}} <b><span
              class="acm-ml-px-5">{{$rfs->id}}</span></b>
        </div>
        <div class="text-dark">
            <?php echo nl2br($comment->content); ?>
        </div>
      @elseif(in_array($notification->type, $type2s))
        <div class="text-dark">
          @if($rfs->confidence)
            @if($rfs->get_food())
              Predicted Dish: <b><span class="acm-mr-px-5 text-danger">{{$rfs->confidence}}%</span><span
                  class="acm-mr-px-5">{{$rfs->get_food()->name}}</span></b>
            @endif
          @else
            Predicted Dish: <b><span class="acm-mr-px-5">{{$rfs->get_food()->name}}</span></b>
          @endif
        </div>
        @php
          $texts = array_filter(explode('&nbsp', $rfs->missing_texts));
            if(!empty($rfs->missing_texts) && count($texts)):
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
