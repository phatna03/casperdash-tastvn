<div class="text-dark fw-bold mb-1">+ Recipe Ingredients</div>
@if(count($recipes))
  <div class="acm-clearfix">
  @foreach($recipes as $ite)
    <div class="acm-float-left w-50">
      - <span class="text-dark fs-5">{{$ite->name}}</span>
    </div>
  @endforeach
  </div>
@else
  - <div class="badge bg-info">No data found</div>
@endif
