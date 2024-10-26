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
  <input type="hidden" name="current_restaurant_parent_id" value="{{$pageConfigs['item']->restaurant_parent_id}}"/>
  <input type="hidden" name="debug" value="{{$pageConfigs['debug']}}"/>

  <div class="row g-4 mb-4 @if($pageConfigs['debug']) d-none @endif">
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
                           autocomplete="off" data-value="last_and_current_day"
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
            <div class="d-flex align-items-center row py-1 gap-3 gap-md-0">
              <div class="col-md-6 mb-2">
                <div class="form-floating form-floating-outline wrap-select-food-category">
                  <div class="form-control acm-wrap-selectize" id="scan-search-food-category">
                    <select name="categories" multiple onchange="sensor_search_food_scan(this)">
                      <option value="">All categories</option>
                    </select>
                  </div>
                  <label for="scan-search-food-category">Dish Categories</label>
                </div>
              </div>
              <div class="col-md-6 mb-2">
                <div class="form-floating form-floating-outline wrap-select-food">
                  <div class="form-control acm-wrap-selectize" id="scan-search-food">
                    <select name="foods" multiple onchange="sensor_search_food_scan(this)">
                      <option value="">All dishes</option>
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
                    <select name="statuses" class="opt_selectize" onchange="sensor_search_food_scan(this)"
                      placeholder="All" data-placeholder="All"
                    >
                      <option>All dishes</option>
                      <option value="group_1" selected="selected">Super Confidence</option>
                      <option value="group_2">Less Training</option>
                      <option value="group_3">Not Trained Yet</option>
                    </select>
                  </div>
                  <label for="scan-search-status">Group</label>
                </div>
              </div>
              <div class="col-md-3 mb-2 d-none">
                <div class="form-floating form-floating-outline">
                  <div class="form-control acm-wrap-selectize" id="scan-search-marked">
                    <select name="marked" class="opt_selectize" onchange="sensor_search_food_scan(this)">
                      <option>All dishes</option>
                      <option value="yes">Dishes are marked</option>
                    </select>
                  </div>
                  <label for="scan-search-marked">Mark?</label>
                </div>
              </div>
              <div class="col-md-3 mb-2 d-none">
                <div class="form-floating form-floating-outline">
                  <div class="form-control acm-wrap-selectize" id="scan-search-resolved">
                    <select name="resolved" class="opt_selectize" onchange="sensor_search_food_scan(this)">
                      <option>All dishes</option>
                      <option value="yes">Dishes are resolved</option>
                    </select>
                  </div>
                  <label for="scan-search-resolved">Resolve?</label>
                </div>
              </div>
              <div class="col-md-3 mb-2">
                <div class="form-floating form-floating-outline">
                  <div class="form-control acm-wrap-selectize" id="scan-search-customer-requested">
                    <select name="customer_requested" class="opt_selectize" onchange="sensor_search_food_scan(this)">
                      <option>All dishes</option>
                      <option value="yes">Yes</option>
                    </select>
                  </div>
                  <label for="scan-search-customer-requested">Customer requested?</label>
                </div>
              </div>
              <div class="col-md-3 mb-2">
                <div class="form-floating form-floating-outline">
                  <div class="form-control acm-wrap-selectize" id="scan-search-food-multi">
                    <select name="food_multi" class="opt_selectize" onchange="sensor_search_food_scan(this)">
                      <option>All dishes</option>
                      <option value="yes">Yes</option>
                    </select>
                  </div>
                  <label for="scan-search-food-multi">Multiple dishes?</label>
                </div>
              </div>
              <div class="col-md-3 mb-2">
                <div class="form-floating form-floating-outline">
                  <div class="form-control acm-wrap-selectize" id="scan-search-typed">
                    <select name="missing" class="opt_selectize" onchange="sensor_search_food_scan(this)">
                      <option>All dishes</option>
                      <option value="yes">Dishes with missing ingredients only</option>
                      <option value="no">Dishes has all the ingredients</option>
                    </select>
                  </div>
                  <label for="scan-search-typed">Type</label>
                </div>
              </div>
              <div class="col-md-3 mb-2">
                <div class="form-floating form-floating-outline">
                  <div class="form-control acm-wrap-selectize" id="scan-search-noted">
                    <select name="noted" class="opt_selectize" onchange="sensor_search_food_scan(this)">
                      <option>All dishes</option>
                      <option value="yes">Only dishes with comments</option>
                    </select>
                  </div>
                  <label for="scan-search-noted">Note?</label>
                </div>
              </div>
              <div class="col-md-12 mb-2">
                <div class="form-floating form-floating-outline wrap-select-users">
                  <div class="form-control acm-wrap-selectize" id="scan-search-users">
                    <select name="users" multiple onchange="sensor_search_food_scan(this)"
                            data-value="user" class="ajx_selectize multi_selectize"
                    >
                      <option value="">All users</option>
                    </select>
                  </div>
                  <label for="scan-search-users">Commentators</label>
                </div>
              </div>

              <div class="col-md-6 mb-2 dev_photo_add d-none">
                <div class="form-floating form-floating-outline">
                  <input type="text" class="form-control text-center border-danger border-2" name="photo_url"
                         id="scan-search-photo" autocomplete="off"
                    value="https://s3.ap-southeast-1.amazonaws.com/cargo.tastevietnam.asia/58-5b-69-19-ad-83/SENSOR/1/2024-09-13/19/SENSOR_2024-09-13-19-06-17-622_568.jpg"
                  />
                  <label for="scan-search-photo">Photo URL</label>
                </div>
              </div>
              <div class="col-md-2 mb-2 dev_photo_add d-none">
                <div class="form-floating form-floating-outline">
                  <input type="text" class="form-control text-center border-danger border-2" name="dataset"
                         id="scan-search-dataset" autocomplete="off"
                    value="burger-jhnsa"
                  />
                  <label for="scan-search-dataset">Dataset</label>
                </div>
              </div>
              <div class="col-md-2 mb-2 dev_photo_add d-none">
                <div class="form-floating form-floating-outline">
                  <input type="text" class="form-control text-center border-danger border-2" name="version"
                         id="scan-search-version" autocomplete="off"
                    value="2"
                  />
                  <label for="scan-search-version">Version</label>
                </div>
              </div>
              <div class="col-md-2 mb-2 dev_photo_add d-none form_check_photo">
                <div class="wrap-btns">
                  @include('tastevn.htmls.form_button_loading')
                  <button type="button" class="btn btn-danger btn-ok btn-submit" onclick="photo_check()">
                    <i class="mdi mdi-check acm-mr-px-5"></i> Check photo
                  </button>
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
              <th>Group</th>
              <th>Photo</th>
              <th>Dish</th>
              <th>Ingredients missing</th>
              <th>Time upload</th>
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
    <div class="modal-dialog modal-xl acm-modal-xxl" role="document">
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

      @if($pageConfigs['debug'])
      toggle_header();
      @endif

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

      //later call
      setTimeout(function () {
        //stats
        // sensor_stats();
        $('#wrap-stats-total input[name=search_time]').attr('onchange', sensor_stats());

        //datatable
        datatable_listing_scan = $('#datatable-listing-scan table').DataTable(Object.assign(datatable_listing_scan_cfs, acmcfs.datatable_init));
        datatable_listing_error = $('#datatable-listing-error table').DataTable(Object.assign(datatable_listing_error_cfs, acmcfs.datatable_init));

      }, acmcfs.timeout_default);
    });

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
            restaurant_parent_id: '{{$pageConfigs['item']->restaurant_parent_id}}',
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
            restaurant_parent_id: '{{$pageConfigs['item']->restaurant_parent_id}}',
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

    <?php
    $php_array = $pageConfigs['food_datas'];
    $js_array = json_encode($php_array);
    echo "var food_datas = ". $js_array . ";\n";
    ?>

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
          d.resolved = $('#datatable-listing-scan .wrap-search-form form select[name=resolved]').val();
          d.marked = $('#datatable-listing-scan .wrap-search-form form select[name=marked]').val();
          d.customer_requested = $('#datatable-listing-scan .wrap-search-form form select[name=customer_requested]').val();
          d.food_multi = $('#datatable-listing-scan .wrap-search-form form select[name=food_multi]').val();
          d.noted = $('#datatable-listing-scan .wrap-search-form form select[name=noted]').val();
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
            var html = func_food_group(full['food_id']);
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
            var photo_url = full['photo_url'];
            if (parseInt(full['local_storage'])) {
              photo_url = acmcfs.link_base_url + '/sensors/' + full['photo_name'];
            }

            // if (full['photo_name'].startsWith('photos/')) {
            //   photo_url = acmcfs.link_base_url + '/sensors/' + full['photo_name'];
            // } else {
            //   if (parseInt(full['local_storage']) && acmcfs.dev_mode != 'production') {
            //     var d1 = new Date();
            //     var d2 = new Date(full['time_photo']);
            //
            //     var s1 = d1.getFullYear() + '-' + d1.getMonth() + '-' + d1.getDate();
            //     var s2 = d2.getFullYear() + '-' + d2.getMonth() + '-' + d2.getDate();
            //
            //     if (s1 == s2) {
            //       photo_url = 'https://ai.block8910.com/sensors/' + full['photo_name'];
            //     }
            //   }
            // }

            return (
              '<div class="clearfix cursor-pointer" onclick="sensor_food_scan_info(' + full['id'] + ')">' +
              '<div class="acm-float-left acm-mr-px-5">' +
              '<img class="acm-border-css" loading="lazy" width="140" height="100px" src="' + photo_url + '" />' +
              '</div>' +
              '</div>'
            );
          }
        },
        {
          targets: 3,
          render: function (data, type, full, meta) {
            var html = '';

            var food_name = !full['food_name'] || full['food_name'] === 'null'
              ? 'Unknown...' : full['food_name'];
            var food_category = !full['category_name'] || full['category_name'] === 'null'
              ? '' : '(' + full['category_name'] + ')';

            html += '<div class="overflow-hidden acm-max-line-3 acm-width-150-min">' +
              '<div>ID: <b class="text-dark">' + full['id'] + '</b></div>' +
              '<div>' + full['confidence'] + '% <b class="text-dark">' + food_name + '</b></div>' +
              '<div class="acm-text-italic text-dark">' + food_category + '</div>' +
              '</div>';

            if (full['count_foods']) {
              html += '<div>' + '<span class="badge bg-primary p-1">Multiple Dishes: <b>' + full['count_foods'] + '</b></span>' +
                '</div>';
            }

            return ('<div class="cursor-pointer acm-width-200-min" onclick="sensor_food_scan_info(' + full['id'] + ')">' + html + '</div>');
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

              if (full['rbf_error']) {
                html += '<div>' + '<span class="badge bg-danger p-1">Robot Error</span>' +
                  '</div>';
              }

              if (full['customer_requested']) {
                html += '<div>' + '<span class="badge bg-secondary p-1">Customer Requested</span>' +
                  '</div>';
              }

              if (full['missing_texts'] && full['missing_texts'] !== '' && full['missing_texts'] !== 'NULL') {
                var texts = full['missing_texts'].split('&amp;nbsp');
                if (texts.length) {
                  texts.forEach(function (v, k) {

                    if (v && v.trim() !== '') {
                      html += '<div class="fw-bold text-danger">' + v + '</div>';
                    }
                  });
                }
              }

              @if($viewer->is_dev())
              var debug = $('input[name=debug]').val();
                if (parseInt(debug)) {
                  if (full['status'] == 'tested') {
                    html += '<div class="badge bg-danger">Test Image</div>';
                  } else {
                    html += '<div><button type="button" class="btn btn-outline-secondary p-1" onclick="sensor_food_scan_api(this, 3)">tested?</button></div>';
                  }
                }

                return ('<div>' + html + '</div>');
              @endif

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

            @if($pageConfigs['debug'])

            var ts1 = Math.floor(new Date(full['time_photo']).getTime() / 1000);
            var ts2 = Math.floor(new Date(full['time_end']).getTime() / 1000);
            var ts = ts2 - ts1;
            var dev_call = false;
            if (ts > 30 || ts < 0) {
              dev_call = true;
            }

            var html2 = '<div class="acm-clearfix">' +
              '<div class="text-dark fw-bold acm-float-right">' + full['time_scan'] + '</div>' +
              '<div class="text-dark overflow-hidden">Time Start Scan: </div>' +
              '</div>' +
              '<div class="acm-clearfix">' +
              '<div class="text-dark fw-bold acm-float-right">' + full['time_end'] + '</div>' +
              '<div class="text-dark overflow-hidden">Time End Predict: </div>' +
              '</div>' +
              '<div class="acm-clearfix">' +
              '<div class="text-dark fw-bold acm-float-right">' + ts + '</div>' +
              '<div class="text-dark overflow-hidden">Total (seconds): </div>' +
              '</div>';

            if (dev_call) {
              html2 = '<div class="acm-clearfix">' +
                '<div class="text-dark overflow-hidden acm-text-italic">(dev called checking again)</div>' +
                '</div>';
            }

            html = '<div class="position-relative">' +
              '<div class="acm-clearfix">' +
              '<div class="text-dark fw-bold acm-float-right">' + full['time_photo'] + '</div>' +
              '<div class="text-dark overflow-hidden">Time Upload: </div>' +
              '</div>' + html2 +
              '</div>';

            @endif

            return ('<div class="cursor-pointer" onclick="sensor_food_scan_info(' + full['id'] + ')">' + html + '</div>');
          }
        },
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

              if (full['note_kitchen']) {
                html += '<div>' + '<span class="badge bg-secondary p-1">Note Kitchen</span>' +
                  '</div>';
              }

              if (full['note'] && full['note'] !== 'null') {
                html += '<div>+ ' + full['note'] + '</div>';
              }

              return ('<div class="cursor-pointer acm-col-noted acm-width-300-max acm-width-200-min" onclick="sensor_food_scan_info(' + full['id'] + ')">' + html + '</div>');
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
        @if($pageConfigs['debug'])
        {
          text: '<i class="mdi mdi-plus me-0 me-sm-1"></i><span class="d-none d-sm-inline-block">Add photo</span>',
          className: 'add-new btn btn-primary waves-effect waves-light acm-mr-px-10',
          attr: {
            'onclick': 'photo_add()',
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
      if (datatable_listing_scan) {
        datatable_listing_scan.ajax.reload();
      }
    }

    function datatable_listing_error_refresh() {
      if (datatable_listing_error) {
        datatable_listing_error.ajax.reload();
      }
    }

    function func_food_group(food_id) {
      var html = '<div><span class="badge bg-secondary">Not Trained<br/>Yet</span></div>';

      if (parseInt(food_id)) {
        food_datas.forEach(function (v, k) {
          if (parseInt(food_id) == parseInt(v.food_id)) {
            if (parseInt(v.live_group) == 1) {
              html = '<div><span class="badge bg-success">Super<br/>Confidence</span></div>';
            } else if (parseInt(v.live_group) == 2) {
              html = '<div><span class="badge bg-info">Less<br/>Training</span></div>';
            }
          }
        });
      }

      return html;
    }

    function photo_add() {
      $('.dev_photo_add').toggleClass('d-none');
    }

    function photo_check() {
      var form = $('#datatable-listing-scan form');
      var form_fake = $('.form_check_photo');
      form_loading(form_fake);

      axios.post('/tester/photo/check', {
        sensor: {{$pageConfigs['item']->id}},
        photo: form.find('input[name=photo_url]').val(),
        dataset: form.find('input[name=dataset]').val(),
        version: form.find('input[name=version]').val(),
      })
        .then(response => {

          datatable_listing_scan_refresh();

        })
        .catch(error => {
          console.log(error);
          if (error.response.data && Object.values(error.response.data).length) {
            Object.values(error.response.data).forEach(function (v, k) {
              message_from_toast('error', acmcfs.message_title_error, v);
            });
          }
        }).then(response => {

        form_loading(form_fake, false);

      });

      return false;
    }
  </script>
@endsection
