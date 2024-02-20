@if(count($ingredients))
  <div class="form-floating form-floating-outline">
    <div class="form-control acm-height-px-auto p-1" id="user-update-ingredients">
      @foreach($ingredients as $ingredient)
        <div class="mt-1 mb-1 position-relative clearfix js-item-row"
             data-itd="{{$ingredient->id}}"
             data-ingredient_type="{{$ingredient->ingredient_type}}"
        >
          <div class="acm-float-left acm-mr-px-5">
            <button type="button" class="btn btn-danger btn-sm p-1" onclick="js_item_row_remove(this)">
              <i class="mdi mdi-trash-can"></i>
            </button>
          </div>
          <div class="acm-float-left acm-mr-px-5">
            <input class="form-control fnumber w-px-50 p-1 text-center" value="{{$ingredient->ingredient_quantity}}"
                   onblur="input_number_min_one(this);" name="quantity"
            />
          </div>
          <div
            class="acm-text-line-one acm-fs-14 acm-line-height-1 position-relative acm-top-px-10 @if($ingredient->ingredient_type == 'core') text-danger @endif ">
            @if(!empty($ingredient->name_vi))
              {{$ingredient->name . ' - ' . $ingredient->name_vi}}
            @else
              {{$ingredient->name}}
            @endif
          </div>
        </div>
      @endforeach
    </div>
    <label for="user-update-ingredients" class="text-danger fw-bold">Ingredients missing</label>
  </div>
@endif
