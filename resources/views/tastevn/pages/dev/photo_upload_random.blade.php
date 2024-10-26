@extends('tastevn/layouts/layoutMaster')

@section('title', 'Developer...')

@section('content')
  <div class="card">
    <div class="card-header">
      <h5 class="card-title mb-0">Photo Random Upload</h5>
    </div>

    <div class="card-body">
      <div class="nav-align-left">
        <div class="row">
          <div class="col-lg-12 mb-1">
            <div class="acm-float-right w-px-200">
              <button type="button" onclick="sensor_upload_all(this)"
                      class="btn btn-sm btn-primary w-100 d-flex justify-content-between">
                <span class="text-uppercase">all sensors</span>
                <i class="mdi mdi-food acm-mr-px-5"></i>
              </button>
            </div>
          </div>

          @foreach($pageConfigs['sensors'] as $sensor)
            <div class="col-lg-12 mb-1 acm-clearfix">
              <button type="button" onclick="page_open('{{url('admin/sensor/info/' . $sensor->id . '?debug=1')}}')"
                      class="btn btn-sm btn-info acm-float-left acm-mr-px-5">
                <i class="mdi mdi-information"></i>
              </button>
              <button type="button" onclick="page_open('{{url('admin/kitchen/' . $sensor->id)}}')"
                      class="btn btn-sm btn-success acm-float-left acm-mr-px-5">
                <i class="mdi mdi-chef-hat"></i>
              </button>

              <button type="button" onclick="sensor_upload(this)"
                      data-sensor="{{$sensor->id}}"
                      class="btn btn-sm btn-primary overflow-hidden">
                <i class="mdi mdi-food acm-mr-px-10"></i>
                <span>{{$sensor->name}}</span>
              </button>
            </div>
          @endforeach
        </div>
      </div>
    </div>
  </div>
@endsection

@section('js_end')
  <script type="text/javascript">
    var $ = jQuery.noConflict();
    $(document).ready(function() {



    });

    function sensor_upload(ele) {
      var bind = $(ele);

      if (bind.hasClass('data_loading')) {
        return false;
      }

      bind.addClass('data_loading');
      bind.removeClass('btn-primary').addClass('btn-secondary');
      bind.find('i').removeClass('mdi-food').addClass('mdi-reload');

      axios.post('/admin/dev/photo/upload/random/sensor', {
        sensor: bind.attr('data-sensor'),
      })
        .then(response => {

          message_from_toast('success', acmcfs.message_title_success, 'Uploaded file success...');

          bind.addClass('btn-success').removeClass('btn-secondary');
          bind.find('i').addClass('mdi-check').removeClass('mdi-reload');

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

          setTimeout(function () {
            bind.removeClass('data_loading');

            bind.addClass('btn-primary').removeClass('btn-success');
            bind.find('i').removeClass('mdi-check').addClass('mdi-food');
          }, 2000);
        });

      return false;
    }

    function sensor_upload_all(ele) {
      var bind = $(ele);

      if (bind.hasClass('data_loading')) {
        return false;
      }

      bind.addClass('data_loading');
      bind.removeClass('btn-primary').addClass('btn-secondary');
      bind.find('i').removeClass('mdi-food').addClass('mdi-reload');

      axios.post('/admin/dev/photo/upload/random/sensor', {

      })
        .then(response => {

          message_from_toast('success', acmcfs.message_title_success, 'Uploaded file success...');

          bind.addClass('btn-success').removeClass('btn-secondary');
          bind.find('i').addClass('mdi-check').removeClass('mdi-reload');

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

          setTimeout(function () {
            bind.removeClass('data_loading');

            bind.addClass('btn-primary').removeClass('btn-success');
            bind.find('i').removeClass('mdi-check').addClass('mdi-food');
          }, 2000);
        });

      return false;
    }
  </script>
@endsection
