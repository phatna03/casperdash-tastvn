@php
if (!count($items)) {
    return;
}
foreach ($items as $item):
@endphp
<div class="col-md-4 col-lg-3 p-0 item_photo" data-itd="{{$item->id}}">
  <div class="card p-1 m-1">
    <a class="acm-lightbox-photo" href="{{$item->photo_url}}?dpr=1&auto=format&fit=crop&w=2000&q=80&cs=tinysrgb"
       data-lcl-txt="{{$item->photo_url}}"
       data-lcl-author="{{$item->restaurant_name}}"
       data-lcl-thumb="{{$item->photo_url}}?dpr=1&auto=format&fit=crop&w=150&q=80&cs=tinysrgb"
    >
      <img class="card-img-top" src="{{$item->photo_url}}" alt="{{$item->photo_url}}" />
    </a>
    <div class="card-body p-1 clearfix position-relative">
      <div class="acm-float-right">
        <small>{{date('d/m/Y H:i:s', strtotime($item->time_photo))}}</small>
      </div>
      <h6 class="card-title m-0 mt-1 overflow-hidden">{{$item->restaurant_name}}</h6>
    </div>
  </div>
</div>
@endforeach
