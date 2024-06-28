@if(count($items))
  @foreach($items as $recipe)
    <div class="text-dark acm-fs-18">- <b>{{$recipe->ingredient_quantity}}</b> {{$recipe->name}}</div>
  @endforeach
@else
  <div>---</div>
@endif
