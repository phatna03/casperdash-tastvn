@extends('tastevn/layouts/layoutMaster')

@section('title', 'Developer...')

@section('vendor-style')
  <link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css')}}">
  <link rel="stylesheet" href="{{asset('assets/vendor/libs/flatpickr/flatpickr.css')}}" />
  <link rel="stylesheet" href="{{asset('assets/vendor/libs/bootstrap-datepicker/bootstrap-datepicker.css')}}" />
  <link rel="stylesheet" href="{{asset('assets/vendor/libs/bootstrap-daterangepicker/bootstrap-daterangepicker.css')}}" />
  <link rel="stylesheet" href="{{asset('assets/vendor/libs/jquery-timepicker/jquery-timepicker.css')}}" />
  <link rel="stylesheet" href="{{asset('assets/vendor/libs/pickr/pickr-themes.css')}}" />

  <link rel="stylesheet" href="{{url('custom/library/lightbox/lc_lightbox.css')}}" />
  <link rel="stylesheet" href="{{url('custom/library/lightbox/minimal.css')}}" />
@endsection

@section('vendor-script')
  <script src="{{asset('assets/vendor/libs/moment/moment.js')}}"></script>
  <script src="{{asset('assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js')}}"></script>
  <script src="{{asset('assets/vendor/libs/flatpickr/flatpickr.js')}}"></script>
  <script src="{{asset('assets/vendor/libs/bootstrap-datepicker/bootstrap-datepicker.js')}}"></script>
  <script src="{{asset('assets/vendor/libs/bootstrap-daterangepicker/bootstrap-daterangepicker.js')}}"></script>
  <script src="{{asset('assets/vendor/libs/jquery-timepicker/jquery-timepicker.js')}}"></script>
  <script src="{{asset('assets/vendor/libs/pickr/pickr.js')}}"></script>
  <script type="text/javascript" src="https://cdn.datatables.net/plug-ins/1.10.16/sorting/datetime-moment.js"></script>
  <script type="text/javascript" src="https://cdn.datatables.net/plug-ins/1.10.21/dataRender/datetime.js"></script>
  <script type="text/javascript" src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.bundle.min.js"></script>

  <script src="{{url('custom/library/lightbox/lc_lightbox.lite.js?v=101')}}"></script>
  <script src="{{url('custom/library/lightbox/alloy_finger.min.js')}}"></script>
@endsection

@section('content')
  <div class="card">
    <div class="card-header">
      <h5 class="card-title mb-0">Developer...</h5>
    </div>

    <div class="card-body">
      <div class="nav-align-left">
        <ul class="nav nav-tabs" role="tablist">
          <li class="nav-item">
            <button type="button" class="nav-link active" role="tab"
                    data-bs-toggle="tab" data-bs-target="#navs-left-photos"
                    aria-controls="navs-left-photos" aria-selected="true">Photos</button>
          </li>
          <li class="nav-item">
            <button type="button" class="nav-link" role="tab"
                    data-bs-toggle="tab" data-bs-target="#navs-left-profile"
                    aria-controls="navs-left-profile" aria-selected="false">Reports</button>
          </li>
          <li class="nav-item">
            <button type="button" class="nav-link" role="tab"
                    data-bs-toggle="tab" data-bs-target="#navs-left-messages"
                    aria-controls="navs-left-messages" aria-selected="false">Tester</button>
          </li>
        </ul>
        <div class="tab-content">
          <div class="tab-pane fade form_tab show active" id="navs-left-photos">
            <div class="acm-clearfix mb-2 form_search">
              <form onsubmit="event.preventDefault(); return form_photos_filter(this);">
                <div class="row">
                  <div class="col-lg-3">
                    <div class="form-floating form-floating-outline mb-2">
                      <div class="form-control acm-wrap-selectize" id="filter-search-restaurant">
                        <select class="ajx_selectize" data-value="restaurant_parent" name="restaurant" required>
                          <option value="">Choose restaurant...</option>
                        </select>
                      </div>
                      <label for="filter-search-restaurant">Restaurant</label>
                    </div>
                  </div>

                  <div class="col-lg-3">
                    <div class="form-floating form-floating-outline mb-2">
                      <input type="text" class="form-control text-center date_picker" name="dates" required
                             id="filter-search-date" autocomplete="off" data-value="last_and_current_day" />
                      <label for="filter-search-date">Dates</label>
                    </div>
                  </div>

                  <div class="col-lg-6 acm-text-right">
                    <button type="submit" class="btn btn-primary">Search</button>
                  </div>
                </div>
              </form>
            </div>

            <div class="acm-clearfix mb-2 form_datas">

            </div>
          </div>
          <div class="tab-pane fade" id="navs-left-profile">
            <p>
              Donut dragée jelly pie halvah. Danish gingerbread bonbon cookie wafer candy oat cake ice cream. Gummies
              halvah tootsie roll muffin biscuit icing dessert gingerbread. Pastry ice cream cheesecake fruitcake.
            </p>
            <p class="mb-0">
              Jelly-o jelly beans icing pastry cake cake lemon drops. Muffin muffin pie tiramisu halvah cotton candy
              liquorice caramels.
            </p>
          </div>
          <div class="tab-pane fade" id="navs-left-messages">
            <p>
              Oat cake chupa chups dragée donut toffee. Sweet cotton candy jelly beans macaroon gummies cupcake gummi
              bears cake chocolate.
            </p>
            <p class="mb-0">
              Cake chocolate bar cotton candy apple pie tootsie roll ice cream apple pie brownie cake. Sweet roll icing
              sesame snaps caramels danish toffee. Brownie biscuit dessert dessert. Pudding jelly jelly-o tart brownie
              jelly.
            </p>
          </div>
        </div>
      </div>
    </div>
  </div>
@endsection

@section('js_end')
  <script type="text/javascript">
    var $ = jQuery.noConflict();
    $(document).ready(function() {

      // toggle_header();

      if (notify_realtime) {
        clearInterval(notify_realtime);
      }

    });

    function form_photos_filter(ele) {
      var form_parent = $(ele).closest('.form_tab');
      var form_search = form_parent.find('.form_search');
      var form_datas = form_parent.find('.form_datas');


      axios.post('/admin/dev/photo/check', {
        restaurant: form_search.find('select[name=restaurant]').val(),
        dates: form_search.find('input[name=dates]').val(),
      })
        .then(response => {

          var html = '';

          if (response.data.datas.length && !$('#wrap_photo_sensor_' + response.data.restaurant_id).length) {
            html = '<div class="acm-clearfix mb-2" id="wrap_photo_sensor_' + response.data.restaurant_id + '">';

            response.data.datas.forEach(function (v1, k1) {
              // console.log(v1);
              var html_dates = '';

              if (v1.dates.length) {
                v1.dates.forEach(function (v2, k2) {
                  // console.log(v2);
                  // console.log(typeof(v2.hours));
                  // console.log(v2.hours);
                  var html_hours = '';
                  var hours = Object.values(v2.hours);

                  if (hours.length) {
                    hours.forEach(function (v3, k3) {
                      // console.log(v3);

                      html_hours += '<div class="acm-clearfix mb-1 acm-ml-px-10 photo_sensor_date_hour" id="sensor_date_hour_' + v1.sensor_id + '_' + v2.date + '_' + v3 + '" ' +
                        ' data-sensor_id="' + v1.sensor_id + '" ' +
                        ' data-date="' + v2.date + '" ' +
                        ' data-folder="' + v2.folder + '" ' +
                        ' data-hour="' + v3 + '" ' +
                        ' >' +
                        '<div class="mb-1 photo_sensor_date_hour_datas">' +
                        '<button type="button" onclick="photo_sensor_date_hour_get(this)" class="btn btn-sm btn-info btn_get p-2 acm-mr-px-10">' +
                        '<span class="acm-mr-px-10">' + v3 + '</span><span><i class="mdi mdi-download"></i></span>' +
                        '</button>' +
                        '<button type="button" onclick="photo_sensor_date_hour_sync(this)" class="btn btn-sm btn-info btn_sync p-2">' +
                        '<span class="acm-mr-px-10">' + v3 + '</span><span><i class="mdi mdi-sync"></i></span>' +
                        '</button>' +
                        '</div>' +
                        '</div>';
                    });
                  }

                  html_dates += '<div class="acm-clearfix mb-1 acm-ml-px-10">' +
                    '<div class="mb-1"><span class="badge bg-dark">' + v2.date + '</span></div>' +
                    html_hours +
                    '</div>';
                });
              }

              html += '<div class="acm-clearfix mb-2" data-sensor_id="' + v1.sensor_id + '">' +
                '<div class="mb-1"><span class="badge bg-primary">' + v1.sensor_name + '</span></div>' +
                html_dates +
                '</div>';
            });

            html += '</div>';
          }

          form_datas.append(html);

        })
        .catch(error => {
          console.log(error);

          if (error.response.data && Object.values(error.response.data).length) {
            Object.values(error.response.data).forEach(function (v, k) {
              message_from_toast('error', acmcfs.message_title_error, v);
            });
          }
        })
        .then(() => {

        });

      return false;
    }

    function photo_sensor_date_hour_get(ele) {
      var wrap = $(ele).closest('.photo_sensor_date_hour');
      var parent = wrap.find('.photo_sensor_date_hour_datas');

      $(ele).removeClass('btn-info').addClass('btn-secondary');

      axios.post('/admin/dev/photo/check/hour/get', {
        sensor: wrap.attr('data-sensor_id'),
        date: wrap.attr('data-date'),
        folder: wrap.attr('data-folder'),
        hour: wrap.attr('data-hour'),
      })
        .then(response => {

          if (response.data.status) {

            $(ele).removeClass('btn-secondary').addClass('btn-success');
            parent.find('.btn_sync')[0].click();

          } else {

            $(ele).removeClass('btn-secondary').addClass('btn-danger');
          }

        })
        .catch(error => {
          console.log(error);

          if (error.response.data && Object.values(error.response.data).length) {
            Object.values(error.response.data).forEach(function (v, k) {
              message_from_toast('error', acmcfs.message_title_error, v);
            });
          }

        })
        .then(() => {

        });

      return false;
    }

    function photo_sensor_date_hour_sync(ele) {
      var wrap = $(ele).closest('.photo_sensor_date_hour');
      var parent = wrap.find('.photo_sensor_date_hour_datas');

      $(ele).removeClass('btn-info').addClass('btn-secondary');

      axios.post('/admin/dev/photo/check/hour/sync', {
        sensor: wrap.attr('data-sensor_id'),
        date: wrap.attr('data-date'),
        folder: wrap.attr('data-folder'),
        hour: wrap.attr('data-hour'),
      })
        .then(response => {

          if (response.data.status) {

            $(ele).removeClass('btn-secondary').addClass('btn-success');

          } else {

            $(ele).removeClass('btn-secondary').addClass('btn-danger');
          }

        })
        .catch(error => {
          console.log(error);

          if (error.response.data && Object.values(error.response.data).length) {
            Object.values(error.response.data).forEach(function (v, k) {
              message_from_toast('error', acmcfs.message_title_error, v);
            });
          }

        })
        .then(() => {

        });

      return false;
    }
  </script>
@endsection
