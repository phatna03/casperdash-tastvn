<div class="text-dark fw-bold mb-1">+ Recipe Ingredients</div>
@if(count($recipes))
  <div class="acm-clearfix">
  @php
    $burger_ingredient_name = 'beef burger or grilled chicken';
    $burger_ingredient_check = false;
    $burger_ingredients = \App\Api\SysRobo::_SYS_BURGER_INGREDIENTS;

    foreach($recipes as $ite):
    if (in_array($ite->id, $burger_ingredients)) {
        $burger_ingredient_check = true;
        continue;
    }
  @endphp
    <div class="acm-float-left w-50">
      - <span class="text-dark fs-4 fw-bold">{{$ite->name}}</span>
    </div>
  @endforeach

    @if($burger_ingredient_check)
      <div class="acm-float-left w-50">
        - <span class="text-dark fs-4 fw-bold">{{$burger_ingredient_name}}</span>
      </div>
    @endif
  </div>
@else
  - <div class="badge bg-info">No data found</div>
@endif
