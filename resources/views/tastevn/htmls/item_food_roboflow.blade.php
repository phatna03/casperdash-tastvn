<div class="row">
  <div class="col-lg-6">
    <div class="text-primary fw-bold mb-1">+ Recipe Ingredients</div>
    @if(count($recipes))
      @foreach($recipes as $ite)
        <div>
          - <b>{{$ite->ingredient_quantity}}</b> {{$ite->name}}
        </div>
      @endforeach
    @endif
  </div>

  <div class="col-lg-6">
    <div class="text-primary fw-bold mb-1">+ Roboflow Ingredients</div>
    @if(count($ingredients))
      @foreach($ingredients as $ite)
        <div class="@if($ite->ingredient_type == 'core') acm-highlight @endif">
          - <b>{{$ite->ingredient_quantity}}</b> {{$ite->name}}
        </div>
      @endforeach
    @endif
  </div>
</div>
