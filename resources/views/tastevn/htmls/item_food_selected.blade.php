@php
if (!isset($ingredients) || !count($ingredients)) {
    return;
}
foreach($ingredients as $ing):
@endphp
  <div class="mb-2 @if($ing['ingredient_type'] == 'core') text-danger fw-bold @else text-dark @endif">
    <b>{{$ing['ingredient_quantity']}}</b>
    <span>{{$ing['name'] . ' - ' . $ing['name_vi']}}</span>
  </div>
@endforeach
