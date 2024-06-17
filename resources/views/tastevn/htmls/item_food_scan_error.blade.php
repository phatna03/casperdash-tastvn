<div class="row">
  <div class="col-lg-12 mb-1">
    <div class="acm-border-css p-1 border-dark">
      <div class="row">
        <div class="col-12 mb-1">
          <h6 class="text-uppercase text-center m-0">
            <div class="badge bg-primary">{{$food->name}}</div>
          </h6>
        </div>
        <div class="col-6 mb-1">
          <div class="text-center w-auto p-1">
            <div class="text-uppercase fw-bold text-dark">photo standard</div>
            <img class="w-100" loading="lazy"
                 src="{{$food->get_photo(['restaurant_parent_id' => $restaurant->restaurant_parent_id])}}" />
          </div>
        </div>
        <div class="col-6 mb-1">
          <div class="text-center w-auto p-1">
            <div class="text-uppercase fw-bold text-dark">photo sensor error</div>

            <div id="custCarousel" class="carousel slide" data-ride="carousel" align="center">
              <!-- slides -->
              <div class="carousel-inner">
                @php
                  $count = 0;
                  foreach($rows as $row):
                  $count++;
                @endphp
                <div class="carousel-item item-{{$row->id}} @if($count == 1) active @endif">
                  <img loading="lazy" src="{{$row->get_photo()}}" alt="{{$row->get_photo()}}">
                </div>
                @endforeach
              </div>

              <!-- Left right -->
              <a class="carousel-control carousel-control-prev" href="#custCarousel" data-slide="prev">
                <img class="custom-arrow" src="{{url('custom/img/arrow_left.png')}}" />
              </a>
              <a class="carousel-control carousel-control-next" href="#custCarousel" data-slide="next">
                <img class="custom-arrow" src="{{url('custom/img/arrow_right.png')}}" />
              </a>

              <!-- Thumbnails -->
{{--              <ol class="carousel-indicators list-inline">--}}
{{--                @php--}}
{{--                  $count = 0;--}}
{{--                  foreach($rows as $row):--}}
{{--                  $count++;--}}
{{--                @endphp--}}
{{--                <li class="list-inline-item cursor-pointer @if($count == 1) active @endif">--}}
{{--                  <a id="carousel-selector-{{$count - 1}}" class="@if($count == 1) selected @endif" data-slide-to="{{$count - 1}}" data-target="#custCarousel">--}}
{{--                    <img src="{{$row->get_photo()}}" class="img-fluid">--}}
{{--                  </a>--}}
{{--                </li>--}}
{{--                @endforeach--}}
{{--              </ol>--}}
            </div>

          </div>
        </div>
      </div>
    </div>
  </div>
</div>
