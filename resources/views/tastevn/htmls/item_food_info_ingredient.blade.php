@php
if (!isset($ingredients) || !count($ingredients)) {
    return;
}

foreach ($ingredients as $ingredient):
@endphp
<div class="wrap_text_roboflow_ingredient @if($viewer->is_admin()) cursor-pointer @endif @if($ingredient->ingredient_type == 'core') cored text-danger @else text-dark @endif"
     @if($viewer->is_admin()) onclick="food_ingredient_core_quick(this, {{$ingredient->food_ingredient_id}})" @endif
>
  - <b>{{$ingredient->ingredient_quantity}}</b> {{$ingredient->name}}
</div>
@endforeach
