@extends('tastevn/layouts/layoutMaster')

@section('title', 'Admin - Sensor Kitchen')

@section('vendor-style')
  {{--  <link rel="stylesheet" href="{{asset('assets/vendor/libs/spinkit/spinkit.css')}}" />--}}
@endsection

@section('vendor-script')
  {{--  <script src="{{asset('assets/vendor/libs/dropzone/dropzone.js')}}"></script>--}}
@endsection

@section('page-script')
  {{--  <script src="{{asset('assets/js/forms-file-upload.js')}}"></script>--}}
@endsection

@section('content')
  <div class="row m-0">
    <div class="col-12 mb-1">
      <h4 class="position-relative w-100 mb-0">
        <div class="acm-float-right">
          <button type="button" class="btn btn-sm btn-primary p-1" onclick="speaker_allow()">
            <i class="mdi mdi-speaker"></i> Test Speaker
          </button>
          <button type="button" class="btn btn-sm btn-info p-1" onclick="toggle_header()">
            <i class="mdi mdi-alert-remove"></i> Toggle Header
          </button>
        </div>

        <a href="{{url('admin')}}">
          <span class="text-muted fw-light">Admin /</span> <span class="text-dark">{{$pageConfigs['item']->name}}</span>
        </a>

        <input type="hidden" name="restaurant_id" value="{{$pageConfigs['item']->id}}"/>
        <input type="hidden" name="restaurant_parent_id" value="{{$pageConfigs['item']->restaurant_parent_id}}"/>
        <input type="hidden" name="current_itd"/>

      </h4>
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
                    <div class="col-lg-6 mb-1 d-none">
                      <div class="form-floating form-floating-outline mb-1">
                        <div class="form-control acm-wrap-selectize" id="select-item-restaurant">
                          <select name="restaurant_parent_id" class="ajx_selectize" required
                                  data-value="restaurant_parent" onchange="restaurant_selected(this)"
                                  data-placeholder="Please choose restaurant..."
                          ></select>
                        </div>
                        <label for="select-item-restaurant" class="text-danger">Restaurant</label>
                      </div>
                    </div>

                    <div class="col-lg-6 mb-1 d-none">
                      <div class="form-floating form-floating-outline mb-1">
                        <div class="form-control acm-wrap-selectize" id="select-item-food">
                          <select name="food" class="opt_selectize" onchange="food_selected(this)"
                                  data-placeholder="Please choose dish..."
                          ></select>
                        </div>
                        <label for="select-item-food" class="text-danger">Dish</label>

                        <input type="hidden" name="current_food"/>
                        <input type="hidden" name="current_restaurant_parent_id"/>
                      </div>
                    </div>

                    <div class="col-lg-12 p-0 mb-1 position-relative">
                      <div class="text-center w-auto d-none">
                        <h3 class="food-name"></h3>
                      </div>

                      <div class="text-center w-auto">
                        <img class="w-100 food-photo" loading="lazy" src="{{url('custom/img/logo_'. $pageConfigs['item']->restaurant_parent_id . '.png?v=1')}}"/>
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
                        <img class="w-100" loading="lazy" src=""/>
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
    <div class="toast toast-error @if(!$isMobi) acm-width-700-min @endif bg-danger-subtle" aria-live="assertive"
         style="display: block;">
      <div class="toast-title"><span class="badge bg-danger text-uppercase fs-6">Please double check</span>
      </div>
      <div class="toast-message data_result"></div>
    </div>
  </div>

  @if($pageConfigs['debug'])
    <div class="acm-toast-wrapper toast-top-left d-none result_time_check">
      <div class="toast toast-error @if(!$isMobi) acm-width-700-min @endif bg-danger-subtle" aria-live="assertive"
           style="display: block;">
        <div class="toast-title"><span class="badge bg-danger text-uppercase fs-6">Times</span>
        </div>
        <div class="toast-message data_result"></div>
      </div>
    </div>
  @endif

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

      toggle_header();

      if (notify_realtime) {
        clearInterval(notify_realtime);
      }

      @if($pageConfigs['sse'])
        var sse_source = new EventSource("{{url('admin/sse/stream/kitchen/' . $pageConfigs['item']->id)}}");
        sse_source.onmessage = function (evt) {
          let sse_datas = JSON.parse(evt.data);
          sensor_sse(sse_datas);
        }

      @else

        setInterval(function () {
          sensor_checker();
        }, acmcfs.timeout_kitchen);
      @endif

    });

    var sys_running = 0;

    function sensor_sse(datas) {
      // console.log(datas);

      var wrap = $('.wrap_sensor_foods');

      var current_file_id = parseInt(wrap.find('input[name=current_file_id]').val());
      var current_file_status = wrap.find('input[name=current_file_status]').val();

      var check_file_id = parseInt(datas.file_id);
      var check_file_status = datas.status;

      if (!check_file_id) {
        return false;
      }
      if (current_file_id == check_file_id && current_file_status == check_file_status) {
        return false;
      }

      if (check_file_id != current_file_id) {
        wrap.find('input[name=current_file_id]').val(check_file_id);
        wrap.find('input[name=current_file_status]').val(check_file_status);

        $('.wrap_notify_result').addClass('d-none');

        $('.result_ingredients_missing').addClass('d-none');
        $('.result_main_note').addClass('d-none');
        $('.result_photo_status .data_btns').addClass('d-none');

        $('.result_photo_sensor img').attr('src', datas.file_url);
        $('.result_photo_sensor').removeClass('d-none');

        $('.result_photo_itd .data_result').empty()
          .append('<div class="text-danger fw-bold acm-ml-px-10 acm-fs-15">' + check_file_id + '</div>');
        $('.result_photo_itd').removeClass('d-none');
      }

      if (check_file_status == 'new' || check_file_status == 'scanned') {

        var no_photo = '{{url('custom/img/logo_')}}' + datas.restaurant_id + '.png?v=1';
        $('.wrap-selected-food').find('.food-photo').attr('src', no_photo);
        $('.wrap-selected-food').find('.wrap-ingredients').empty();

        $('.result_photo_status .data_result').empty()
          .append('<div class="badge bg-info fw-bold acm-ml-px-10 acm-fs-13">checking...</div>');
        $('.result_photo_status').removeClass('d-none');

      }
      else {
        //show data
        food_datas(datas);
      }


    }

    function food_predict_by_api(item_id) {
      var wrap = $('.wrap-selected-food');

      // sys_running = 1;

      $('.result_photo_status .data_result').empty()
        .append('<div class="badge bg-success fw-bold acm-ml-px-10 acm-fs-13">predicting...</div>');

      axios.post('/admin/kitchen/predict', {
        item: item_id,
        restaurant_id: '{{$pageConfigs['item']->id}}',
      })
        .then(response => {

          //show data
          // food_datas(response.data.datas);

          //temp off
          //notify
          // if (response.data.notifys && response.data.notifys.length) {
          //
          //   response.data.notifys.forEach(function (v, k) {
          //
          //     var html_toast = '<div class="cursor-pointer" onclick="sensor_food_scan_info(' + v.itd + ')">';
          //     html_toast += '<div class="acm-fs-13">+ Dish: <b><span class="acm-mr-px-5 text-danger">' + v.food_confidence + '%</span><span>' + v.food_name + '</span></b></div>';
          //
          //     html_toast += '<div class="acm-fs-13">+ Ingredients Missing:</div>';
          //     v.ingredients.forEach(function (v1, k1) {
          //       if (v1 && v1 !== '' && v1.trim() !== '') {
          //         html_toast += '<div class="acm-fs-13 acm-ml-px-10">- ' + v1 + '</div>';
          //       }
          //     });
          //
          //     html_toast += '</div>';
          //     message_from_toast('info', v.restaurant_name, html_toast, true);
          //   });
          //
          //   if (response.data.printer) {
          //   page_open(acmcfs.link_base_url + '/printer?ids=' + response.data.notify_ids.toString());
          //   }
          // }

          // if (response.data.speaker) {
          //   setTimeout(function () {
          //     speaker_play();
          //   }, 888);
          // }

        })
        .catch(error => {
          console.log(error);

          page_reload();
        })
        .then(res => {
          // sys_running = 0;
        });

      return false;
    }

    function sensor_checker() {

      if (sys_running) {
        return false;
      }
      sys_running = 1;

      @if($pageConfigs['debug'])
        var img_url = 'https://ai.block8910.com/sensors/58-5b-69-15-cd-2b/SENSOR/1/2024-07-22/10/SENSOR1_2024-07-22-10-31-01-708_170.jpg';
        internet_download_check(img_url);
      @endif

      axios.post('/admin/kitchen/checker', {
        item: '{{$pageConfigs['item']->id}}',
      })
        .then(response => {
          var wrap = $('.wrap_sensor_foods');

          var current_file_id = parseInt(wrap.find('input[name=current_file_id]').val());
          var current_file_status = wrap.find('input[name=current_file_status]').val();

          var check_file_id = parseInt(response.data.file_id);
          var check_file_status = response.data.status;

          if (!check_file_id) {
            sys_running = 0;

            return false;
          }

          if (current_file_id == check_file_id && current_file_status == check_file_status) {
            sys_running = 0;

            return false;
          }

          var running_datas = 0;

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

          // if (check_file_status == current_file_status) {
          //   return false;
          // }

          //speaker
          // if (check_file_status == 'checked' && current_file_status == 'new') {
          //   food_predict_by_api(check_file_id);
          // }

          if (check_file_status == 'new' || check_file_status == 'scanned') {

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
              running_datas = 1;

              food_datas(response.data.datas);
            }
          }

          if (!running_datas) {
            sys_running = 0;
          }
        })
        .catch(error => {
          console.log(error);

          page_reload();
        })
        .then(res => {
          // sys_running = 0;
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
          if (parseInt(datas.speaker)) {
            $('.wrap_sensor_foods input[name=current_file_status]').val('checked');

            speaker_play();
          }
        }

        //predicted_dish
        if (datas.food_name != '') {
          $('.result_predicted_dish .data_result').empty().append('<div class="text-danger fw-bold acm-ml-px-10">' + datas.food_name + '</div>');
          $('.result_predicted_dish').removeClass('d-none');
        }

        //ingredients_missing
        var html = '';
        if (datas.ingredients_missing.length) {
          datas.ingredients_missing.forEach(function (v, k) {
            html += '<div class="text-dark fw-bold fs-1">- <b class="text-dark acm-mr-px-5">' + v.ingredient_quantity + '</b> ' + v.name + '</div>';
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
            html += '<div class="text-dark acm-ml-px-10 fs-5 fw-bold">- <b class="text-danger acm-mr-px-5">' + v.quantity + '</b> ' + v.name + '</div>';
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

      @if($pageConfigs['debug'])
      var html_times = '';
      var time_photo = datas.time_photo;
      var time_scan = datas.time_scan;
      var time_end = datas.time_end;
      var total_times = datas.total_times;
      var localhost = parseInt(datas.localhost);

      var html_robos = '';
      if (localhost) {
        html_robos = '<div class="mb-2 acm-clearfix">' +
          '<div class="text-dark fw-bold acm-float-right">' + datas.total_robos + '</div>' +
          '<div class="text-dark overflow-hidden">Robos (seconds): </div>' +
          '</div>';
      }

      $('.result_time_check').removeClass('d-none');

      var connection = '';
      if (navigator && navigator.connection) {
        connection = navigator.connection.effectiveType + ' - ' + navigator.connection.downlink + ' Mb/s - ' + navigator.connection.rtt + ' ms';
      }

      html_times = '<div class="position-relative">' +
        '<div class="mb-2 acm-clearfix">' +
        '<div class="text-dark fw-bold acm-float-right">' + time_photo + '</div>' +
        '<div class="text-dark overflow-hidden">Photo Uploaded At: </div>' +
        '</div>' +
        '<div class="mb-2 acm-clearfix">' +
        '<div class="text-dark fw-bold acm-float-right">' + time_scan + '</div>' +
        '<div class="text-dark overflow-hidden">Photo Begin Scan At: </div>' +
        '</div>' +
        '<div class="mb-2 acm-clearfix">' +
        '<div class="text-dark fw-bold acm-float-right">' + time_end + '</div>' +
        '<div class="text-dark overflow-hidden">Finish Predicted At: </div>' +
        '</div>' + html_robos +
        '<div class="mb-2 acm-clearfix">' +
        '<div class="text-dark fw-bold acm-float-right">' + total_times + '</div>' +
        '<div class="text-dark overflow-hidden">Total (seconds): </div>' +
        '</div>' +
        '<div class="mb-2 acm-clearfix">' +
        '<div class="text-dark fw-bold acm-float-right">' + connection + '</div>' +
        '<div class="text-dark overflow-hidden">Navigator Connection: </div>' +
        '</div>' +
        '</div>';

      $('.result_time_check .data_result').empty()
        .append(html_times);
      @endif

        sys_running = 0;
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
  </script>
@endsection
