@php
  $datas = $pageConfigs['datas'];
  if (!count($datas)) {
      return;
  }

  foreach ($pageConfigs['datas'] as $row):
  $restaurant = $row['restaurant'];
  $item = $row['item'];
@endphp
<div style="margin-bottom: 20px;">
<div>{{$restaurant->name . ' - ' . $item->created_at}}</div>
{{--<div>--}}
{{--  @if($item->confidence)--}}
{{--    @if($item->get_food())--}}
{{--      Predicted Dish: <b><span class="acm-mr-px-5 text-danger">{{$item->confidence}}%</span><span--}}
{{--          class="acm-mr-px-5">{{$item->get_food()->name}}</span></b>--}}
{{--    @endif--}}
{{--  @else--}}
{{--    Predicted Dish: <b><span class="acm-mr-px-5">{{$item->get_food()->name}}</span></b>--}}
{{--  @endif--}}
{{--</div>--}}
@php
  $texts = array_filter(explode('&nbsp', $item->missing_texts));
    if(!empty($item->missing_texts) && count($texts)):
@endphp
<div>
  <div>Ingredients Missing:</div>
  @foreach($texts as $text)
    @if(!empty(trim($text)))
      <div>- {{$text}}</div>
    @endif
  @endforeach
</div>
@endif
</div>
@endforeach

<script src="{{ asset(mix('assets/vendor/libs/jquery/jquery.js')) }}"></script>
<script type="text/javascript">

  $(document).ready(function () {
    window.print();
    setTimeout(function () {
      window.close();
    }, 2000);
    return false;
  });
</script>
