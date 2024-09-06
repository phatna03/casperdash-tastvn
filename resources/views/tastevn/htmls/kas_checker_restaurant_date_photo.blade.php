<div id="swiper-gallery">
  <div class="swiper gallery-top">
    <div class="swiper-wrapper">
      @foreach($items as $item)
        <div class="swiper-slide" style="background-image:url({{$item->get_photo()}})">
          <div class="photo_checker_main">{{$item->id}}</div>
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
