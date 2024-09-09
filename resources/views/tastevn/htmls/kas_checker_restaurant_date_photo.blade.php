<div id="swiper-gallery">
  <div class="swiper gallery-top">
    <div class="swiper-wrapper">
      @foreach($items as $item)
        <div class="swiper-slide" style="background-image:url({{$item->get_photo()}})">
          <div class="photo_checker_main">{{$item->id . ' - ' . date('d/m/Y H:i:s', strtotime($item->time_photo))}}</div>
          <a class="acm-lightbox-photo d-none"
             href="{{$item->get_photo()}}?dpr=1&auto=format&fit=crop&w=2000&q=80&cs=tinysrgb"
             title="{{$item->restaurant_name . ' at ' . date('d/m/Y H:i:s', strtotime($item->time_photo))}}"
             data-lcl-txt="{{$item->get_comment($viewer)}}"
             data-lcl-author="{{$item->id}}"
             data-lcl-thumb="{{$item->get_photo()}}?dpr=1&auto=format&fit=crop&w=150&q=80&cs=tinysrgb"
          >
            <div class="photo_checker_main">{{$item->id . ' - ' . date('d/m/Y H:i:s', strtotime($item->time_photo))}}</div>
            <div class="photo_checker_lightbox">
                <i class="mdi mdi-fullscreen fs-2"></i>
            </div>
          </a>
        </div>
      @endforeach
    </div>
    <!-- Add Arrows -->
    <div class="swiper-button-next swiper-button-white"></div>
    <div class="swiper-button-prev swiper-button-white"></div>
  </div>
  <div class="swiper gallery-thumbs">
    <div class="swiper-wrapper">
      @foreach($items as $item)
        <div class="swiper-slide" style="background-image:url({{$item->get_photo()}})">
          <div class="photo_checker_thumb">{{$item->id}}</div>
        </div>
      @endforeach
    </div>
  </div>
</div>
