<div class="acm-clearfix mb-2">
  <div class="row">
    <div class="col-lg-8 mb-1">
      @if($viewer->is_moderator())
        <button type="button" class="btn btn-sm btn-primary p-2 d-inline-block acm-ml-px-5" onclick="restaurant_food_add_prepare()">
          <i class="mdi mdi-plus"></i>
        </button>
      @endif

      <h4 class="text-dark text-uppercase fw-bold d-inline-block">
        List of dishes
        <b class="text-primary acm-ml-px-5">({{count($foods)}})</b>
      </h4>
    </div>

    <div class="col-lg-4 mb-1">
      <div class="table-responsive">
        <table class="table table-bordered table-hover">
          <thead>
          <tr>
            <th colspan="2" class="p-1 text-dark text-center">Roboflow Confidence</th>
          </tr>
          </thead>
          <tbody>
          <tr>
            <td class="p-1 text-dark">Super Confidence</td>
            <td class="p-1 text-dark text-center"></td>
          </tr>
          <tr>
            <td class="p-1 text-dark">Less Training</td>
            <td class="p-1 text-dark text-center"></td>
          </tr>
          <tr>
            <td class="p-1 text-dark">Not Trained Yet</td>
            <td class="p-1 text-dark text-center"></td>
          </tr>
          <tr>
            <td class="p-1 text-dark">Total Dishes</td>
            <td class="p-1 text-dark text-center fw-bold">{{count($foods)}}</td>
          </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <div class="frm_upload_photo d-none">
    <form onsubmit="return event.preventDefault();" id="frm_food_photo_standard">
      <input type="file" name="photo"
             onchange="restaurant_food_photo(this)"
             accept=".jpg,.jpeg,.png,.webp"
      />

      <input type="hidden" name="food_id" />
    </form>
  </div>

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
      $food_photo = $food->get_photo([
        'restaurant_parent_id' => $restaurant_parent->id
      ]);

      $recipes = $food->get_recipes([
        'restaurant_parent_id' => $restaurant_parent->id
      ]);

      $ingredients = $food->get_ingredients([
        'restaurant_parent_id' => $restaurant_parent->id
      ]);

      $count++;

      $food_group = 'Super Confidence';
      if ($obj->food_live_group == 2) {
          $food_group = 'Less Training';
      } elseif ($obj->food_live_group == 3) {
          $food_group = 'Not Trained Yet';
      }

    @endphp
    <div class="acm-border-css border-dark @if($count%2) bg-warning-subtle @endif p-3 mb-2 data_food_item data_food_item_{{$food->id}}"
         data-food_id="{{$food->id}}"
         data-restaurant_parent_id="{{$restaurant_parent->id}}"
         data-live_group="{{$obj->food_live_group}}"
         data-model_name="{{$obj->food_model_name}}"
         data-model_version="{{$obj->food_model_version}}"
    >
      <div class="row">
        <div class="col-lg-4 mb-1">
          <div class="text-dark fw-bold fs-4">{{$food->name}}</div>
          <div class="text-dark acm-fs-18 acm-text-italic mb-2">{{$obj->food_category_id ? '(' . $obj->food_category_name . ')' : ''}}</div>
          <div class="text-center w-100 wrap_food_photo_standard">
            <button type="button" class="btn btn-danger p-1 position-absolute acm-right-5px @if($isMobi) d-block @endif"
                    onclick="restaurant_food_photo_prepare(this)"
            >
              <i class="mdi mdi-upload"></i> Upload Photo
            </button>
            <img class="w-100 food_photo_standard" id="food_photo_standard_{{$restaurant_parent->id}}_{{$food->id}}"
                 title="{{$food_photo}}" loading="lazy" src="{{$food_photo}}?v={{time()}}"/>
          </div>
        </div>
        <div class="col-lg-3 mb-1">
          <div class="acm-clearfix">
            @if($viewer->is_moderator())
            <button type="button" class="btn btn-sm btn-info p-1 d-inline-block">
              <i class="mdi mdi-pencil"></i>
            </button>
            @endif
            <div class="text-primary fw-bold acm-fs-18 d-inline-block">Recipe Ingredients</div>
          </div>
          @if(count($recipes))
            @foreach($recipes as $recipe)
              <div class="text-dark acm-fs-18">- {{$recipe->name}}</div>
            @endforeach
          @else
            <div>---</div>
          @endif
        </div>
        <div class="col-lg-3 mb-1">
          <div class="acm-clearfix">
            @if($viewer->is_moderator())
              <button type="button" class="btn btn-sm btn-info p-1 d-inline-block">
                <i class="mdi mdi-pencil"></i>
              </button>
            @endif
            <div class="text-primary fw-bold acm-fs-18 d-inline-block">Roboflow Ingredients</div>
          </div>
          @if(count($ingredients))
            @foreach($ingredients as $ingredient)
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
        </div>
        <div class="col-lg-2 mb-1 btn_inputs">
          @if($viewer->is_admin())
            <button type="button" class="btn btn-sm btn-danger w-100 mb-4"
                    onclick="restaurant_food_remove_prepare(this)">
              <i class="mdi mdi-trash-can"></i> <span class="acm-ml-px-5">Remove</span>
            </button>
          @endif

          <div class="form-floating form-floating-outline mb-3 position-relative">
            @if($viewer->is_moderator())
              <button type="button" class="btn btn-sm btn-info p-1 position-absolute acm-right-0"
                      onclick="restaurant_food_update_prepare(this, 'live_group')">
                <i class="mdi mdi-pencil"></i>
              </button>
            @endif
            <input type="text" class="form-control" id="robo-group-{{$food->id}}" name="live_group"
                   disabled value="{{$food_group}}" />
            <label class="text-dark fw-bold" for="robo-group-{{$food->id}}">Roboflow Confidence</label>
          </div>

          <div class="form-floating form-floating-outline mb-3 position-relative">
            @if($viewer->is_super_admin())
              <button type="button" class="btn btn-sm btn-info p-1 position-absolute acm-right-0"
                      onclick="restaurant_food_update_prepare(this, 'model_name')">
                <i class="mdi mdi-pencil"></i>
              </button>
            @endif
            <input type="text" class="form-control" id="robo-model-{{$food->id}}" name="model_name"
                   disabled value="{{$obj->food_model_name}}" />
            <label class="text-dark fw-bold" for="robo-model-{{$food->id}}">Roboflow Model Name</label>
          </div>

          <div class="form-floating form-floating-outline mb-3 position-relative">
            @if($viewer->is_super_admin())
              <button type="button" class="btn btn-sm btn-info p-1 position-absolute acm-right-0"
                      onclick="restaurant_food_update_prepare(this, 'model_version')">
                <i class="mdi mdi-pencil"></i>
              </button>
            @endif
            <input type="text" class="form-control" id="robo-version-{{$food->id}}" name="model_version"
                   disabled value="{{$obj->food_model_version}}" />
            <label class="text-dark fw-bold" for="robo-version-{{$food->id}}">Roboflow Model Version</label>
          </div>
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
