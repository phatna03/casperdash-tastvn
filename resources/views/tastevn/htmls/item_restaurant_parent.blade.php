<div class="row">
  <div class="col-12 mb-1">
    <h4 class="text-dark text-uppercase fw-bold">List of dishes <b class="text-primary acm-ml-px-5">({{count($foods)}})</b></h4>

    @if(count($foods))
      @php
        $food_id = 0;
        $count = 0;

        foreach($foods as $obj):
        if ($food_id != $obj->food_id) {
            $food_id = $obj->food_id;
        } else {
            continue;
        }

        $food = $sys_app->get_item($obj->food_id, 'food');

        $recipes = $food->get_recipes([
          'restaurant_parent_id' => $restaurant_parent->id
        ]);

        $ingredients = $food->get_ingredients([
          'restaurant_parent_id' => $restaurant_parent->id
        ]);

        $count++;
      @endphp
      <div class="acm-border-css border-dark @if($count%2) bg-warning-subtle @endif p-3 mb-2 data_food_item data_food_item_{{$food->id}}"
           data-food_id="{{$food->id}}">
        <div class="row">
          <div class="col-lg-6 mb-1">
            <div class="text-dark fw-bold fs-4">{{$food->name}}</div>
            <div
              class="text-dark acm-text-italic mt-1">{{$obj->food_category_id ? '(' . $obj->food_category_name . ')' : ''}}</div>
          </div>

          <div class="col-lg-3 mb-1">
            <select class="opt_selectize w-100" onchange="restaurant_food_live_group(this)">
              @for($i=1;$i<=3;$i++)
                <option @if($obj->food_live_group == $i) selected="selected" @endif value="{{$i}}">
                  @if($i==1)
                    {{$i}}. Super Confidence
                  @elseif($i==2)
                    {{$i}}. Less Training
                  @elseif($i==3)
                    {{$i}}. Not Trained Yet
                  @endif
                </option>
              @endfor
            </select>
          </div>

          <div class="col-lg-3 mb-1">
            <button type="button" class="btn btn-sm btn-danger w-100"
                    onclick="restaurant_food_remove_prepare(this)">
              <i class="mdi mdi-trash-can"></i> <span class="acm-ml-px-5">Remove</span>
            </button>
          </div>

          <div class="col-lg-6 mb-1">
            <div class="text-center w-100">
              <img class="w-100" loading="lazy" src="{{$obj->food_photo}}"/>
            </div>
          </div>

          <div class="col-lg-3 mb-1">
            <div class="text-primary fw-bold">+ Recipe Ingredients</div>
            @if(count($recipes))
              @foreach($recipes as $recipe)
                <div class="text-dark">- {{$recipe->name}}</div>
              @endforeach
            @else
              <div>---</div>
            @endif
          </div>

          <div class="col-lg-3 mb-1">
            <div class="text-primary fw-bold">+ Roboflow Ingredients</div>
            @if(count($ingredients))
              @foreach($ingredients as $ingredient)
                <div class="wrap_text_roboflow_ingredient @if($ingredient->ingredient_type == 'core') cored text-danger @else text-dark @endif"
                     @if($viewer->id == 5 || $viewer->is_super_admin()) onclick="food_ingredient_core_quick(this, {{$ingredient->food_ingredient_id}})" @endif
                >
                  - <b>{{$ingredient->ingredient_quantity}}</b> {{$ingredient->name}}
                </div>
              @endforeach
            @else
              <div>---</div>
            @endif
          </div>
        </div>
      </div>
      @endforeach
    @else
      <div>
        <span class="badge bg-info">No data found</span>
      </div>
    @endif
  </div>
</div>
