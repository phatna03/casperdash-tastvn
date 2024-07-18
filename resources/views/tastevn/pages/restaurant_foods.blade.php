@extends('tastevn/layouts/layoutMaster')

@section('title', 'Admin - Dishes Checker')

@section('content')
  @php
  $restaurants = $pageConfigs['restaurants'];
  $foods = $pageConfigs['foods'];

  @endphp

  <div class="row m-0">
    <div class="col-12 mb-1">
      <h4 class="position-relative w-100 mb-0">
        <div class="acm-float-right">
          <button type="button" class="btn btn-sm btn-info p-1" onclick="toggle_header()">
            <i class="mdi mdi-alert-remove"></i> Toggle Header
          </button>
        </div>

        <span class="text-muted fw-light">Admin /</span> Dishes Checker

      </h4>
    </div>

    <div class="col-12 mb-1">
      <div class="card">
        <div class="card-body p-2">
          <div class="table_fixed_first">
            <table class="table table-bordered">
              <thead>
              <tr>
                <th class="td_fixed">Dishes / Restaurants</th>
                @foreach($restaurants as $restaurant)
                  <th class="td_content td_title_restaurant checker_restaurant_{{$restaurant->id}}">{{$restaurant->name}}</th>
                @endforeach
              </tr>
              </thead>
              <tbody>
              @php
                $stt = 0;
                  foreach($foods as $food):
                $stt++;
              @endphp
              <tr class="tr_food_{{$food->id}}">
                <th class="td_fixed">
                  <div>{{$stt . '. '}}</div>
                  <div>{{$food->name}}</div>
                </th>
                @foreach($restaurants as $restaurant)
                  <td class="td_content tr_restaurant_food_{{$restaurant->id}}_{{$food->id}}"></td>
                @endforeach
              </tr>
              @endforeach
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- modal food photo fake -->
  <div class="modal">
    <div class="frm_upload_photo d-none">
      <form onsubmit="return event.preventDefault();" id="frm_food_photo_standard">
        <input type="file" name="photo"
               onchange="restaurant_food_photo(this)"
               accept=".jpg,.jpeg,.png,.webp"
        />

        <input type="hidden" name="food_id" />
      </form>
    </div>

    <input type="hidden" name="restaurant_parent_id" />
  </div>
  <!-- modal food robot -->
  <div class="modal fade modal-second" id="modal_food_ingredient_robot" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title">Edit Roboflow Ingredients</h4>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form class="pt-0" onsubmit="return restaurant_food_robot(event, this);">
          <div class="modal-body">
            <div class="row">
              <div class="col mb-12 mt-2">
                <div class="wrap-add-item-ingredients">
                  <div class="wrap-ingredients wrap-custom p-1">
                    <div class="ingredient-item-add mb-1 acm-text-right">
                      <button class="btn btn-sm btn-info me-sm-3 me-1" type="button" onclick="ingredient_item_add(this)"><i class="mdi mdi-plus me-0 me-sm-1"></i> Add Ingredient</button>
                    </div>
                  </div>
                  <div class="wrap-ingredients wrap-fetch p-1">

                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <div class="wrap-btns">
              @include('tastevn.htmls.form_button_loading')
              <button type="submit" class="btn btn-primary btn-ok btn-submit acm-float-right">Submit</button>
              <button type="button" class="btn btn-outline-secondary btn-ok btn-cancel" data-bs-dismiss="modal">Cancel</button>
            </div>

            <input type="hidden" name="restaurant_parent_id" />
            <input type="hidden" name="food_id" />
          </div>
        </form>
      </div>
    </div>
  </div>
  <!-- modal food update -->
  <div class="modal fade modal-second" id="modal_food_update" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title">Confirmation?</h4>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="row">
            <div class="col-lg-12 mt-2 mb-2 d-none wrap_updated wrap_live_group">
              <label class="text-dark fw-bold">Roboflow Confidence</label>
              <div class="form-check mt-3">
                <input name="live_group" class="form-check-input live_group live_group_1" type="radio" value="1" id="defaultRadio1" />
                <label class="form-check-label" for="defaultRadio1">
                  Super Confidence
                </label>
              </div>
              <div class="form-check">
                <input name="live_group" class="form-check-input live_group live_group_2" type="radio" value="2" id="defaultRadio2" />
                <label class="form-check-label" for="defaultRadio2">
                  Less Training
                </label>
              </div>
              <div class="form-check">
                <input name="live_group" class="form-check-input live_group live_group_3" type="radio" value="3" id="defaultRadio3" />
                <label class="form-check-label" for="defaultRadio3">
                  Not Trained Yet
                </label>
              </div>
            </div>
            <div class="col-lg-12 mt-2 mb-2 d-none wrap_updated wrap_model_name">
              <label class="text-dark fw-bold">Roboflow Model Name</label>
              <input type="text" class="form-control text-center" name="model_name" />
            </div>
            <div class="col-lg-12 mt-2 mb-2 d-none wrap_updated wrap_model_version">
              <label class="text-dark fw-bold">Roboflow Model Version</label>
              <input type="text" class="form-control text-center" name="model_version" />
            </div>
            <div class="col-lg-12 mt-2 mb-2 d-none wrap_updated wrap_confidence">
              <label class="text-dark fw-bold">Roboflow Food Confidence</label>
              <input type="text" class="form-control text-center fnumber" onblur="input_number_min_30_max_100(this);" name="confidence" />
            </div>
            <div class="col-lg-12 mt-2 mb-2 d-none wrap_updated wrap_category_name">
              <label class="text-dark fw-bold">Category Name</label>
              <input type="text" class="form-control text-center" name="category_name" />
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <div class="wrap-btns">
            @include('tastevn.htmls.form_button_loading')
            <button type="button" class="btn btn-primary btn-ok btn-submit acm-float-right" onclick="restaurant_food_update()">Submit</button>
            <button type="button" class="btn btn-outline-secondary btn-ok btn-cancel" data-bs-dismiss="modal">Cancel</button>
          </div>

          <input type="hidden" name="restaurant_parent_id" />
          <input type="hidden" name="food_id" />
          <input type="hidden" name="type" />
        </div>
      </div>
    </div>
  </div>

@endsection

@section('js_end')
  <script type="text/javascript">
    var $ = jQuery.noConflict();

    $('.page_main_content').removeClass('container-xxl');

    $(document).ready(function() {

      @foreach($restaurants as $restaurant)
        restaurant_food_serve({{$restaurant->id}});
      @endforeach

      toggle_header();
  // restaurant_food_serve(1);

    });
  </script>
@endsection
