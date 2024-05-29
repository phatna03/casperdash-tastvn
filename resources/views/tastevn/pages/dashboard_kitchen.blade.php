@extends('tastevn/layouts/layoutMaster')

@section('title', 'Admin - Sensor Kitchen Optimize Performance')

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
    <div class="col-12 mb-1">
      <h4 class="position-relative w-100">
        <div class="acm-float-right">
          <button type="button" class="btn btn-sm btn-primary p-1" onclick="speaker_allow()">
            <i class="mdi mdi-speaker"></i> Test Speaker
          </button>
          <button type="button" class="btn btn-sm btn-info p-1" onclick="toggle_header()">
            <i class="mdi mdi-alert-remove"></i> Toggle Header
          </button>
        </div>

        <span class="text-muted fw-light">Admin /</span> {{$pageConfigs['item']->name}}

        <input type="hidden" name="restaurant_id" value="{{$pageConfigs['item']->id}}" />
        <input type="hidden" name="restaurant_parent_id" value="{{$pageConfigs['item']->restaurant_parent_id}}" />
      </h4>
    </div>

    <div class="col-12 mb-1">
      <div class="card">
        <div class="card-body p-0">
          <div class="row">
            <div class="col-lg-6">
              <div class="acm-border-css p-2 border-dark acm-height-450-min">
                <div class="text-center text-uppercase overflow-hidden mb-2">
                  <div class="badge bg-secondary">Food Recipe</div>
                </div>
                <div class="wrap-selected-food">
                  <div class="row">
                    <div class="col-lg-6 mb-1 d-none">
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

                    <div class="col-lg-6 mb-1 d-none">
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

            <div class="col-lg-6 wrap_food_tester">
              <div class="acm-border-css p-2 border-dark acm-height-450-min">
                <div class="text-center text-uppercase overflow-hidden mb-2">
                  <div class="badge bg-secondary">Latest Sensor Photo</div>
                </div>
                <div>
                  <div class="row">
                    <div class="col-lg-12 mb-2 d-none">
                      <div class="form-floating form-floating-outline mb-2">
                        <div class="form-control acm-wrap-selectize" id="restaurant-sensor-select">
                          <select name="sensor" class="opt_selectize d-none" onchange="sensor_selected(this)"
                                  data-placeholder="Please choose restaurant sensor..."
                          >
                            @foreach($viewer->get_sensors() as $sensor)
                              <option value="{{$sensor->id}}" @if(count($viewer->get_sensors()) == 1) selected="selected" @endif>{{$sensor->name}}</option>
                            @endforeach
                          </select>

                          Sensor Tester Optimize Performance

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
                      <div class="d-inline-block">
                        <img class="w-100" loading="lazy" src="" />
                      </div>
                    </div>

                    <div class="col-lg-12 mb-1 wrap_notify_result d-none result_photo_itd w-100">
                      <div class="d-inline-block">
                        <div class="text-dark">+ Photo ID: <b class="fw-bold"></b></div>
                      </div>

                      <div class="data_result d-inline-block"></div>
                    </div>

                    <div class="col-lg-12 mb-1 wrap_notify_result d-none result_photo_status">
                      <div class="d-inline-block">
                        <div class="text-dark">+ Status: <b class="fw-bold"></b></div>
                      </div>

                      <div class="data_result d-inline-block"></div>
                    </div>

                    <div class="col-lg-12 mb-2 wrap_notify_result d-none result_predicted_dish">
                      <div class="d-inline-block">
                        <div class="text-dark">+ Predicted Dish:</div>
                      </div>

                      <div class="data_result d-inline-block"></div>
                    </div>

                    <div class="col-lg-6 mb-1 wrap_notify_result d-none result_ingredients_found">
                      <div class="w-100">
                        <div class="text-dark">+ Ingredients Found:</div>
                      </div>

                      <div class="data_result"></div>
                    </div>

                    <div class="col-lg-6 mb-1 wrap_notify_result d-none result_ingredients_missing">
                      <div class="w-100">
                        <div class="text-dark">+ Ingredients Missing:</div>
                      </div>

                      <div class="data_result"></div>
                    </div>

                    <div class="col-lg-12 mb-1 wrap_notify_result d-none result_unknown_data">
                      <div class="d-inline-block">
                        <div class="text-dark">+ Status: <b class="fw-bold text-danger">Unknown photo information</b></div>
                      </div>

                      <div class="data_result d-inline-block"></div>
                    </div>

                    <input type="hidden" name="current_file_id" />
                    <input type="hidden" name="current_file_url" />
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

{{--  <script src="https://cdn.roboflow.com/0.2.26/roboflow.js"></script>--}}
  <script src="{{url('custom/library/roboflow/roboflow.js')}}"></script>

  <script type="text/javascript">
    $(document).ready(function() {

      if (notify_realtime) {
        clearInterval(notify_realtime);
      }

      toggle_header();

      // sensor_checker();

      setInterval(function () {
        if (sys_ready) {
          sensor_checker();
        }
      }, 2000);

    });

    var sys_running = 0;
    var sys_ready = !parseInt(acmcfs.rbf_js);

    if (parseInt(acmcfs.rbf_js)) {
      //roboflow init
      roboflow.auth({
        publishable_key: "rf_3DtUFXV7oiSXMh2VkXK8d0EHcRD2"
      });

      async function rbf_load_model() {
        var model = await roboflow.load({
          model: "missing-dish-ingredients",
          version: 29
        });

        model.configure({
          threshold: 0.3,
          overlap: 0.6,
          max_objects: 100
        });

        acmcfs.rbf_model = model;

        return model;
      }

      //roboflow check ready
      rbf_load_model().then(model => {
        console.log("==============================================");
        console.log("RBF load success......");

        // Do something with the model
        console.log(model.getMetadata());
        console.log(model.getConfiguration());
        console.log('ok...');

        sys_ready = 1;

      }).catch(error => {
        console.log("==============================================");
        console.error('Error loading model:', error);
      });
    }

    function food_predict_by_datas(item_id, datas) {
      var wrap = $('.wrap-selected-food');

      $('.result_photo_status .data_result').empty()
        .append('<div class="badge bg-success fw-bold acm-ml-px-10 acm-fs-15">predicting...</div>');

      axios.post('/admin/kitchen/predict', {
        item: item_id,
        restaurant_id: '{{$pageConfigs['item']->id}}',
        datas: datas,
      })
        .then(response => {

          //show data
          food_datas(response.data.datas);

          //notify
          if (response.data.notifys && response.data.notifys.length) {

            response.data.notifys.forEach(function (v, k) {

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


            if (response.data.printer) {
              page_open(acmcfs.link_base_url + '/printer?ids=' + response.data.notify_ids.toString());
            }
          }

          if (response.data.speaker) {
            setTimeout(function () {
              speaker_play();
            }, 888);
          }

          //end
          sys_ready = 1;

        })
        .catch(error => {
          console.log(error);
        });

      return false;
    }

    function food_predict_by_api(item_id) {
      var wrap = $('.wrap-selected-food');

      $('.result_photo_status .data_result').empty()
        .append('<div class="badge bg-success fw-bold acm-ml-px-10 acm-fs-15">predicting...</div>');

      axios.post('/admin/kitchen/predict', {
        item: item_id,
        restaurant_id: '{{$pageConfigs['item']->id}}',
        type: 'api',
      })
        .then(response => {

          //show data
          food_datas(response.data.datas);

          //notify
          if (response.data.notifys && response.data.notifys.length) {

            response.data.notifys.forEach(function (v, k) {

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


            if (response.data.printer) {
              page_open(acmcfs.link_base_url + '/printer?ids=' + response.data.notify_ids.toString());
            }
          }

          if (response.data.speaker) {
            setTimeout(function () {
              speaker_play();
            }, 888);
          }

          //end
          sys_ready = 1;

        })
        .catch(error => {
          console.log(error);
        });

      return false;
    }

    function sensor_checker() {

      if (sys_running) {
        return false;
      }
      sys_running = 1;

      axios.post('/admin/kitchen/checker', {
        item: '{{$pageConfigs['item']->id}}',
      })
        .then(response => {

          var current_file_id = $('.wrap_food_tester input[name=current_file_id]').val();
          var current_file_url = $('.wrap_food_tester input[name=current_file_url]').val();

          if (response.data.file && response.data.file != '' && current_file_url != response.data.file) {

            $('.wrap_food_tester input[name=current_file_id]').val(response.data.file_id);

            $('.wrap_notify_result').addClass('d-none');

            $('.wrap_food_tester input[name=current_file_url]').val(response.data.file);

            $('.result_photo_sensor img').attr('src', response.data.file_url);
            $('.result_photo_sensor').removeClass('d-none');

            $('.result_photo_itd .data_result').empty()
              .append('<div class="text-danger fw-bold acm-ml-px-10 acm-fs-15">' + response.data.file_id + '</div>');
            $('.result_photo_itd').removeClass('d-none');

            $('.result_photo_status .data_result').empty()
              .append('<div class="badge bg-info fw-bold acm-ml-px-10 acm-fs-15">checking...</div>');
            $('.result_photo_status').removeClass('d-none');

            //show data
            if (response.data.datas && (response.data.datas != '' || response.data.datas != '[]')) {
              food_datas(response.data.datas);
            }

            if (sys_ready && response.data.status == 'new') {
              sys_ready = 0;

              if (parseInt(acmcfs.rbf_js)) {
                var photo_img = new Image();
                photo_img.crossOrigin = "anonymous";
                photo_img.src = response.data.file_url;

                console.log("==============================================");
                console.log("RBF start......");

                if (acmcfs.rbf_model) {

                  setTimeout(function () {
                    acmcfs.rbf_model.detect(photo_img).then(function (predictions) {
                      console.log("Photo URL: ", response.data.file_url);
                      console.log("Predictions: ", predictions);
                      food_predict_by_datas(response.data.file_id, predictions);

                    });
                  }, 888);
                }
              } else {
                food_predict_by_api(response.data.file_id);
              }
            }
          }
        })
        .catch(error => {
          console.log(error);

          // if (error.response.data && Object.values(error.response.data).length) {
          //   Object.values(error.response.data).forEach(function (v, k) {
          //     message_from_toast('error', acmcfs.message_title_error, v);
          //   });
          // }
        })
        .then(res => {
          sys_running = 0;
        });

      return false;
    }

    function food_datas(datas) {
      var wrap = $('.wrap-selected-food');

      if (datas.food_id) {

        //standard
        wrap.find('.food-name').empty().text(datas.food_name);
        wrap.find('.food-photo').attr('src', datas.food_photo);
        wrap.find('.wrap-ingredients').empty().append(datas.html_info);

        //sensor
        $('.result_photo_status .data_result').empty()
          .append('<div class="badge bg-primary fw-bold acm-ml-px-10 acm-fs-15">checked</div>');

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
            html += '<div class="text-danger acm-ml-px-10">- <b class="text-danger acm-mr-px-5">' + v.quantity + '</b> ' + v.name + '</div>';
          });
        }
        if (html && html != '') {
          $('.result_ingredients_missing .data_result').empty().append(html);
          $('.result_ingredients_missing').removeClass('d-none');
        }

        //ingredients_found
        html = '';
        if (datas.ingredients_found.length) {
          datas.ingredients_found.forEach(function (v, k) {
            html += '<div class="text-dark acm-ml-px-10">- <b class="text-danger acm-mr-px-5">' + v.quantity + '</b> ' + v.name + '</div>';
          });
        }
        if (html && html != '') {
          $('.result_ingredients_found .data_result').empty().append(html);
          $('.result_ingredients_found').removeClass('d-none');
        }
      }
      else {

        wrap.find('.food-photo').attr('src', acmcfs.link_food_no_photo);
        wrap.find('.wrap-ingredients').empty();

        $('.result_photo_status .data_result').empty()
          .append('<div class="badge bg-danger fw-bold acm-ml-px-10 acm-fs-15">No dish found</div>');
      }
    }

  </script>
@endsection
