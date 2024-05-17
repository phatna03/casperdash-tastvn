<div class="row">
  <div class="col-12 mb-1">
    <div class="card">
      <div class="card-header border-bottom">
        <h5 class="card-title">List of dishes</h5>
      </div>

      <div class="card-body">
        @if(count($foods))
          @foreach($foods as $ite)
            <div class="acm-border-css p-1 mb-2 data_food_item data_food_item_{{$ite->food_id}}" data-food_id="{{$ite->food_id}}">
              <div class="row">
                <div class="col-lg-2 mb-1">
                  <div class="text-center w-100">
                    <img class="w-100" src="{{!empty($ite->food_photo) ? $ite->food_photo : url('custom/img/no_photo.png')}}" />
                  </div>
                </div>
                <div class="col-lg-6 mb-1">
                  <div class="@if($isMobi) text-center w-100 @endif">
                    <div class="text-dark">{{$ite->food_name}}</div>
                    <div class="acm-text-italic">{{!empty($ite->food_category_name) ? '(' . $ite->food_category_name . ')' : ''}}</div>
                  </div>
                </div>
                <div class="col-lg-2 mb-1">
                  <select class="opt_selectize w-100" onchange="restaurant_food_live_group(this)">
                    @for($i=1;$i<=3;$i++)
                      <option @if($ite->food_live_group == $i) selected="selected" @endif value="{{$i}}">
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
                <div class="col-lg-2 mb-1">
                  <button type="button" class="btn btn-sm btn-danger w-100" onclick="restaurant_food_remove_prepare(this)">
                    <i class="mdi mdi-trash-can"></i> <span class="acm-ml-px-5">Remove</span>
                  </button>
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
  </div>
</div>
