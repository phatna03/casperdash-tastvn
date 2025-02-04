@extends('tastevn/layouts/layoutMaster')

@section('title', 'Admin - Sensor: ' . $pageConfigs['item']->name)

@section('vendor-style')
  <link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css')}}">
  <link rel="stylesheet" href="{{asset('assets/vendor/libs/flatpickr/flatpickr.css')}}"/>
  <link rel="stylesheet" href="{{asset('assets/vendor/libs/bootstrap-datepicker/bootstrap-datepicker.css')}}"/>
  <link rel="stylesheet"
        href="{{asset('assets/vendor/libs/bootstrap-daterangepicker/bootstrap-daterangepicker.css')}}"/>
  <link rel="stylesheet" href="{{asset('assets/vendor/libs/jquery-timepicker/jquery-timepicker.css')}}"/>
  <link rel="stylesheet" href="{{asset('assets/vendor/libs/pickr/pickr-themes.css')}}"/>
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
  <script type="text/javascript"
          src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.bundle.min.js"></script>
@endsection

@section('content')

  <h4 class="mb-2"><span class="text-muted fw-light">Admin /</span> Sensor: {{$pageConfigs['item']->name}}</h4>
  <input type="hidden" name="current_restaurant" value="{{$pageConfigs['item']->id}}"/>
  <input type="hidden" name="debug" value="{{$pageConfigs['debug']}}"/>

  <div class="row g-4 mb-4">
    <div class="col-lg-12 wrap-stats" id="wrap-stats-total">
      <div class="card h-100">
        <div class="card-header">
          <div class="d-flex justify-content-between">
            <h4 class="mb-2">Total dishes scanned</h4>
            <div class="dropdown">
              <button class="btn btn-primary p-1" type="button" data-bs-toggle="dropdown" aria-haspopup="true"
                      aria-expanded="false">
                <i class="mdi mdi-filter mdi-18px"></i>
              </button>
              <div class="dropdown-menu dropdown-menu-end">
                <div class="w-px-400 p-2">
                  <div class="form-floating form-floating-outline">
                    <input type="text" class="form-control text-center date_time_picker" name="search_time"
                           autocomplete="off"
                           onchange="sensor_stats()"
                           data-value="last_and_current_day"
                    />
                    <label>Date Time Range</label>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="d-flex justify-content-between">
            <div class="d-flex align-items-end mt-2">
              <h4 class="mb-0 me-2 fnumber stats-total-found-count"></h4>
              <small class="stats-today-found">(today: <b class="text-success fnumber"></b>)</small>
            </div>

            <div class="mt-2 wrap-search-condition d-none">
              <div class="mb-0">
                <div class="d-inline-block search-time"></div>
                <div class="d-inline-block">
                  <button type="button" class="btn btn-danger btn-sm p-1" onclick="sensor_stats_clear(this)">
                    <i class="mdi mdi-trash-can"></i>
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="card-body d-flex justify-content-between flex-wrap gap-3">
          <div class="d-flex gap-3">
            <div class="btn-group">
              <div class="avatar cursor-pointer" data-bs-toggle="dropdown" aria-expanded="false">
                <div class="avatar-initial bg-label-info rounded">
                  <i class="mdi mdi-list-box mdi-24px"></i>
                </div>
              </div>
              <ul class="dropdown-menu acm-overflow-y-auto acm-height-300-max stats-food-category-list"></ul>
            </div>

            <div class="card-info">
              <div>
                <h4 class="mb-0 d-inline-block stats-food-category-count"></h4>
                <small class="text-danger fw-bold d-inline-block stats-food-category-percent"></small>
              </div>
              <small>Total Categories Error</small>
            </div>
          </div>
          <div class="d-flex gap-3">
            <div class="btn-group">
              <div class="avatar cursor-pointer" data-bs-toggle="dropdown" aria-expanded="false">
                <div class="avatar-initial bg-label-primary rounded">
                  <i class="mdi mdi-food mdi-24px"></i>
                </div>
              </div>
              <ul class="dropdown-menu acm-overflow-y-auto acm-height-300-max stats-food-list"></ul>
            </div>
            <div class="card-info">
              <div>
                <h4 class="mb-0 d-inline-block stats-food-count"></h4>
                <small class="text-danger fw-bold d-inline-block stats-food-percent"></small>
              </div>
              <small>Total Dishes Error</small>
            </div>
          </div>
          <div class="d-flex gap-3">
            <div class="btn-group">
              <div class="avatar cursor-pointer" data-bs-toggle="dropdown" aria-expanded="false">
                <div class="avatar-initial bg-label-danger rounded">
                  <i class="mdi mdi-food-off mdi-24px"></i>
                </div>
              </div>
              <ul class="dropdown-menu acm-overflow-y-auto acm-height-300-max stats-ingredients-missing-list"></ul>
            </div>
            <div class="card-info">
              <div>
                <h4 class="mb-0 d-inline-block stats-ingredients-missing-count"></h4>
                <small class="text-danger fw-bold d-inline-block stats-ingredients-missing-percent"></small>
              </div>
              <small>Total Ingredients Missing</small>
            </div>
          </div>
          <div class="d-flex gap-3">
            <div class="btn-group">
              <div class="avatar cursor-pointer" data-bs-toggle="dropdown" aria-expanded="false">
                <div class="avatar-initial bg-label-warning rounded">
                  <i class="mdi mdi-clock mdi-24px"></i>
                </div>
              </div>
              <ul class="dropdown-menu acm-overflow-y-auto acm-height-300-max stats-time-frames-list"></ul>
            </div>
            <div class="card-info">
              <div>
                <h4 class="mb-0 d-inline-block stats-time-frames-count"></h4>
                <small class="text-danger fw-bold d-inline-block d-none"></small>
              </div>
              <small>Time Frames Error</small>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="nav-align-top mb-4">
    <ul class="nav nav-pills nav-fill mb-1" role="tablist">
      <li class="nav-item">
        <button type="button" class="nav-link active" role="tab"
                data-bs-toggle="tab" data-bs-target="#datatable-listing-scan"
                aria-controls="datatable-listing-scan" aria-selected="true">
          <i class="tf-icons mdi mdi-view-list me-1"></i> List of scanned
          <span class="badge rounded-pill badge-center h-px-20 w-px-20 bg-danger ms-1 d-none">3</span>
        </button>
      </li>
      <li class="nav-item">
        <button type="button" class="nav-link" role="tab"
                data-bs-toggle="tab" data-bs-target="#datatable-listing-error"
                aria-controls="datatable-listing-error" aria-selected="false">
          <i class="tf-icons mdi mdi-view-list me-1"></i> List of errors
        </button>
      </li>
    </ul>
    <div class="tab-content mb-4">
      <div class="tab-pane fade show active" id="datatable-listing-scan" role="tabpanel">
        <div class="wrap-search-form">
          <h5 class="card-title">Search Conditions</h5>
          <form onsubmit="event.preventDefault(); return datatable_listing_scan_refresh();">
            <div class="d-flex justify-content-between align-items-center row py-1 gap-3 gap-md-0">
              <div class="col-md-6 mb-2">
                <div class="form-floating form-floating-outline wrap-select-food-category">
                  <div class="form-control acm-wrap-selectize" id="scan-search-food-category">
                    <select name="categories" multiple onchange="sensor_search_food_scan(this)">
                      <option value="">All</option>
                    </select>
                  </div>
                  <label for="scan-search-food-category">Dish Categories</label>
                </div>
              </div>
              <div class="col-md-6 mb-2">
                <div class="form-floating form-floating-outline wrap-select-food">
                  <div class="form-control acm-wrap-selectize" id="scan-search-food">
                    <select name="foods" multiple onchange="sensor_search_food_scan(this)">
                      <option value="">All</option>
                    </select>
                  </div>
                  <label for="scan-search-food">Dishes</label>
                </div>
              </div>
              <div class="col-md-6 mb-2">
                <div class="form-floating form-floating-outline">
                  <input type="text" class="form-control text-center date_time_picker" name="time_upload"
                         id="scan-search-time-upload" autocomplete="off" data-value="last_and_current_day"
                         onchange="sensor_search_food_scan(this)"/>
                  <label for="scan-search-time-upload">Time Upload</label>
                </div>
              </div>
              <div class="col-md-6 mb-2 d-none">
                <div class="form-floating form-floating-outline">
                  <input type="text" class="form-control text-center date_time_picker" name="time_scan"
                         id="scan-search-time-scan" autocomplete="off" data-value="last_and_current_day"
                         onchange="sensor_search_food_scan(this)"/>
                  <label for="scan-search-time-scan">Time scanned</label>
                </div>
              </div>
              <div class="col-md-6 mb-2">
                <div class="form-floating form-floating-outline">
                  <div class="form-control acm-wrap-selectize" id="scan-search-status">
                    <select name="statuses" class="opt_selectize multi_selectize" multiple onchange="sensor_search_food_scan(this)">
                      <option value="">All</option>
                      <option value="checked" selected="selected">checked</option>
                      <option value="failed">no data</option>
                      <option value="edited" selected="selected">edited</option>
                    </select>
                  </div>
                  <label for="scan-search-status">Statuses</label>
                </div>
              </div>
              <div class="col-md-6 mb-2">
                <div class="form-floating form-floating-outline">
                  <div class="form-control acm-wrap-selectize" id="scan-search-missing">
                    <select name="missing" class="opt_selectize" onchange="sensor_search_food_scan(this)">
                      <option value="">All dishes</option>
                      <option value="yes">Dish with missing ingredients only</option>
                      <option value="no">Dish has all the ingredients</option>
                    </select>
                  </div>
                  <label for="scan-search-missing">Type</label>
                </div>
              </div>
              <div class="col-md-6 mb-2">
                <div class="form-floating form-floating-outline wrap-select-users">
                  <div class="form-control acm-wrap-selectize" id="scan-search-users">
                    <select name="users" multiple onchange="sensor_search_food_scan(this)"
                            data-value="user" class="ajx_selectize multi_selectize"
                    >
                      <option value="">All</option>
                    </select>
                  </div>
                  <label for="scan-search-users">Commentators</label>
                </div>
              </div>
            </div>
          </form>
        </div>

        <div class="table-responsive">
          <table class="table table-hover">
            <thead class="table-light">
            <tr>
              <th class="acm-th-first"></th>
              <th>Status</th>
              <th>Dish</th>
              <th>Confidence</th>
              <th>Ingredients missing</th>
              <th>Time upload</th>
{{--              <th>Time scanned</th>--}}
              <th>Note</th>
              <th class="d-none"></th>
              <th class="d-none"></th>
            </tr>
            </thead>
          </table>
        </div>
      </div>

      <div class="tab-pane fade" id="datatable-listing-error" role="tabpanel">
        <div class="wrap-search-form">
          <h5 class="card-title">Search Conditions</h5>
          <form onsubmit="event.preventDefault(); return datatable_listing_error_refresh();">
            <div class="d-flex justify-content-between align-items-center row py-1 gap-3 gap-md-0">
              <div class="col-md-6 mb-2">
                <div class="form-floating form-floating-outline wrap-select-food-category">
                  <div class="form-control acm-wrap-selectize" id="error-search-food-category">
                    <select name="categories" multiple
                            onchange="sensor_search_food_scan_error(this)">
                      <option value="">All</option>
                    </select>
                  </div>
                  <label for="error-search-food-category">Dish Categories</label>
                </div>
              </div>
              <div class="col-md-6 mb-2">
                <div class="form-floating form-floating-outline wrap-select-food">
                  <div class="form-control acm-wrap-selectize" id="error-search-food">
                    <select name="foods" multiple onchange="sensor_search_food_scan_error(this)">
                      <option value="">All</option>
                    </select>
                  </div>
                  <label for="error-search-food">Dishes</label>
                </div>
              </div>
              <div class="col-md-6 mb-2">
                <div class="form-floating form-floating-outline">
                  <input type="text" class="form-control text-center date_time_picker" name="time_upload"
                         id="error-search-time-upload" autocomplete="off" data-value="last_and_current_day"
                         onchange="sensor_search_food_scan_error(this)"/>
                  <label for="error-search-time-upload">Time Upload</label>
                </div>
              </div>
              <div class="col-md-6 mb-2 d-none">
                <div class="form-floating form-floating-outline">
                  <input type="text" class="form-control text-center date_time_picker" name="time_scan"
                         id="error-search-time-scan" autocomplete="off" data-value="last_and_current_day"
                         onchange="sensor_search_food_scan_error(this)"/>
                  <label for="error-search-time-scan">Time scanned</label>
                </div>
              </div>
            </div>
          </form>
        </div>

        <div class="table-responsive">
          <table class="table table-hover">
            <thead class="table-light">
            <tr>
              <th class="acm-th-first"></th>
{{--              <th>Category</th>--}}
              <th>Dish</th>
              <th>Ingredients missing</th>
              <th>Total errors</th>
            </tr>
            </thead>
          </table>
        </div>
      </div>
    </div>
  </div>

  <!-- modal confirm to retrain roboflow -->
  <div class="modal animate__animated animate__rollIn" id="modal_roboflow_retraining" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title">Roboflow Confirmation?</h4>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="row">
            <div class="col mb-12 mt-2">
              <div class="alert alert-danger">Are you sure you want to re-training Roboflow with current search
                results?
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <div class="wrap-btns">
            @include('tastevn.htmls.form_button_loading')
            <button type="button" class="btn btn-primary btn-ok btn-submit acm-float-right" onclick="sensor_retraining(this)">Submit</button>
            <button type="button" class="btn btn-outline-secondary btn-ok btn-cancel" data-bs-dismiss="modal">Cancel</button>
          </div>
        </div>
      </div>
    </div>
  </div>
  <!-- modal show scan error -->
  <div class="modal animate__animated animate__rollIn" id="modal_food_scan_error" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title text-danger fw-bold"></h4>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">

        </div>

        <input type="hidden" name="item"/>
      </div>
    </div>
  </div>
  <!-- modal confirm to delete item -->
  <div class="modal animate__animated animate__rollIn" id="modal_delete_item" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title">Delete Confirmation?</h4>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="row">
            <div class="col mb-12 mt-2">
              <div class="alert alert-danger">Are you sure you want to delete this item?</div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <div class="wrap-btns">
            @include('tastevn.htmls.form_button_loading')
            <button type="button" class="btn btn-primary btn-ok btn-submit acm-float-right" onclick="sensor_delete_food_scan(this)">Submit</button>
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
    $(document).ready(function () {

      //stats
      sensor_stats();

      //selectize
      var selectize_food_category = $('.wrap-select-food-category select');
      selectize_food_category.selectize({
        valueField: 'id',
        labelField: 'name',
        searchField: 'name',
        //multi_selectize
        plugins: ["remove_button"],
        preload: true,
        clearCache: function (template) {
        },
        load: function (query, callback) {
          jQuery.ajax({
            url: acmcfs.link_base_url + '/admin/food-category/selectize',
            type: 'post',
            data: {
              keyword: query,
              _token: acmcfs.var_csrf,
            },
            complete: function (xhr, textStatus) {
              var rsp = xhr.responseJSON;

              if (xhr.status == 200) {
                selectize_food_category.options = rsp.items;
                callback(rsp.items);
              }
            },
          });
        },
        create: function (input, callback) {
          $.ajax({
            url: acmcfs.link_base_url + '/admin/food-category/create',
            type: 'POST',
            data: {
              name: input,
              _token: acmcfs.var_csrf,
            },
            success: function (rsp) {
              selectize_food_category.options = rsp.items;
              callback(rsp.items);
            }
          });
        },
      });

      var selectize_food = $('.wrap-select-food select');
      selectize_food.selectize({
        valueField: 'id',
        labelField: 'name',
        searchField: 'name',
        //multi_selectize
        plugins: ["remove_button"],
        preload: true,
        clearCache: function (template) {
        },
        load: function (query, callback) {
          jQuery.ajax({
            url: acmcfs.link_base_url + '/admin/food/selectize',
            type: 'post',
            data: {
              keyword: query,
              restaurant: selectize_food.attr('data-restaurant'),
              _token: acmcfs.var_csrf,
            },
            complete: function (xhr, textStatus) {
              var rsp = xhr.responseJSON;

              if (xhr.status == 200) {
                selectize_food.options = rsp.items;
                callback(rsp.items);
              }
            },
          });
        },
      });

      //datatable
      datatable_listing_scan = $('#datatable-listing-scan table').DataTable(Object.assign(datatable_listing_scan_cfs, acmcfs.datatable_init));
      datatable_listing_error = $('#datatable-listing-error table').DataTable(Object.assign(datatable_listing_error_cfs, acmcfs.datatable_init));

      //keyCode
      $(document).keydown(function(e) {
        // console.log(e.keyCode);
        if ($('#modal_food_scan_info').hasClass('show')) {
          if (e.keyCode == 37) {
            sensor_food_scan_info_action();
          } else if (e.keyCode == 39) {
            sensor_food_scan_info_action(1);
          }
        }
      });

    });

    var datatable_listing_scan;
    var datatable_listing_scan_cfs = {
      "ajax": {
        'url': '{{url('datatable/sensor-food-scans')}}',
        "data": function (d) {
          d.restaurant = '{{$pageConfigs['item']->id}}';
          d.statuses = $('#datatable-listing-scan .wrap-search-form form select[name=statuses]').val();
          d.missing = $('#datatable-listing-scan .wrap-search-form form select[name=missing]').val();
          d.categories = $('#datatable-listing-scan .wrap-search-form form select[name=categories]').val();
          d.foods = $('#datatable-listing-scan .wrap-search-form form select[name=foods]').val();
          d.users = $('#datatable-listing-scan .wrap-search-form form select[name=users]').val();
          d.time_upload = $('#datatable-listing-scan .wrap-search-form form input[name=time_upload]').val();
          d.time_scan = $('#datatable-listing-scan .wrap-search-form form input[name=time_scan]').val();
        },
      },
      "createdRow": function (row, data, dataIndex) {
        $(row).attr('data-itd', data.id);
        $(row).attr('data-restaurant_id', '{{$pageConfigs['item']->id}}');
        $(row).attr('data-food_category_id', data.food_category_id);
        $(row).attr('data-food_id', data.food_id);

        $(row).addClass('itm_rfs');
        $(row).addClass('itm_rfs_' + data.id);

        if (data.food_id) {
          if (data.missing_texts && data.missing_texts !== '' && data.missing_texts !== 'NULL') {
            $(row).addClass('bg-danger-subtle');
          }
        }
      },
      "columns": [
        //stt
        {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false},
        {data: 'status', name: 'status'},
        {data: 'food_name', name: 'foods.name'},
        {data: 'confidence', name: 'confidence'},
        {data: 'missing_texts', name: 'missing_texts'},
        {data: 'time_photo', name: 'time_photo'},
        // {data: 'time_scan', name: 'time_scan'},
        {data: 'note', name: 'note'},
        {data: 'id', name: 'id'},
        {data: 'text_texts', name: 'text_texts'},
      ],
      columnDefs: [
        {
          targets: 0,
          render: function (data, type, full, meta) {
            var html = '';

            @if($viewer->is_dev()) //dev
              html += '<div class="d-inline-block dropdown acm-mr-px-5">' +
              '<button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown"><i class="mdi mdi-dots-vertical"></i></button>' +
              '<div class="dropdown-menu">' +
              '<a class="dropdown-item" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#modal_delete_item" onclick="sensor_delete_food_scan_prepare(this)"><i class="mdi mdi-trash-can-outline me-1"></i> Delete</a>' +
              '</div>' +
              '</div>';
            @endif

              html += '<div class="d-inline-block">' +
              '<span class="badge bg-secondary">' + full['DT_RowIndex'] + '</span>' +
              '</div>';

            return ('<div>' + html + '</div>');
          }
        },
        {
          targets: 1,
          render: function (data, type, full, meta) {
            var html = '';

            if (full['status'] == 'new') {
              html = '<div><span class="badge bg-warning">' + full['status'] + '</span></div>';
            } else if (full['status'] == 'failed') {
              html = '<div><span class="badge bg-secondary">no data</span></div>';
            } else if (full['status'] == 'scanned') {
              html = '<div><span class="badge bg-primary">' + full['status'] + '</span></div>';
            } else if (full['status'] == 'checked') {
              html = '<div><span class="badge bg-success">' + full['status'] + '</span></div>';
            } else if (full['status'] == 'edited') {
              html = '<div><span class="badge bg-info">' + full['status'] + '</span></div>';
            }

            var debug = $('input[name=debug]').val();
            var html_admin = '<div></div>';
            if (parseInt(debug)) {
              html_admin = '<div class="mt-1">' +
                '<button type="button" class="btn btn-sm btn-primary p-1 acm-mr-px-10" onclick="sensor_food_scan_api(this, 1)"><i class="mdi mdi-food ic_current"></i> re-predict</button>' +
                '</div>' +
                '<div class="mt-1">' +
                '<button type="button" class="btn btn-sm btn-danger p-1"  onclick="sensor_food_scan_api(this, 2)"><i class="mdi mdi-api ic_current"></i> re-scan-api</button>' +
                '</div>';
            }

            return ('<div>'
              + html
              + html_admin
              + '</div>');
          }
        },
        {
          targets: 2,
          render: function (data, type, full, meta) {

            var food_name = !full['food_name'] || full['food_name'] === 'null'
              ? 'Unknown...' : full['food_name'];
            var food_category = !full['category_name'] || full['category_name'] === 'null'
              ? '' : '(' + full['category_name'] + ')';
            var photo_url = full['photo_url'];
            if (parseInt(full['local_storage'])) {
              photo_url = acmcfs.link_base_url + '/sensors/' + full['photo_name'];
            }

            return (
              '<div class="clearfix cursor-pointer" onclick="sensor_food_scan_info(' + full['id'] + ')">' +
              '<div class="acm-float-left acm-mr-px-5">' +
              '<img class="acm-border-css" loading="lazy" width="100" height="70px" src="' + photo_url + '" />' +
              '</div>' +
              '<div class="overflow-hidden acm-max-line-3 acm-width-150-min">' +
              '<div>ID: ' + full['id'] + '</div>' +
              '<div>' + food_name + '</div>' +
              '<div class="acm-text-italic">' + food_category + '</div>' +
              '</div>' +
              '</div>'
            );
          }
        },
        {
          targets: 3,
          render: function (data, type, full, meta) {
            var html = '';
            var retrain = parseInt(full['rbf_retrain']);

            if (full['confidence'] && parseInt(full['confidence'])) {
              html = full['confidence'] + '%';
            }

            if (retrain) {
              switch (retrain) {
                case 1:
                  html += '<div class="mt-1"><span class="badge bg-info">re-training</span></div>';
                  break;
              }
            }

            return ('<div class="cursor-pointer" onclick="sensor_food_scan_info(' + full['id'] + ')">' + html + '</div>');
          }
        },
        {
          targets: 4,
          sType: "priority",
          render: function (data, type, full, meta) {
            if (type == 'order' || type == 'sort') {
              var sort = 0;
              if (full['missing_texts'] && full['missing_texts'] !== '' && full['missing_texts'] !== 'NULL') {
                var texts = full['missing_texts'].split('&amp;nbsp');
                if (texts.length) {
                  sort = texts.length;
                }
              }
              return sort;
            }
            else {
              var html = '';
              if (full['missing_texts'] && full['missing_texts'] !== '' && full['missing_texts'] !== 'NULL') {
                var texts = full['missing_texts'].split('&amp;nbsp');
                if (texts.length) {
                  texts.forEach(function (v, k) {

                    if (v && v.trim() !== '') {
                      html += '<div>' + v + '</div>';
                    }
                  });
                }
              }
              return ('<div class="cursor-pointer" onclick="sensor_food_scan_info(' + full['id'] + ')">' + html + '</div>');
            }
          }
        },
        {
          targets: 5,
          render: function (data, type, full, meta) {
            var html = '';
            var arr = full['time_photo'].split(' ');
            if (arr.length) {
              html = '<div>' + arr[0] + '</div>' +
                '<div>' + arr[1] + '</div>';
            } else {
              html = full['time_photo'];
            }
            return ('<div class="cursor-pointer" onclick="sensor_food_scan_info(' + full['id'] + ')">' + html + '</div>');
          }
        },
        // {
        //   targets: 6,
        //   render: function (data, type, full, meta) {
        //     var html = '';
        //     var arr = full['time_scan'].split(' ');
        //     if (arr.length) {
        //       html = '<div>' + arr[0] + '</div>' +
        //         '<div>' + arr[1] + '</div>';
        //     } else {
        //       html = full['time_scan'];
        //     }
        //     return ('<div class="cursor-pointer" onclick="sensor_food_scan_info(' + full['id'] + ')">' + html + '</div>');
        //   }
        // },
        {
          targets: 6,
          sType: "priority",
          render: function (data, type, full, meta) {
            if (type == 'order' || type == 'sort') {
              var sort = 0;
              if (full['text_texts'] && full['text_texts'] !== '' && full['text_texts'] !== 'NULL') {
                var texts = full['text_texts'].split('&amp;nbsp');
                if (texts.length) {
                  sort = texts.length;
                }
              }
              return sort;
            }
            else {
              var html = '';
              if (full['text_texts'] && full['text_texts'] !== '' && full['text_texts'] !== 'NULL') {
                var texts = full['text_texts'].split('&amp;nbsp');
                if (texts.length) {
                  texts.forEach(function (v, k) {

                    if (v && v.trim() !== '') {
                      html += '<div>+ ' + v + '</div>';
                    }
                  });
                }
              }

              if (full['note'] && full['note'] !== 'null') {
                html += '<div>+ ' + full['note'] + '</div>';
              }

              return ('<div class="cursor-pointer" onclick="sensor_food_scan_info(' + full['id'] + ')">' + html + '</div>');
            }
          }
        },
        {
          targets: 7,
          className: 'd-none',
        },
        {
          targets: 8,
          className: 'd-none',
        },
      ],
      buttons: [
        @if($viewer->is_super_admin() || $viewer->is_dev())
        {
          text: '<i class="mdi mdi-robot-confused me-0 me-sm-1"></i><span class="d-none d-sm-inline-block">Re-train Roboflow</span>',
          className: 'add-new btn btn-danger waves-effect waves-light acm-mr-px-10',
          attr: {
            'data-bs-toggle': 'modal',
            'data-bs-target': '#modal_roboflow_retraining',
          }
        },
        @endif
        {
          text: '<i class="mdi mdi-reload me-0 me-sm-1"></i><span class="d-none d-sm-inline-block">Refresh</span>',
          className: 'add-new btn btn-dark waves-effect waves-light',
          attr: {
            'onclick': 'datatable_listing_scan_refresh()',
          }
        }
      ],
    };

    var datatable_listing_error;
    var datatable_listing_error_cfs = {
      "ajax": {
        'url': '{{url('datatable/sensor-food-scan-errors')}}',
        "data": function (d) {
          d.restaurant = '{{$pageConfigs['item']->id}}';
          d.categories = $('#datatable-listing-error .wrap-search-form form select[name=categories]').val();
          d.foods = $('#datatable-listing-error .wrap-search-form form select[name=foods]').val();
          d.time_upload = $('#datatable-listing-error .wrap-search-form form input[name=time_upload]').val();
          d.time_scan = $('#datatable-listing-error .wrap-search-form form input[name=time_scan]').val();
        },
      },
      "createdRow": function (row, data, dataIndex) {
        $(row).attr('data-food_id', data.food_id);
        $(row).attr('data-missing_ids', data.missing_ids);
        $(row).attr('data-restaurant_id', {{$pageConfigs['item']->id}});

        $(row).addClass('cursor-pointer');
        $(row).attr('onclick', 'sensor_food_scan_error_info(this)');
      },
      "columns": [
        {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false},
        // {data: 'food_category_name', name: 'food_categories.name'},
        {data: 'food_name', name: 'foods.name'},
        {data: 'missing_texts', name: 'missing_texts'},
        {data: 'total_error', name: 'total_error'},
      ],
      columnDefs: [
        {
          targets: 0,
          render: function (data, type, full, meta) {
            var html = '';

              html += '<div class="d-inline-block">' +
              '<span class="badge bg-secondary">' + full['DT_RowIndex'] + '</span>' +
              '</div>';

            return ('<div>' + html + '</div>');
          }
        },
        {
          targets: 2,
          render: function (data, type, full, meta) {
            var html = '';
            if (full['missing_texts'] && full['missing_texts'] !== '' && full['missing_texts'] !== 'NULL') {
              var texts = full['missing_texts'].split('&amp;nbsp');
              if (texts.length) {
                texts.forEach(function (v, k) {

                  if (v && v.trim() !== '') {
                    html += '<div>' + v + '</div>';
                  }
                });
              }
            }
            return ('<div>' + html + '</div>');
          }
        }
      ],
      buttons: [
        {
          text: '<i class="mdi mdi-reload me-0 me-sm-1"></i><span class="d-none d-sm-inline-block">Refresh</span>',
          className: 'add-new btn btn-dark waves-effect waves-light',
          attr: {
            'onclick': 'datatable_listing_error_refresh()',
          }
        }
      ],
    };

    function datatable_listing_scan_refresh() {
      datatable_listing_scan.ajax.reload();
    }

    function datatable_listing_error_refresh() {
      datatable_listing_error.ajax.reload();
    }
  </script>
@endsection
