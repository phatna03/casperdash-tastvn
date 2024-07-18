@extends('tastevn/layouts/layoutMaster')

@section('title', 'Admin - Report: ' . $pageConfigs['item']->name)

@section('content')

  <h4 class="mb-2"><span class="text-muted fw-light">Admin /</span> Report: {{$pageConfigs['item']->name}}</h4>

  <h4 class="mb-2">
    <div class="acm-float-right acm-ml-px-5">
      <span class="text-uppercase text-dark acm-fs-13 fw-bold">total photos: </span><span class="badge bg-secondary acm-fs-15">{{$pageConfigs['item']->total_photos}}</span>
    </div>

    <div class="overflow-hidden">
      <span class="badge bg-primary">{{$pageConfigs['item']->get_restaurant_parent()->name}}</span>
      <span class="badge bg-danger">{{date('d/m/Y H:i:s', strtotime($pageConfigs['item']->date_from)) . ' -> ' . date('d/m/Y H:i:s', strtotime($pageConfigs['item']->date_to))}}</span>
    </div>
  </h4>

  <div class="card">
    <div class="card-header border-bottom acm-clearfix">
      <div class="acm-float-right acm-ml-px-5">
        <div>
          <span class="text-uppercase text-dark acm-fs-13 fw-bold">Robot not found dishes: </span>
          <span class="badge bg-warning acm-fs-15 cursor-pointer" id="not_found_dishes"
            onclick="report_photo_nf()"
          ></span>
        </div>
      </div>

      <h5 class="card-title m-0 text-uppercase overflow-hidden">Report Information</h5>
    </div>
    <div class="card-body" id="wrap-datas">

    </div>
  </div>

  <!-- modal not found -->
  <div class="modal animate__animated animate__zoomIn" id="modal_report_nf" aria-hidden="true">
    <div class="modal-dialog modal-xl acm-modal-xxl" role="document">
      <div class="modal-content">
        <div class="modal-header acm-header-custom">
          <div class="mid_content acm-fs-18">
            <input type="text" class="form-control text-center d-inline-block acm-fs-18 w-px-75 text-dark fw-bold view_current"
              name="popup_view_input" onblur="input_number_min_one(this); setTimeout(function () { report_photo_nf_action(2); }, 333);" />
            <span class="acm-mr-px-5 acm-ml-px-5">/</span>
            <span class="text-dark fw-bold view_all_count">0</span>
          </div>

          <h4 class="modal-title text-danger fw-bold left_content">Review photos</h4>
          <button type="button" class="btn-close right_content" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body p-1">
          <div class="row">
            <div class="col-lg-9 mb-1 position-relative wrap_report @if(!$isMobi) acm-pr-px-0 @endif">
              <div class="p-1 sensor-wrapper wrap_datas">

              </div>
            </div>

            <div class="col-lg-3 mb-1 position-relative wrap_infos @if(!$isMobi) acm-pl-px-0 @endif">
              <form class="p-1" onsubmit="return event.preventDefault();">
                <div class="row">
                  <div class="col-lg-12 mb-2 acm-text-right">
                    <div class="wrap-btns">
                      @include('tastevn.htmls.form_button_loading')
                      <button type="button" class="btn btn-danger btn-ok btn-submit d-none" onclick="report_photo_nf_clear_prepare()">Clear Result</button>
                      <button type="button" class="btn btn-primary btn-ok btn-submit" onclick="report_photo_nf_update_prepare()">Submit</button>
                    </div>

                    <input type="hidden" name="rfs" />
                    <input type="hidden" name="item" value="{{$pageConfigs['item']->id}}" />
                  </div>
                  <div class="col-lg-12 mb-2">
                    <div class="form-floating form-floating-outline">
                      <div class="form-control acm-wrap-selectize pb-0" id="failed-update-food">
                        <select class="ajx_selectize" name="food"
                                data-value="restaurant_food"
                                data-restaurant="{{$pageConfigs['item']->restaurant_parent_id}}"
                                data-placeholder="dish name..."
                                onchange="report_photo_nf_food_select();"
                        ></select>
                      </div>
                      <label for="failed-update-food" class="text-danger">Select Dish Valid</label>
                    </div>
                  </div>
                  <div class="col-lg-4 mb-2 acm-pr-px-0">
                    <div class="form-floating form-floating-outline mb-2">
                      <div class="form-control">
                        <input class="form-check-input" type="checkbox" id="failed-update-robot-error"
                               name="rbf_error" />
                        <span class="text-dark">Yes</span>
                      </div>
                      <label for="failed-update-robot-error" class="text-danger">Robot Error?</label>
                    </div>
                  </div>
                  <div class="col-lg-4 mb-2 acm-pr-px-0">
                    <div class="form-floating form-floating-outline">
                      <div class="form-control acm-wrap-selectize" id="failed-update-missing">
                        <div class="form-control text-center pb-0 pt-0 border-0">
                          <input class="form-check-input m-auto" type="checkbox" name="missing"
                                 onchange="report_photo_nf_ingredient_missing();"
                          />
                          <span class="text-dark">Yes</span>
                        </div>
                      </div>
                      <label for="failed-update-missing" class="text-danger">Missing?</label>
                    </div>
                  </div>
                  <div class="col-lg-4 mb-2">
                    <div class="form-floating form-floating-outline">
                      <div class="form-control acm-wrap-selectize" id="failed-update-point">
                        <select class="form-control text-center pb-0 pt-0 border-0" name="point"
                                placeholder="points achieved..."
                        >
                          @php
                            for($p=0; $p < 1; ):

                            if ($p > 1) {
                                break;
                            }
                          @endphp
                          <option value="{{$p > 0 ? $p : ''}}">{{$p}}</option>
                          @php
                            $p += 0.1;
                            endfor;
                          @endphp
                        </select>
                      </div>
                      <label for="failed-update-point" class="text-danger">Point?</label>
                    </div>
                  </div>
                  <div class="col-lg-12 mb-2 d-none wrap_ingredients_missing">
                    <div class="form-floating form-floating-outline">
                      <div class="form-control acm-wrap-selectize datas" id="failed-update-ingredients">

                      </div>
                      <label for="failed-update-ingredients" class="text-danger">Ingredients Missing</label>
                    </div>
                  </div>
                  <div class="col-lg-4 mb-2 acm-pr-px-0">
                    <div class="form-floating form-floating-outline mb-2">
                      <div class="form-control">
                        <input class="form-check-input" type="checkbox" id="failed-update-customer_requested"
                               name="customer_requested" />
                        <span class="text-dark">Yes</span>
                      </div>
                      <label for="failed-update-customer_requested" class="text-danger">Requested?</label>
                    </div>
                  </div>
                  <div class="col-lg-4 mb-2 acm-pr-px-0">
                    <div class="form-floating form-floating-outline mb-2">
                      <div class="form-control">
                        <input class="form-check-input" type="checkbox" id="failed-update-food_multi"
                               onchange="sensor_food_scan_update_food_multi(this)"
                               name="food_multi" />
                        <span class="text-dark">Yes</span>
                      </div>
                      <label for="failed-update-food_multi" class="text-danger">Multiple Dishes?</label>
                    </div>
                  </div>
                  <div class="col-lg-4 mb-2">
                    <div class="form-floating form-floating-outline mb-2">
                      <div class="form-control">
                        <input class="form-check-input" type="checkbox" id="failed-update-note_kitchen"
                               name="note_kitchen" />
                        <span class="text-dark">Yes</span>
                      </div>
                      <label for="failed-update-note_kitchen" class="text-danger">Note Kitchen?</label>
                    </div>
                  </div>
                  <div class="col-lg-12 mb-2 d-none food_count">
                    <div class="form-floating form-floating-outline">
                      <input type="text" class="form-control text-dark text-center fw-bold fnumber" name="food_count" autocomplete="off"
                             placeholder="Number of dishes" onblur="input_number_min_two(this);"  />
                    </div>
                  </div>
                  <div class="col-lg-12 mb-2">
                    <div class="form-floating form-floating-outline">
                      <div class="form-control acm-wrap-selectize" id="failed-update-note">
                        <textarea name="note" class="form-control h-px-100 p-0 border-0" placeholder="take note..."></textarea>
                      </div>
                      <label for="failed-update-note" class="text-danger">Main Note</label>
                    </div>
                  </div>
                  <div class="col-lg-12 mb-2">
                    <div class="form-floating form-floating-outline wrap-texts @if(!count($pageConfigs['texts'])) d-none @endif">
                      <div class="form-control acm-height-px-auto acm-overflow-y-auto acm-height-300-max p-2" id="user-update-text">
                        @if(count($pageConfigs['texts']))
                          @foreach($pageConfigs['texts'] as $k => $text)
                            <div class="mt-1 position-relative clearfix itm-text">
                              <div class="form-check m-0">
                                <input class="form-check-input" type="checkbox" id="for-text-{{$k}}"
                                       data-itd="{{$text->id}}" name="text_{{$k}}"
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
                  <div class="col-lg-12 mb-2">
                    <div class="form-floating form-floating-outline wrap-cmts d-none">
                      <div class="form-control acm-wrap-selectize acm-clearfix datas" id="failed-update-cmt">

                      </div>
                      <label for="failed-update-cmt" class="text-danger">Comments</label>
                    </div>
                  </div>
                </div>
              </form>
            </div>
          </div>
        </div>

        <div class="acm-modal-arrow acm-modal-arrow-prev" onclick="report_photo_nf_action()">
          <img src="{{url('custom/img/arrow_left.png')}}" />
        </div>
        <div class="acm-modal-arrow acm-modal-arrow-next" onclick="report_photo_nf_action(1)">
          <img src="{{url('custom/img/arrow_right.png')}}" />
        </div>

        <input type="hidden" name="report_id" value="{{$pageConfigs['item']->id}}" />
        <input type="hidden" name="popup_view_ids" />
        <input type="hidden" name="popup_view_id_itm" />
      </div>
    </div>
  </div>
  <!-- modal confirm to photo update -->
  <div class="modal fade modal-second" id="modal_photo_update" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title">Confirmation?</h4>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="row">
            <div class="col mb-12 mt-2">
              <div class="alert alert-danger">Are you sure you want to update result for this photo?</div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <div class="wrap-btns">
            @include('tastevn.htmls.form_button_loading')
            <button type="button" class="btn btn-primary btn-ok btn-submit acm-float-right" onclick="report_photo_nf_update(this)">Submit</button>
            <button type="button" class="btn btn-outline-secondary btn-ok btn-cancel" data-bs-dismiss="modal">Cancel</button>
          </div>

          <input type="hidden" name="item" />
        </div>
      </div>
    </div>
  </div>
  <!-- modal confirm to photo clear -->
  <div class="modal fade modal-second" id="modal_photo_clear" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title">Confirmation?</h4>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="row">
            <div class="col mb-12 mt-2">
              <div class="alert alert-danger">Are you sure you want to clear updated result for this photo?</div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <div class="wrap-btns">
            @include('tastevn.htmls.form_button_loading')
            <button type="button" class="btn btn-primary btn-ok btn-submit acm-float-right" onclick="report_photo_nf_clear(this)">Submit</button>
            <button type="button" class="btn btn-outline-secondary btn-ok btn-cancel" data-bs-dismiss="modal">Cancel</button>
          </div>

          <input type="hidden" name="item" />
        </div>
      </div>
    </div>
  </div>

@endsection

@section('js_end')
  <script type="text/javascript">
    var $ = jQuery.noConflict();
    $(document).ready(function() {

      report_load('{{$pageConfigs['item']->id}}');

      //keyCode
      $(document).keydown(function(e) {
        // console.log(e.keyCode);
        if ($('#modal_report_nf').hasClass('show')) {
          if (e.keyCode == 37) {
            report_photo_nf_action();
          } else if (e.keyCode == 39) {
            report_photo_nf_action(1);
          }
        }
      });

    });
  </script>
@endsection
