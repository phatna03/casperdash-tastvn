<div class="row">
  <div class="col-lg-6 mb-2">
    <div class="form-floating form-floating-outline mb-2">
      <div class="form-control">
        <input class="form-check-input" type="checkbox" id="user-update-robot-error"
                name="rbf_error" @if($item->rbf_error) checked @endif />
        <span class="text-dark">Yes, need to retrain</span>
      </div>
      <label for="user-update-robot-error" class="text-danger">Robot Error?</label>
    </div>

    <div class="form-floating form-floating-outline mb-2">
      <textarea class="form-control @if(count($texts)) h-px-150 @else h-px-400 @endif" id="user-update-note" name="update_note">{{$item->note}}</textarea>
      <label for="user-update-note" class="text-danger">Note</label>
    </div>

    <div class="form-floating form-floating-outline mb-2 wrap-texts @if(!count($texts)) d-none @endif">
      <div class="form-control acm-height-px-auto acm-overflow-y-auto acm-height-300-max p-2" id="user-update-text">
        @if(count($texts))
          @foreach($texts as $k => $text)
            <div class="mt-1 position-relative clearfix itm-text">
              <div class="form-check m-0">
                <input class="form-check-input" type="checkbox" id="for-text-{{$k}}"
                       data-itd="{{$text->id}}" name="text_{{$k}}"
                       @if(in_array($text->id, $text_ids)) checked @endif
                />
                <label class="form-check-label" for="for-text-{{$k}}">{{$text->name}}</label>
              </div>
            </div>
          @endforeach
        @endif
      </div>
      <label for="user-update-text" class="text-danger">Text Notes</label>
    </div>
  </div>

  <div class="col-lg-6 mb-2">
    <div class="form-floating form-floating-outline">
      <div class="form-control acm-wrap-selectize" id="user-update-food">
        <select class="ajx_selectize" name="update_food"
                data-value="food"
                data-placeholder="dish name..."
                @if($item->get_food())
                data-chosen="{{$item->get_food()->id}}"
                @endif
        ></select>
      </div>
      <label for="user-update-food" class="text-danger">Select Dish Valid</label>
    </div>

    <div class="mt-4 mb-2 wrap-ingredients @if(!count($ingredients)) d-none @endif">
      @if(count($ingredients))
        @include('tastevn.htmls.item_ingredient_select', ['ingredients' => $ingredients])
      @endif
    </div>
  </div>
</div>
