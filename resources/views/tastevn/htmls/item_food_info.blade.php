@php
  $img_src = !empty($item->photo) ? url('') . $item->photo : url('custom/img/food_photo.jpg');
@endphp

<div class="row">
  <div class="col-lg-6 mb-2 text-center">
    <div class="w-auto">
      <img class="w-100" src="{{$img_src}}" />
    </div>
  </div>
  <div class="col-lg-6 mb-2">
    <div class="row">
      <div class="col-lg-12 mb-2">
        <div class="text-primary fw-bold">+ Ingredients</div>
        @foreach($ingredients as $ingredient)
          <div class="acm-ml-px-5 @if($ingredient->ingredient_type == 'core') fw-bold text-danger @endif">
            - <b class="fnumber">{{$ingredient->ingredient_quantity}}</b>
            <span>{{$ingredient->name}}</span>
          </div>
        @endforeach
      </div>

      @if(count($restaurants))
      <div class="col-lg-12 mb-2">
        <div class="text-primary fw-bold">+ Restaurants</div>
        @foreach($restaurants as $restaurant)
          <div class="acm-ml-px-5">
            - <span>{{$restaurant->name}}</span>
          </div>
        @endforeach
      </div>
      @endif
    </div>
  </div>
</div>
