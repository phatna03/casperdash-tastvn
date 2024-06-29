@php
if (!count($items)) {
    return;
}
foreach ($items as $item):
@endphp
<div class="col-md-4 col-lg-3 p-0 item_photo"
     data-itd="{{$item->id}}"
>
  <div class="card p-1 m-1">
    <a
      @if($isMobi)
         class="restaurant_food_scan_{{$item->id}} lc_lightbox_photo_{{$item->id}}"
         href="javascript:void(0)" onclick="mobi_photo_view({{$item->id}})"
       @else
         class="acm-lightbox-photo restaurant_food_scan_{{$item->id}} lc_lightbox_photo_{{$item->id}}"
         href="{{$item->get_photo()}}?dpr=1&auto=format&fit=crop&w=2000&q=80&cs=tinysrgb"
         title="{{$item->restaurant_name . ' at ' . date('d/m/Y H:i:s', strtotime($item->time_photo))}}"
         data-lcl-txt="{{$item->get_comment($viewer)}}"
         data-lcl-author="{{$item->id}}"
         data-lcl-thumb="{{$item->get_photo()}}?dpr=1&auto=format&fit=crop&w=150&q=80&cs=tinysrgb"
       @endif
    >
      <img class="card-img-top" loading="lazy" src="{{$item->get_photo()}}" alt="{{$item->get_photo()}}" />
    </a>

    <div class="card-body p-1 clearfix position-relative">
      <div class="clearfix mb-2 mt-1">
        <div class="acm-fs-15 fw-bold text-dark">{{$item->restaurant_name}}</div>
      </div>
      <div class="clearfix">
        <div class="acm-float-right">
          <div class="acm-fs-13">{{date('d/m/Y H:i:s', strtotime($item->time_photo))}}</div>
        </div>
        <div class="overflow-hidden">
          <div class="acm-fs-15 fw-bold text-dark">ID: {{$item->id}}</div>
        </div>
      </div>
    </div>
  </div>
</div>
@endforeach
