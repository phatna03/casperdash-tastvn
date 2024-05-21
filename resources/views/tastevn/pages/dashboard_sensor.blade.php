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
  <div class="row p-1">
    <div class="col-12 mb-2">
      <h4 class="mb-2 position-relative w-100">
        <div class="acm-float-right">
          <button type="button" class="btn btn-sm btn-primary p-1" onclick="speaker_allow()">
            <i class="mdi mdi-speaker"></i> Test Speaker
          </button>
          <button type="button" class="btn btn-sm btn-info p-1" onclick="toggle_header()">
            <i class="mdi mdi-alert-remove"></i> Toggle Header
          </button>
        </div>

        <span class="text-muted fw-light">Admin /</span> Sensor Kitchen
      </h4>
    </div>

    <div class="col-12 mb-2">
      <div class="card">
        <div class="card-body">
          <div class="row">
            <div class="@if(count($viewer->get_sensors())) col-lg-6 @else col-lg-12 @endif">
              <div class="acm-border-css p-2 border-dark acm-height-450-min">
                <div class="text-center text-uppercase overflow-hidden mb-2">
                  <div class="badge bg-secondary">Food Recipe</div>
                </div>
                <div class="wrap-selected-food">
                  <div class="row">
                    <div class="col-lg-6 mb-1">
                      <div class="form-floating form-floating-outline mb-2">
                        <div class="form-control acm-wrap-selectize" id="select-item-restaurant">
                          <select name="restaurant_parent_id" class="ajx_selectize" required
                                  data-value="restaurant_parent" onchange="restaurant_selected(this)"
                                  data-placeholder="Please choose restaurant..."
                          ></select>
                        </div>
                        <label for="select-item-restaurant" class="text-danger">Restaurant</label>
                      </div>
                    </div>

                    <div class="col-lg-6 mb-1">
                      <div class="form-floating form-floating-outline mb-2">
                        <div class="form-control acm-wrap-selectize" id="select-item-food">
                          <select name="food" class="opt_selectize" onchange="food_selected(this)"
                                  data-placeholder="Please choose dish..."
                          ></select>
                        </div>
                        <label for="select-item-food" class="text-danger">Dish</label>

                        <input type="hidden" name="current_food" />
                        <input type="hidden" name="current_restaurant_parent_id" />
                      </div>
                    </div>

                    <div class="col-lg-12 mb-1 position-relative">
                      <div class="text-center w-auto d-none">
                        <h3 class="food-name"></h3>
                      </div>

                      <div class="text-center w-auto">
                        <img class="w-100 mt-2 food-photo" src="{{url('custom/img/no_photo.png')}}" />
                      </div>
                    </div>

                    <div class="col-lg-12 mb-1">
                      <div class="position-relative w-100 wrap-ingredients"></div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            @if(count($viewer->get_sensors()))
            <div class="col-lg-6">
              <div class="acm-border-css p-2 border-dark acm-height-450-min">
                <div class="text-center text-uppercase overflow-hidden mb-2">
                  <div class="badge bg-secondary">Latest Sensor Photo</div>
                </div>
                <div>
                  <div class="row">
                    <div class="col-lg-12 mb-2">
                      <div class="form-floating form-floating-outline mb-2">
                        <div class="form-control acm-wrap-selectize" id="restaurant-sensor-select">
                          <select name="sensor" class="opt_selectize" onchange="sensor_selected(this)"
                                  data-placeholder="Please choose restaurant sensor..."
                          >
                            @foreach($viewer->get_sensors() as $sensor)
                              <option value="{{$sensor->id}}" @if(count($viewer->get_sensors()) == 1) selected="selected" @endif>{{$sensor->name}}</option>
                            @endforeach
                          </select>
                        </div>
                        <label for="restaurant-sensor-select" class="text-danger">Restaurant Sensor</label>
                      </div>
                    </div>

                    <input type="hidden" name="current_itd" />

                    <div class="col-lg-6 mb-2 wrap_notify_result d-none result_photo_standard">
                      <div class="text-center w-100">
                        <img class="w-100" src="" />
                      </div>
                    </div>

                    <div class="col-lg-12 mb-2 wrap_notify_result d-none result_photo_sensor">
                      <div class="w-100">
                        <img class="w-100" src="" />
                      </div>
                    </div>

                    <div class="col-lg-12 mb-1 wrap_notify_result d-none result_photo_itd">
                      <div class="w-100">
                        <div class="text-dark">+ Photo ID: <b class="fw-bold"></b></div>
                      </div>

                      <div class="w-100 data_result"></div>
                    </div>

                    <div class="col-lg-12 mb-2 wrap_notify_result d-none result_predicted_dish">
                      <div class="w-100">
                        <div class="text-dark">+ Predicted Dish:</div>
                      </div>

                      <div class="w-100 data_result"></div>
                    </div>

                    <div class="col-lg-12 mb-1 wrap_notify_result d-none result_ingredients_missing">
                      <div class="w-100">
                        <div class="text-dark">+ Ingredients Missing:</div>
                      </div>

                      <div class="w-100 data_result"></div>
                    </div>

                    <div class="col-lg-12 mb-1 wrap_notify_result d-none result_unknown_data">
                      <div class="w-100">
                        <div class="text-dark">+ Status: <b class="fw-bold text-danger">Unknown photo information</b></div>
                      </div>

                      <div class="w-100 data_result"></div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            @endif
          </div>
        </div>
      </div>
    </div>

  </div>
@endsection

@section('js_end')
  <script type="text/javascript">
    $(document).ready(function() {
      toggle_header();

      setInterval(function () {
        sensor_latest();
      }, acmcfs.timeout_notification);

      if (notify_realtime) {
        // console.log('=============');
        clearInterval(notify_realtime);

        // sensor_latest();

        acmcfs.notify_running = 0;

        //new interval
        notify_realtime = setInterval(function () {
          notification_dashboard();
        }, acmcfs.timeout_notification);
      }

    });

    function food_selected(ele) {
      var wrap = $(ele).closest('.wrap-selected-food');
      var chosen = $(ele).val();
      if (!chosen || !parseInt(chosen)) {
        return false;
      }
      var restaurant_parent_id = wrap.find('select[name=restaurant_parent_id]').val();

      wrap.find('input[name=current_food]').val(chosen);
      wrap.find('input[name=current_restaurant_parent_id]').val(restaurant_parent_id);

      food_get_by_datas(chosen, restaurant_parent_id);
    }

    function food_get_by_datas(food_id, restaurant_parent_id) {
      var wrap = $('.wrap-selected-food');

      axios.post('/admin/dashboard/food/get/info', {
        restaurant_parent_id: restaurant_parent_id,
        item: food_id,
      })
        .then(response => {

          wrap.find('.food-name').empty().text(response.data.food_name);
          wrap.find('.food-photo').attr('src', response.data.food_photo);
          wrap.find('.wrap-ingredients').empty().append(response.data.html_info);

        })
        .catch(error => {
          if (error.response.data && Object.values(error.response.data).length) {
            Object.values(error.response.data).forEach(function (v, k) {
              message_from_toast('error', 'Invalid Credentials', v);
            });
          }
        });

      return false;
    }

    function restaurant_selected(ele) {
      var wrap = $(ele).closest('.wrap-selected-food');

      var chosen = $(ele).val();
      if (!chosen || !parseInt(chosen)) {
        wrap.find('select[name=food]').selectize()[0].selectize.destroy();
        wrap.find('select[name=food]').selectize({});
        return false;
      }

      axios.post('/admin/dashboard/restaurant/food/get', {
        item: chosen,
      })
        .then(response => {

          wrap.find('select[name=food]').selectize()[0].selectize.destroy();
          wrap.find('select[name=food]').selectize({
            maxItems: 1,
            valueField: 'id',
            labelField: 'name',
            searchField: 'name',
            options: response.data.items,
            create: false,
          });

        })
        .catch(error => {
          if (error.response.data && Object.values(error.response.data).length) {
            Object.values(error.response.data).forEach(function (v, k) {
              message_from_toast('error', 'Invalid Credentials', v);
            });
          }
        });

      return false;
    }

    function sensor_selected(ele) {
      var bind = $(ele);
      var selector = bind.selectize()[0].selectize;

      var chosen = bind.val();
      if (!chosen || !parseInt(chosen)) {
        // console.log('=============');
        // console.log(selector);

        chosen = selector.currentResults.items[0].id;
        selector.setValue(chosen);
      }

      // sensor_latest();

      //new interval
      acmcfs.notify_running = 0;

      notify_realtime = setInterval(function () {
        notification_dashboard();
      }, acmcfs.timeout_notification);
    }

    function sensor_latest(itd = 0) {
      var selector = $('#restaurant-sensor-select select');
      if (!selector || !selector.length) {
        return false;
      }

      var sensor = selector.val();

      axios.post('/admin/sensor/kitchen', {
        restaurant_id: sensor,
        itd: itd,
      })
        .then(response => {

          var current_itd = $('input[name=current_itd]').val();

          if (response.data.item && response.data.item.itd && parseInt(response.data.item.itd) != current_itd) {

            $('.wrap_notify_result').addClass('d-none');

            $('input[name=current_itd]').val(response.data.item.itd);

            $('.result_photo_itd b').text(response.data.item.itd);
            $('.result_photo_itd').removeClass('d-none');

            if (response.data.item.status == 'failed') {

              //photo sensor
              $('.result_photo_sensor img').removeAttr().attr('src', response.data.item.photo_url);
              $('.result_photo_sensor').removeClass('d-none');

              // $('.result_photo_sensor').removeClass('col-lg-6').addClass('col-lg-12');

              //status
              $('.result_unknown_data').removeClass('d-none');

            } else {

              //food standard
              // $('.result_photo_standard img').removeAttr().attr('src', response.data.item.food_photo);
              // $('.result_photo_standard').removeClass('d-none');

              //photo sensor
              $('.result_photo_sensor img').removeAttr().attr('src', response.data.item.photo_url);
              $('.result_photo_sensor').removeClass('d-none');

              // $('.result_photo_sensor').removeClass('col-lg-12').addClass('col-lg-6');

              //predicted_dish
              if (response.data.item.food_name != '') {
                $('.result_predicted_dish .data_result').empty().append('<div class="text-danger fw-bold acm-ml-px-10">- ' + response.data.item.food_name + '</div>');
                $('.result_predicted_dish').removeClass('d-none');
              }

              //ingredients_missing
              var html = '';
              if (response.data.item.ingredients_missing.length) {
                response.data.item.ingredients_missing.forEach(function (v, k) {
                  if (v && v !== '' && v.trim() !== '') {
                    html += '<div class="text-danger fw-bold acm-ml-px-10">- ' + v + '</div>';
                  }
                });
              }
              if (html && html != '') {
                $('.result_ingredients_missing .data_result').empty().append(html);
                $('.result_ingredients_missing').removeClass('d-none');
              }

              //get food standard
              if (response.data.item.view_food_id && response.data.item.view_restaurant_parent_id) {
                var current_food_id = $('.wrap-selected-food').find('input[name=current_food_id]').val();
                var current_restaurant_parent_id = $('.wrap-selected-food').find('input[name=current_restaurant_parent_id]').val();

                if (parseInt(response.data.item.view_food_id) != parseInt(current_food_id)
                  || parseInt(response.data.item.view_restaurant_parent_id) != parseInt(current_restaurant_parent_id)
                ) {
                  food_get_by_datas(response.data.item.view_food_id, response.data.item.view_restaurant_parent_id);
                }
              }
            }
          }

        })
        .catch(error => {

        });

      return false;
    }

    function notification_dashboard() {
      var selector = $('#restaurant-sensor-select select');
      if (!selector || !selector.length) {
        console.log('invalid selector...');
        clearInterval(notify_realtime);
        return false;
      }

      acmcfs.notify_running = 1;

      var sensor = selector.val();
      // console.log('runnnnnnnnnnnnnnnnnnnnnnnnn');
      // console.log(sensor);

      axios.post('/admin/notification/dashboard', {
        restaurant_id: sensor,
      })
        .then(response => {

          if (response.data.items && response.data.items.length) {

            var notify_itd = 0;

            response.data.items.forEach(function (v, k) {

              notify_itd = v.itd;

              var html_toast = '<div class="cursor-pointer" onclick="sensor_food_scan_info(' + v.itd + ')">';
              html_toast += '<div class="acm-fs-13">+ Predicted Dish: <b><span class="acm-mr-px-5 text-danger">' + v.food_confidence + '%</span><span>' + v.food_name + '</span></b></div>';

              html_toast += '<div class="acm-fs-13">+ Ingredients Missing:</div>';
              v.ingredients.forEach(function (v1, k1) {
                if (v1 && v1 !== '' && v1.trim() !== '') {
                  html_toast += '<div class="acm-fs-13 acm-ml-px-10">- ' + v1 + '</div>';
                }
              });

              html_toast += '</div>';
              message_from_toast('info', v.restaurant_name, html_toast, true);
            });

            // sensor_latest(notify_itd);

            if (response.data.printer) {
              page_open(acmcfs.link_base_url + '/printer?ids=' + response.data.ids.toString());
            }
          }

          if (response.data.speaker) {
            setTimeout(function () {
              speaker_play();
            }, acmcfs.timeout_quick);
          }

          // if (response.data.role) {
          //   bind_staff(response.data.role);
          // }

          acmcfs.notify_running = 0;
        })
        .catch(error => {

        });

      return false;
    }
  </script>
@endsection
