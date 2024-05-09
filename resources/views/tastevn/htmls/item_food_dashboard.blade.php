<div class="text-primary fw-bold mb-1">+ Recipe Ingredients</div>
@if(count($recipes))
  @foreach($recipes as $ite)
    <div>
      - <b>{{$ite->ingredient_quantity}}</b> {{$ite->name}}
    </div>
  @endforeach
@else
  - <div class="badge bg-info">No data found</div>
@endif
