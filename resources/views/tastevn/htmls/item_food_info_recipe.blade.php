@php
if (!isset($ingredients) || !count($ingredients)) {
    return;
}

foreach ($ingredients as $ingredient):
@endphp
<div class="acm-ml-px-5">
  - <b class="fnumber">{{$ingredient->ingredient_quantity}}</b>
  <span>{{$ingredient->name}}</span>
</div>
@endforeach
