<div class="text-primary fw-bold mb-1">+ Recipe Ingredients</div>
@if(count($recipes))
  <div class="acm-clearfix">
  @foreach($recipes as $ite)
    <div class="acm-float-left w-50">
      - <b>{{$ite->ingredient_quantity}}</b> {{$ite->name}}
    </div>
  @endforeach
  </div>
@else
  - <div class="badge bg-info">No data found</div>
@endif
