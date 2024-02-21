@php
if (!count($items)) {
    return;
}
foreach ($items as $item):
@endphp
<div class="col-md-4 col-lg-3 p-0">
  <div class="card p-1 m-1">
    <img class="card-img-top" src="{{$item->photo_url}}" alt="{{$item->photo_url}}" />
    <div class="card-body p-1 clearfix position-relative">
      <div class="acm-float-right">
        <small>{{date('d/m/Y H:i:s', strtotime($item->time_photo))}}</small>
      </div>
      <h6 class="card-title m-0 overflow-hidden">{{$item->restaurant_name}}</h6>
    </div>
  </div>
</div>
@endforeach
