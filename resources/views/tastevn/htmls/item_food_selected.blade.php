@php
if (!isset($ingredients) || !count($ingredients)) {
    return;
}
foreach($ingredients as $ing):
@endphp
  <div class="mb-2 text-dark">
    <b>{{$ing['ingredient_quantity']}}</b>
    <span>{{$ing['name'] . ' - ' . $ing['name_vi']}}</span>
  </div>
@endforeach
