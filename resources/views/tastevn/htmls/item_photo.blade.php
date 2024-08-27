@php
if (!count($items)) {
    return;
}
foreach ($items as $item):
$count_comment = $item->count_comments();
@endphp
<div class="col-md-4 col-lg-3 p-0 item_photo"
     data-itd="{{$item->id}}"
>
  <div class="card p-1 m-1">
    <a class="acm-lightbox-photo position-relative restaurant_food_scan_{{$item->id}} lc_lightbox_photo_{{$item->id}}"
       href="{{$item->get_photo()}}?dpr=1&auto=format&fit=crop&w=2000&q=80&cs=tinysrgb"
       title="{{$item->restaurant_name . ' at ' . date('d/m/Y H:i:s', strtotime($item->time_photo))}}"
       data-lcl-txt="{{$item->get_comment($viewer)}}"
       data-lcl-author="{{$item->id}}"
       data-lcl-thumb="{{$item->get_photo()}}?dpr=1&auto=format&fit=crop&w=150&q=80&cs=tinysrgb"
    >
      <img class="card-img-top" loading="lazy" src="{{$item->get_photo()}}" alt="{{$item->get_photo()}}" />

      @if(!$isMobi && $count_comment)
        <b class="badge bg-danger p-1 position-absolute acm-right-0 acm-bottom-0">{{$count_comment . ' comment(s)'}}</b>
      @endif
    </a>

    <div class="card-body p-1 clearfix position-relative">
      <div class="clearfix mb-2 mt-1 d-none">
        <div>{{$devMode}}</div>
        <div>{{date('Y-m-d', strtotime($item->created_at))}}</div>
      </div>
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

      @if($isMobi)
        <div class="clearfix mt-2">
          <div class="acm-float-right position-relative">
            <button type="button" class="btn btn-sm btn-primary p-1"
                    onclick="mobi_photo_view({{$item->id}})"
            >
              @if($count_comment)
              <b class="badge bg-danger p-1 position-absolute acm-right--5px acm-top-px--10">{{$count_comment}}</b>
              @endif
              <i class="mdi mdi-comment acm-mr-px-5"></i> Comments
            </button>
          </div>
        </div>
      @endif
    </div>
  </div>
</div>
@endforeach
