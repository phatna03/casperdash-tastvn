<div class="text-dark fw-bold mb-1">+ Recipe Ingredients</div>
@if(count($recipes))
  <div class="acm-clearfix">
  @php
    foreach($recipes as $ite):
  @endphp
    <div class="acm-float-left w-50">
      - <span class="text-dark fs-4 fw-bold">{{$ite->name}}</span>
    </div>
  @endforeach
  </div>
@else
  - <div class="badge bg-info">No data found</div>
@endif
