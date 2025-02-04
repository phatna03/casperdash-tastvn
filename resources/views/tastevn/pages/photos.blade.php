@extends('tastevn/layouts/layoutMaster')

@section('title', 'Admin Photos')

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

  <script src="{{url('custom/library/lightbox/lc_lightbox.lite.js')}}"></script>
  <script src="{{url('custom/library/lightbox/alloy_finger.min.js')}}"></script>
@endsection

@section('content')

  <h4 class="mb-2"><span class="text-muted fw-light">Admin /</span> Album Photos</h4>

  <div class="card" id="wrap-datas">
    <div class="card-header border-bottom wrap-search-form">
      <h5 class="card-title">Search Conditions</h5>

      <form onsubmit="event.preventDefault(); return form_photos_filter();">
        <div class="d-flex justify-content-between align-items-center row py-1 gap-3 gap-md-0">
          <div class="col-md-6 mb-2">
            <div class="form-floating form-floating-outline">
              <div class="form-control acm-wrap-selectize" id="filter-search-restaurants">
                <select class="ajx_selectize multi_selectize"
                        data-value="restaurant"
                        name="restaurants" multiple onchange="form_photos_filter()">
                  <option value="">All</option>
                </select>
              </div>
              <label for="filter-search-restaurants">Restaurants</label>
            </div>
          </div>
          <div class="col-md-6 mb-2">
            <div class="form-floating form-floating-outline">
              <input type="text" class="form-control text-center date_time_picker" name="time_upload"
                     id="filter-search-time-upload" autocomplete="off" data-value="last_and_current_day"
                     onchange="form_photos_filter()" />
              <label for="filter-search-time-upload">Time upload</label>
            </div>
          </div>
        </div>
      </form>
    </div>

    <div class="position-relative clearfix p-2 m-1">
      <div class="row" id="wrap-data-body"></div>

      <div class="row mt-2 load_more">
        <div class="col-12 m-auto text-center">
          <button type="button" class="btn btn-sm btn-primary" onclick="form_photos_filter(true)">Load More</button>
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

    function form_photos_filter(load_more = false) {
      var datas = $('#wrap-datas');
      var form_search = datas.find('form');
      var form_datas = datas.find('#wrap-data-body');

      var existed = [];
      if (load_more) {
        datas.find('.load_more').addClass('d-none');

        if (form_datas.find('.item_photo').length) {
          form_datas.find('.item_photo').each(function (k, v) {
            existed.push($(v).attr('data-itd'));
          });
        }
      }

      axios.post('/admin/photo/get', {
        restaurants: form_search.find('select[name=restaurants]').val(),
        time_upload: form_search.find('input[name=time_upload]').val(),
        existed: existed,
      })
        .then(response => {
          // console.log(response);
          datas.find('.load_more').addClass('d-none');

          if (response.data.html && response.data.html !== '') {

            if (load_more) {
              form_datas.append(response.data.html);
            } else {
              form_datas.empty().append(response.data.html);
            }

            datas.find('.load_more').removeClass('d-none');

            lc_lightbox('.acm-lightbox-photo', {
              wrap_class: 'lcl_fade_oc',
              thumb_attr: 'data-lcl-thumb',
            });

          } else {

            if (!load_more) {
              form_datas.empty().append('<div class="col-12"><span class="badge bg-info">No photo found</span></div>');
            }
          }

        })
        .catch(error => {
          if (error.response.data && Object.values(error.response.data).length) {
            Object.values(error.response.data).forEach(function (v, k) {
              message_from_toast('error', acmcfs.message_title_error, v);
            });
          }
        });

      return false;
    }
  </script>
@endsection
