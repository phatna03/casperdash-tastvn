<div class="row">
  <div class="col-lg-12 mb-1">
    <div class="acm-border-css p-1 border-dark">
      <div class="row">
        <div class="col-12 mb-1">
          <h6 class="text-uppercase text-center m-0">
            @if($item->get_food())
              <div class="badge bg-primary">{{$item->get_food()->name}}</div>
            @else
              <div class="badge bg-danger">No dish found</div>
            @endif
          </h6>
        </div>
        <div class="col-6 mb-1">
          <div class="text-center w-auto p-1">
            <div class="text-uppercase fw-bold text-dark">photo standard</div>
            <img class="w-100 acm-height-300-max" src="{{$data['food']['photo']}}" />
          </div>
        </div>
        <div class="col-6 mb-1">
          <div class="text-center w-auto p-1">
            <div class="text-uppercase fw-bold text-dark">photo sensor</div>
            <img class="w-100 acm-height-300-max" src="{{$item['photo_url']}}" />
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-lg-4 mb-1">
    <div class="acm-border-css p-1 border-dark @if($item['found_by'] == 'rbf') bg-success-subtle @endif">
      <div class="row">
        <div class="col-12 mb-1 text-center">
          <div class="text-uppercase fw-bold text-dark">roboflow</div>
        </div>
        <div class="col-12 mb-1">
          <div class="text-primary fw-bold">+ Predicted dish:</div>
          <div class="acm-text-line-one">
            @if((int)$data['rbf']['food_id'])
              - <b class="acm-mr-px-5 text-danger">{{$data['rbf']['food_confidence']}}%</b> {{$data['rbf']['food_name']}}
            @else
              ---
            @endif
          </div>
        </div>
        <div class="col-12 mb-1">
          <div class="text-primary fw-bold">+ Ingredients found:</div>
          <div>
            @if(count($data['rbf']['ingredients_found']))
              @foreach($data['rbf']['ingredients_found'] as $ing)
                <div class="acm-text-line-one">
                  - <b class="acm-mr-px-5 text-dark">{{$ing['quantity']}}</b> {{$ing['title']}}
                </div>
              @endforeach
            @else
              ---
            @endif
          </div>
        </div>
        @if($item['found_by'] == 'rbf')
          <div class="col-12 mb-1">
            <div class="text-primary fw-bold">+ Ingredients missing:</div>
            <div>
              @if(count($data['rbf']['ingredients_missing']))
                @foreach($data['rbf']['ingredients_missing'] as $ing)
                  <div class="acm-text-line-one @if($ing['type'] == 'core') text-danger @endif">
                    - <b class="acm-mr-px-5 text-dark">{{$ing['quantity']}}</b>
                    @if(!empty($ing['name_vi']))
                      {{$ing['name'] . ' - ' . $ing['name_vi']}}
                    @else
                      {{$ing['name']}}
                    @endif
                  </div>
                @endforeach
              @else
                ---
              @endif
            </div>
          </div>
        @endif
      </div>
    </div>
  </div>
  <div class="col-lg-4 mb-1">
    <div class="acm-border-css p-1 border-dark @if($item['found_by'] == 'sys') bg-success-subtle @endif">
      <div class="row">
        <div class="col-12 mb-1 text-center">
          <div class="text-uppercase fw-bold text-dark">system</div>
        </div>
        <div class="col-12 mb-1">
          <div class="text-primary fw-bold">+ Predicted dish:</div>
          <div class="acm-text-line-one">
            @if((int)$data['sys']['food_id'])
              - <b class="acm-mr-px-5 text-danger">{{$data['sys']['food_confidence']}}%</b> {{$data['sys']['food_name']}}
            @else
              ---
            @endif
          </div>
        </div>
        @if((int)$data['sys']['food_id'])
          <div class="col-12 mb-1">
            <div class="text-primary fw-bold">+ Ingredients missing:</div>
            <div>
              @if(count($data['sys']['ingredients_missing']))
                @foreach($data['sys']['ingredients_missing'] as $ing)
                  <div class="acm-text-line-one @if($ing['type'] == 'core') text-danger @endif">
                    - <b class="acm-mr-px-5 text-dark">{{$ing['quantity']}}</b>
                    @if(!empty($ing['name_vi']))
                      {{$ing['name'] . ' - ' . $ing['name_vi']}}
                    @else
                      {{$ing['name']}}
                    @endif
                  </div>
                @endforeach
              @else
                ---
              @endif
            </div>
          </div>
          <div class="col-12 mb-1">
            <div class="text-primary fw-bold">+ List of predicted dishes:</div>
            <div>
              @if(count($data['sys']['foods']))
                @php
                foreach($data['sys']['foods'] as $foo):
                $food = App\Models\Food::find($foo['food']);
                @endphp
                  <div class="acm-text-line-one">
                    - <b class="acm-mr-px-5 text-danger">{{$foo['confidence']}}%</b> {{$food->name}}
                  </div>
                @endforeach
              @else
                ---
              @endif
            </div>
          </div>
        @endif
      </div>
    </div>
  </div>
  <div class="col-lg-4 mb-1">
    <div class="acm-border-css p-1 border-dark @if($item['found_by'] == 'usr') bg-success-subtle @endif">
      <form onsubmit="return event.preventDefault();">
        <div class="row">
          <div class="col-12 mb-1 text-center">
            <div class="text-uppercase fw-bold text-dark">user update</div>
          </div>
          <div class="col-12 mb-2 acm-text-right">
            <div class="btn-group">
              <button type="button" class="btn btn-info btn-sm btn-rollback d-none"
                      data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                Rollback
              </button>
              <ul class="dropdown-menu dropdown-menu-end">
                <li><button class="dropdown-item bg-danger text-white" type="button" onclick="restaurant_food_scan_result_rollback(this);">Yes, I confirm the rollback data!</button></li>
              </ul>
            </div>

            <div class="btn-group">
              <button type="button" class="btn btn-primary btn-sm"
                      data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                Submit
              </button>
              <ul class="dropdown-menu dropdown-menu-end">
                <li><button class="dropdown-item bg-danger text-white" type="button" onclick="restaurant_food_scan_result_update(this);">Yes, I confirm the updated data!</button></li>
              </ul>
            </div>
          </div>
          <div class="col-12 mb-2">
            <div class="form-floating form-floating-outline">
              <textarea class="form-control h-px-100" id="user-update-note" name="update_note">{{$item->note}}</textarea>
              <label for="user-update-note">Note</label>
            </div>
          </div>
          <div class="col-12 mb-2">
            <div class="form-floating form-floating-outline">
              <div class="form-control acm-wrap-selectize" id="user-update-food">
                <select class="ajx_selectize" name="update_food"
                        data-value="food"
                        @if($item['found_by'] == 'usr' && (int)$data['usr']['food_id'])
                        data-chosen="{{(int)$data['usr']['food_id']}}"
                        @endif
                        data-placeholder="dish name..."
                ></select>
              </div>
              <label for="user-update-food" class="text-danger fw-bold">Select Dish Valid</label>
            </div>
          </div>
          <div class="col-12 mb-2 wrap-ingredients @if($item['found_by'] == 'usr' && count($data['usr']['ingredients_missing'])) @else d-none @endif">
            @if($item['found_by'] == 'usr' && count($data['usr']['ingredients_missing']))
              @include('tastevn.htmls.item_ingredient_select', ['ingredients' => $data['usr']['ingredients_missing']])
            @endif
          </div>
        </div>
      </form>
    </div>
  </div>
</div>
