@extends('tastevn/layouts/layoutMaster')

@section('title', 'Admin - Sensor Kitchen Admin')

@section('vendor-style')
  <link rel="stylesheet" href="{{url('custom/library/lightbox/lc_lightbox.css')}}" />
  <link rel="stylesheet" href="{{url('custom/library/lightbox/minimal.css')}}" />
@endsection

@section('vendor-script')
  <script src="{{url('custom/library/lightbox/lc_lightbox.lite.js?v=101')}}"></script>
  <script src="{{url('custom/library/lightbox/alloy_finger.min.js')}}"></script>
@endsection

@section('page-script')
  {{--  <script src="{{asset('assets/js/forms-file-upload.js')}}"></script>--}}
@endsection

@section('content')
  <div class="row m-0 mt-3">
    <div class="col-6 mb-1">
      <h4 class="position-relative w-100 mb-0">
        <div class="acm-float-right d-none">
          <button type="button" class="btn btn-sm btn-primary p-1" onclick="speaker_allow()">
            <i class="mdi mdi-speaker"></i> Test Speaker
          </button>
          <button type="button" class="btn btn-sm btn-info p-1" onclick="toggle_header()">
            <i class="mdi mdi-alert-remove"></i> Toggle Header
          </button>
        </div>

        <a href="{{url('admin')}}">
          <span class="text-muted fw-light">Admin /</span> <span class="text-dark">Sensor Dashboard</span>
        </a>

        <input type="hidden" name="current_itd"/>

      </h4>
    </div>

    <div class="col-lg-6 mb-1 wrap_sensor_selected">
      <div class="form-floating form-floating-outline mb-1">
        <div class="form-control acm-wrap-selectize" id="select-item-restaurant">
          <select name="restaurant_id" class="ajx_selectize" required
                  data-value="restaurant" onchange="sensor_selected(this)"
                  data-placeholder="Please choose restaurant sensor..."
          ></select>
        </div>
        <label for="select-item-restaurant" class="text-danger">Restaurant Sensor</label>
      </div>
    </div>

    <div class="col-12 mb-1">
      <div class="card">
        <div class="card-body p-0">
          <div class="row">
            <div class="col-lg-6">
              <div class="p-1">
                <div class="text-center text-uppercase overflow-hidden mb-1">
                  <div class="badge bg-secondary">Food Recipe</div>
                </div>
                <div class="wrap-selected-food">
                  <div class="row m-0">
                    <div class="col-lg-12 p-0 mb-1 position-relative">
                      <div class="text-center w-auto d-none">
                        <h3 class="food-name"></h3>
                      </div>

                      <div class="text-center w-auto">
                        <img class="w-100 food-photo" loading="lazy" />
                      </div>
                    </div>

                    <div class="col-lg-12 mb-1">
                      <div class="position-relative w-100 wrap-ingredients"></div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div class="col-lg-6 wrap_sensor_foods">
              <div class="p-1">
                <div class="text-center text-uppercase overflow-hidden mb-1">
                  <div class="badge bg-secondary">Latest Sensor Photo</div>
                </div>
                <div>
                  <div class="row">
                    <div class="col-lg-6 mb-1 wrap_notify_result d-none result_photo_standard">
                      <div class="text-center w-100">
                        <img class="w-100" loading="lazy" src=""/>
                      </div>
                    </div>

                    <div class="col-lg-12 mb-1 wrap_notify_result d-none result_photo_sensor">
                      <div class="d-inline-block">
                        <a class="acm-lightbox-photo">
                          <img class="w-100" loading="lazy" src=""/>
                        </a>
                      </div>
                    </div>

                    <div class="col-lg-12 mb-1 wrap_notify_result d-none result_photo_itd">
                      <div class="acm-float-right acm-ml-px-5 wrap_notify_result d-none result_photo_status">
                        <div class="data_result"></div>
                        <div class="data_btns d-none">
                          <button type="button" class="btn btn-sm btn-primary btn_resolved"
                                  data-value="0"
                                  onclick="photo_resolve(this)"
                          >Resolve
                          </button>
                          <button type="button" class="btn btn-sm btn-primary btn_marked"
                                  data-value="0"
                                  onclick="photo_mark(this)"
                          >Mark
                          </button>
                        </div>
                      </div>

                      <div class="d-inline-block">
                        <div class="text-dark fw-bold">+ Photo ID: <b class="fw-bold"></b></div>
                      </div>

                      <div class="data_result d-inline-block"></div>
                    </div>

                    <div class="col-lg-12 mb-1 wrap_notify_result d-none result_predicted_dish">
                      <div class="d-inline-block">
                        <div class="text-dark fw-bold">+ Dish:</div>
                      </div>

                      <div class="data_result d-inline-block"></div>
                    </div>

                    <div class="col-lg-12 mb-1 wrap_notify_result d-none result_ingredients_found">
                      <div class="w-100">
                        <div class="text-dark fw-bold">+ Ingredients Found:</div>
                      </div>

                      <div class="data_result"></div>
                    </div>

                    <input type="hidden" name="current_file_id"/>
                    <input type="hidden" name="current_file_status"/>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

  </div>
@endsection

@section('js_end')
  <div class="acm-toast-wrapper toast-bottom-right d-none result_ingredients_missing">
    <div class="toast toast-error @if(!$isMobi) acm-width-500-min @endif bg-danger-subtle" aria-live="assertive"
         style="display: block;">
      <div class="toast-title"><span class="badge bg-danger text-uppercase fs-6">Please double check</span>
      </div>
      <div class="toast-message data_result"></div>
    </div>
  </div>

  <div class="acm-toast-wrapper toast-bottom-left d-none result_main_note">
    <div class="toast toast-error @if(!$isMobi) acm-width-800-min @endif bg-danger-subtle" aria-live="assertive"
         style="display: block;">
      <div class="toast-title"><span class="badge bg-danger text-uppercase fs-6">Main Note</span>
      </div>
      <div class="toast-message data_result"></div>
    </div>
  </div>

  <script type="text/javascript">
    $(document).ready(function () {

      if (notify_realtime) {
        clearInterval(notify_realtime);
      }

      toggle_header();

      interval_running = setInterval(function () {
        sensor_actived();
      }, acmcfs.timeout_kitchen);

    });

    var sys_running = 0;
    var cur_sensor = 0;
    var interval_running = null;

    function sensor_checker() {

      if (sys_running) {
        return false;
      }
      sys_running = 1;

      axios.post('/admin/kitchen/checker', {
        item: cur_sensor,
        type: 'main_dashboard',
      })
        .then(response => {
          var wrap = $('.wrap_sensor_foods');

          var current_file_id = parseInt(wrap.find('input[name=current_file_id]').val());
          var current_file_status = wrap.find('input[name=current_file_status]').val();

          var check_file_id = parseInt(response.data.file_id);
          var check_file_status = response.data.status;

          if (!check_file_id) {
            return false;
          }

          if (check_file_id != current_file_id) {
            wrap.find('input[name=current_file_id]').val(check_file_id);
            wrap.find('input[name=current_file_status]').val(check_file_status);

            $('.wrap_notify_result').addClass('d-none');

            $('.result_ingredients_missing').addClass('d-none');
            $('.result_main_note').addClass('d-none');
            $('.result_photo_status .data_btns').addClass('d-none');

            $('.result_photo_sensor img').attr('src', response.data.file_url);
            $('.result_photo_sensor').removeClass('d-none');

            $('.result_photo_itd .data_result').empty()
              .append('<div class="text-danger fw-bold acm-ml-px-10 acm-fs-15">' + check_file_id + '</div>');
            $('.result_photo_itd').removeClass('d-none');
          }

          if (check_file_status == current_file_status) {
            // return false;
          }

          if (check_file_status == 'new') {

            var no_photo = '{{url('custom/img/logo_')}}' + response.data.datas.restaurant_id + '.png?v=1';
            $('.wrap-selected-food').find('.food-photo').attr('src', no_photo);
            $('.wrap-selected-food').find('.wrap-ingredients').empty();

            $('.result_photo_status .data_result').empty()
              .append('<div class="badge bg-info fw-bold acm-ml-px-10 acm-fs-13">checking...</div>');
            $('.result_photo_status').removeClass('d-none');

          }
          else {
            //show data
            if (response.data.datas && (response.data.datas != '' || response.data.datas != '[]')) {
              food_datas(response.data.datas);
            }
          }

          //lc_lightbox
          $('.result_photo_sensor a').attr('href', response.data.file_url + '?dpr=1&auto=format&fit=crop&w=2000&q=80&cs=tinysrgb');
          $('.result_photo_sensor a').attr('title', response.data.datas.sensor_name + ' at ' + response.data.datas.rfs_time);
          $('.result_photo_sensor a').attr('data-lcl-txt', response.data.datas.rfs_note);
          $('.result_photo_sensor a').attr('data-lcl-author', response.data.datas.rfs_id);
          $('.result_photo_sensor a').attr('data-lcl-thumb', response.data.datas.file_url + '?dpr=1&auto=format&fit=crop&w=150&q=80&cs=tinysrgb');

          lc_lightbox('.acm-lightbox-photo', {
            wrap_class: 'lcl_fade_oc',
            thumb_attr: 'data-lcl-thumb',
          });

        })
        .catch(error => {
          console.log(error);
        })
        .then(res => {
          sys_running = 0;
        });

      return false;
    }

    function food_datas(datas) {
      var wrap = $('.wrap-selected-food');

      var no_photo = '{{url('custom/img/logo_')}}' + datas.restaurant_id + '.png?v=1';
      wrap.find('.food-photo').attr('src', no_photo);
      wrap.find('.wrap-ingredients').empty();

      if (datas.food_id) {

        //standard
        wrap.find('.food-name').empty().text(datas.food_name);
        wrap.find('.food-photo').attr('src', datas.food_photo);
        wrap.find('.wrap-ingredients').empty().append(datas.html_info);

        //sensor
        $('.result_photo_status .data_result').empty()
          .append('<div class="badge bg-primary fw-bold acm-ml-px-10 acm-fs-13 d-none">checked</div>');
        if (datas.ingredients_missing.length) {
          // $('.result_photo_status .data_btns').removeClass('d-none');
        }

        //predicted_dish
        if (datas.food_name != '') {
          $('.result_predicted_dish .data_result').empty().append('<div class="text-danger fw-bold acm-ml-px-10">' + datas.food_name + '</div>');
          $('.result_predicted_dish').removeClass('d-none');
        }

        // console.log(datas.ingredients_missing);
        // console.log(datas.ingredients_found);

        //ingredients_missing
        var html = '';
        if (datas.ingredients_missing.length) {
          datas.ingredients_missing.forEach(function (v, k) {
            html += '<div class="text-dark fw-bold fs-1">- <b class="text-dark acm-mr-px-5">' + v.quantity + '</b> ' + v.name + '</div>';
          });
        }
        if (html && html != '') {
          $('.result_ingredients_missing .data_result').empty().append(html);
          $('.result_ingredients_missing').removeClass('d-none');
        }

        //main_note
        if (datas.main_note && datas.main_note !== '' && datas.main_note !== 'null') {
          html = '<div class="text-dark fw-bold fs-3">' + bind_nl2br(datas.main_note) + '</div>';

          $('.result_main_note .data_result').empty().append(html);
          $('.result_main_note').removeClass('d-none');
        }

        //btns
        // $('.result_photo_status .data_btns .btn_resolved').removeClass('btn-success')
        //   .addClass('btn-primary');
        // $('.result_photo_status .data_btns .btn_resolved').text('Resolve');
        // $('.result_photo_status .data_btns .btn_resolved').attr('data-value', 0);
        //
        // $('.result_photo_status .data_btns .btn_marked').removeClass('btn-success')
        //   .addClass('btn-primary');
        // $('.result_photo_status .data_btns .btn_marked').text('Mark');
        // $('.result_photo_status .data_btns .btn_marked').attr('data-value', 0);
        //
        // //is_resolved
        // if (datas.is_resolved) {
        //   $('.result_ingredients_missing').addClass('d-none');
        //
        //   $('.result_photo_status .data_btns .btn_resolved').addClass('btn-success')
        //     .removeClass('btn-primary');
        //   $('.result_photo_status .data_btns .btn_resolved').text('Resolved');
        //   $('.result_photo_status .data_btns .btn_resolved').attr('data-value', 1);
        // }
        // //is_marked
        // if (datas.is_marked) {
        //
        //   $('.result_photo_status .data_btns .btn_marked').addClass('btn-success')
        //     .removeClass('btn-primary');
        //   $('.result_photo_status .data_btns .btn_marked').text('Marked');
        //   $('.result_photo_status .data_btns .btn_marked').attr('data-value', 1);
        // }

        //ingredients_found
        html = '';
        if (datas.ingredients_found.length) {
          html += '<div class="row m-0">';
          datas.ingredients_found.forEach(function (v, k) {
            html += '<div class="col-lg-6">';
            html += '<div class="text-dark acm-ml-px-10 fs-5 fw-bold">- <b class="text-danger acm-mr-px-5">' + v.ingredient_quantity + '</b> ' + v.name + '</div>';
            html += '</div>';
          });
          html += '</div>';
        }
        if (html && html != '') {
          $('.result_ingredients_found .data_result').empty().append(html);
          $('.result_ingredients_found').removeClass('d-none');
        }

        if (datas.confidence > 1) {
          if (!datas.ingredients_missing.length || datas.ingredients_missing.length > 1) {
            $('.result_photo_status').removeClass('d-none');
            $('.result_photo_status .data_result').empty()
              .append('<div class="badge bg-danger fw-bold acm-ml-px-10 acm-fs-13">Less Training</div>');
          }
        }

      } else {

        $('.result_photo_status').removeClass('d-none');
        $('.result_photo_status .data_result').empty()
          .append('<div class="badge bg-danger fw-bold acm-ml-px-10 acm-fs-13">Not Trained Yet</div>');
      }

    }

    function photo_resolve(ele) {
      var bind = $(ele);
      var parent = bind.closest('.data_btns');
      var rfs_id = $('.wrap_sensor_foods input[name=current_file_id]').val();

      var status = parseInt(bind.attr('data-value'));
      var value = 0;
      if (status) {

        bind.addClass('btn-primary').removeClass('btn-success');
        bind.text('Resolve');

      } else {

        value = 1;
        bind.removeClass('btn-primary').addClass('btn-success');
        bind.text('Resolved');

        $('.result_ingredients_missing').addClass('d-none');
      }

      bind.attr('data-value', value);

      axios.post('/admin/sensor/food/scan/resolve', {
        rfs: rfs_id,
        val: value,
      })
        .then(response => {
          message_from_toast('success', acmcfs.message_title_success, acmcfs.message_description_success_update);
        })
        .catch(error => {
          console.log(error);
        });
    }

    function photo_mark(ele) {
      var bind = $(ele);
      var parent = bind.closest('.data_btns');
      var rfs_id = $('.wrap_sensor_foods input[name=current_file_id]').val();

      var status = parseInt(bind.attr('data-value'));
      var value = 0;
      if (status) {

        bind.addClass('btn-primary').removeClass('btn-success');
        bind.text('Mark');

      } else {

        value = 1;
        bind.removeClass('btn-primary').addClass('btn-success');
        bind.text('Marked');
      }

      bind.attr('data-value', value);

      axios.post('/admin/sensor/food/scan/mark', {
        rfs: rfs_id,
        val: value,
      })
        .then(response => {
          message_from_toast('success', acmcfs.message_title_success, acmcfs.message_description_success_update);
        })
        .catch(error => {
          console.log(error);
        });
    }

    function sensor_selected(ele) {
      var bind = $(ele);
      var selected = parseInt(bind.val());
      if (!selected) {
        return false;
      }

      cur_sensor = selected;
    }

    function sensor_actived() {
      var selector = $('.wrap_sensor_selected select');
      var selected = parseInt(selector.val());
      if (selector.hasClass('ajx_selectize')) {
        return false;
      }

      if (!selected) {
        $('.wrap_sensor_selected select').selectize()[0].selectize.setValue(5);
      }

      sensor_checker();
    }
  </script>
@endsection
