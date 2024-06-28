@if(count($items))
  @foreach($items as $ingredient)
    <div class="acm-clearfix acm-height-30-min">
      <div class="acm-float-left acm-mr-px-5">
        @if($viewer->is_admin())
          <select class="form-control p-1 acm-width-50-max"
                  onchange="food_ingredient_confidence_quick(this, {{$ingredient->food_ingredient_id}})"
          >
            @for($i=95; $i>=30; $i--)
              @if($i%5 == 0)
                <option value="{{$i}}" @if($i == $ingredient->confidence) selected="selected" @endif>{{$i . '%'}}</option>
              @endif
            @endfor
          </select>
        @else
          <div class="badge bg-secondary p-1">{{$ingredient->confidence . '%'}}</div>
        @endif
      </div>
      <div class="wrap_text_roboflow_ingredient overflow-hidden acm-height-30-min acm-line-height-30 acm-fs-18 @if($viewer->is_super_admin()) cursor-pointer @endif @if($ingredient->ingredient_type == 'core') cored text-danger @else text-dark @endif"
           @if($viewer->is_super_admin()) onclick="food_ingredient_core_quick(this, {{$ingredient->food_ingredient_id}})" @endif
      >
        - <b>{{$ingredient->ingredient_quantity}}</b> {{$ingredient->name}}
      </div>
    </div>
  @endforeach
@else
  <div>---</div>
@endif
