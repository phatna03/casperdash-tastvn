@if($rfs->get_food())
  <div class="row m-0">
    <div class="col-lg-6 mb-1">
      <div class="text-center w-auto p-1">
        <div class="clearfix position-relative">
          <div class="text-uppercase acm-fs-18 fw-bold">
            <span class="text-dark">photo standard:  <b class="text-danger">{{$rfs->get_food()->name}}</b></span>
          </div>
        </div>
        <img class="w-100" loading="lazy"
             src="{{$rfs->get_food()->get_photo(['restaurant_parent_id' => $rfs->get_restaurant()->restaurant_parent_id])}}"/>
      </div>
    </div>
    <div class="col-lg-6 mb-1">
      <div class="text-center w-auto p-1">
        <div class="clearfix position-relative">
          <div class="text-uppercase acm-fs-18 fw-bold">
            <span class="text-dark">photo sensor id:  <b class="text-danger">{{$rfs->id}}</b></span>
          </div>
        </div>
        <img class="w-100" loading="lazy" src="{{$rfs->get_photo()}}"/>
      </div>
    </div>
  </div>
@else
  <div class="text-center w-auto p-1">
    <div class="clearfix position-relative">
      <div class="text-uppercase acm-fs-18 fw-bold">
        <span class="text-dark">photo sensor id:  <b class="text-danger">{{$rfs->id}}</b></span>
      </div>
    </div>
    <img class="w-100" loading="lazy" src="{{$rfs->get_photo()}}"/>
  </div>
@endif

@if(count($predictions))
  <ul class="cmt-wrapper">
    @if(count($versions))
      @if($model > 0)
        @php
          foreach($versions as $version):
          $version = (array)$version;
        @endphp
        <li class="cmt-itm">
          <div class="d-flex overflow-hidden">
            <span>Dataset: {{$version['dataset'] . '/' . $version['version']}}</span>
          </div>
        </li>
        @endforeach
      @else
        <li class="cmt-itm">
          <div class="d-flex overflow-hidden">
            <span>Dataset: {{$versions['dataset'] . '/' . $versions['version']}}</span>
          </div>
        </li>
      @endif
    @endif
    @php
      $count = 0;
      foreach($predictions as $prediction):
      $count++;
      $confidence = round($prediction['confidence'] * 100);
    @endphp
    <li class="cmt-itm">
      <div class="d-flex overflow-hidden">
        <span class="fw-bold acm-mr-px-5">{{$confidence . '%'}}</span>
        <span>{{$prediction['class']}}</span>
      </div>
    </li>
    @endforeach
  </ul>
@endif
