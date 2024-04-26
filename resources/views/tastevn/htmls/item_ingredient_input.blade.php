@php
$ingredients = isset($ingredients) && count($ingredients) ? $ingredients : [];
@endphp

@if(count($ingredients))
  @foreach($ingredients as $ingredient)
    <div class="food-ingredient-item acm-border-css p-1 m-1"
      data-id="{{$ingredient->id}}"
    >
      <div class="remove-ingredient">
        <button type="button" class="btn btn-sm btn-danger" onclick="ingredient_item_remove(this)">X</button>
      </div>

      <input type="hidden" name="old" value="{{$ingredient->food_ingredient_id}}" />

      <div class="row">
        <div class="col-lg-2 mb-1">
          <div>
            <label class="form-check-label">core</label>
          </div>
          <div class="form-check m-0 p-0">
            <input class="form-check-input m-auto" type="checkbox" name="ing_core"
                   onfocus="ingredient_item_focus(this, 1);"
                   onblur="ingredient_item_focus(this);"
                   @if($ingredient->ingredient_type == 'core') checked="checked" @endif
            />
          </div>
        </div>
        <div class="col-lg-2 mb-1">
          <input type="text" class="form-control text-center fnumber" name="ing_quantity"
                 onfocus="ingredient_item_focus(this, 1);"
                 onblur="ingredient_item_focus(this); input_number_min_one(this);"
                 placeholder="quantity"
                 value="{{$ingredient ? $ingredient->ingredient_quantity : 1}}"
          />
        </div>
        <div class="col-lg-8 mb-1">
          <select class="ajx_selectize" name="ing_name"
                  data-value="ingredient"
                  data-placeholder="ingredient name..."
                  data-chosen="{{$ingredient->id}}"
          ></select>
        </div>
      </div>

      <input type="text" class="form-control text-center d-none" name="ing_color"
             onfocus="ingredient_item_focus(this, 1);"
             onblur="ingredient_item_focus(this);"
             placeholder="#color code"
             value="{{$ingredient && !empty($ingredient->ingredient_color) ? $ingredient->ingredient_color : ''}}"
      />

    </div>
  @endforeach
@else
<div class="food-ingredient-item acm-border-css p-1 m-1">
  <div class="remove-ingredient">
    <button type="button" class="btn btn-sm btn-danger" onclick="ingredient_item_remove(this)">X</button>
  </div>

  <div class="row">
    <div class="col-lg-2 mb-1">
      <div>
        <label class="form-check-label">core</label>
      </div>
      <div class="form-check m-0 p-0">
        <input class="form-check-input m-auto" type="checkbox" name="ing_core"
               onfocus="ingredient_item_focus(this, 1);"
               onblur="ingredient_item_focus(this);"
        />
      </div>
    </div>
    <div class="col-lg-2 mb-1">
      <input type="text" class="form-control text-center fnumber" name="ing_quantity"
             onfocus="ingredient_item_focus(this, 1);"
             onblur="ingredient_item_focus(this); input_number_min_one(this);"
             placeholder="quantity" value="1" />
    </div>
    <div class="col-lg-8 mb-1">
      <select class="ajx_selectize" name="ing_name"
              data-value="ingredient"
              data-placeholder="ingredient name..."
      ></select>
    </div>
  </div>

  <input type="text" class="form-control text-center d-none" name="ing_color"
         onfocus="ingredient_item_focus(this, 1);"
         onblur="ingredient_item_focus(this);"
         placeholder="#color code" />

</div>
@endif
