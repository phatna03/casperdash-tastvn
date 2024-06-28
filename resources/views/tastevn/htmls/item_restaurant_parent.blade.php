<div class="acm-clearfix mb-2 frm_restaurant_foods">
  <div class="row">
    <div class="col-lg-8 mb-1">
      @if($viewer->is_moderator())
        <button type="button" class="btn btn-sm btn-primary p-2 d-inline-block acm-ml-px-5" onclick="restaurant_food_add_prepare('{{$restaurant_parent->id}}')">
          <i class="mdi mdi-plus"></i>
        </button>
      @endif

      <h4 class="text-dark text-uppercase fw-bold d-inline-block">
        List of dishes
        <b class="text-primary acm-ml-px-5 count_foods">({{count($foods)}})</b>
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
            <td class="p-1 text-dark text-center count_foods_1">{{count($foods_group_1)}}</td>
          </tr>
          <tr>
            <td class="p-1 text-dark">Less Training</td>
            <td class="p-1 text-dark text-center count_foods_2">{{count($foods_group_2)}}</td>
          </tr>
          <tr>
            <td class="p-1 text-dark">Not Trained Yet</td>
            <td class="p-1 text-dark text-center count_foods_3">{{count($foods_group_3)}}</td>
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

  <div class="frm_restaurant_foods_data">
    @if(count($foods))
      @php
        $food_id = 0;
        foreach($foods as $food):
        if ($food_id != $food['food_id']) {
            $food_id = $food['food_id'];
        } else {
            continue;
        }

      @endphp

        @include('tastevn.htmls.item_restaurant_parent_food', ['item' => $food, 'restaurant_parent' => $restaurant_parent])

      @endforeach
    @else
      <div class="foods_empty">
        <span class="badge bg-info">No data found</span>
      </div>
    @endif
  </div>
</div>
