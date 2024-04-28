@extends('tastevn/layouts/layoutMaster')

@section('title', 'Admin - Restaurant: ' . $pageConfigs['item']->name)

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

  <h4 class="mb-2"><span class="text-muted fw-light">Admin /</span> Restaurant: {{$pageConfigs['item']->name}}</h4>
  <input type="hidden" name="current_restaurant" value="{{$pageConfigs['item']->id}}"/>

  <div class="row g-4 mb-2">
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
                           onchange="stats_total()"
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
                  <button type="button" class="btn btn-danger btn-sm p-1" onclick="stats_clear(this)">
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

        @if($viewer->id == 5)
        <div class="card-body d-flex justify-content-between flex-wrap gap-3">
          <div class="card-datatable table-responsive">
            <table class="table table-hover">
              <thead class="table-light">
              <tr>
                <th></th>
                @php
                $datas = [];
                  for($d=1;$d<=(int)date('d');$d++):

                $datas[$d][] = $api_core->rfs_query_data(date('Y-m-' . $d), $pageConfigs['item']->id);

                  $df = $d < 10 ? '0' . $d : $d;
                  $dt = ($d + 1) < 10 ? '0' . ($d + 1) : $d + 1;

                @endphp
                <th class="cursor-pointer" onclick="stats_total_by_date('{{date($df . '/m/Y')}}', '{{date($dt . '/m/Y')}}')"
                >{{$d . '/' . (int)date('m')}}</th>
                @endfor
              </tr>
              </thead>
              <tbody>
              <tr>
                <td>Total number of photos</td>
                @php
                  for($d=1;$d<=(int)date('d');$d++):
                @endphp
                <td>{{$datas[$d][0]['total_photos']}}</td>
                @endfor
              </tr>
              <tr>
                <td>-> Other photos, besides the 27 test dishes</td>
                @php
                  for($d=1;$d<=(int)date('d');$d++):
                @endphp
                <td>{{$datas[$d][0]['total_failed']}}</td>
                @endfor
              </tr>
              <tr>
                <td>-> Photos of 27 test dishes</td>
                @php
                  for($d=1;$d<=(int)date('d');$d++):
                @endphp
                <td>{{$datas[$d][0]['total_checked']}}</td>
                @endfor
              </tr>
              <tr>
                <td>---> Photos of dishes missing ingredients</td>
                @php
                  for($d=1;$d<=(int)date('d');$d++):
                @endphp
                <td>{{$datas[$d][0]['total_checked_missing']}} <b>({{$datas[$d][0]['percent_checked_missing']}}%)</b></td>
                @endfor
              </tr>
              <tr>
                <td>---> Photos of dishes full ingredients</td>
                @php
                  for($d=1;$d<=(int)date('d');$d++):
                @endphp
                <td>{{$datas[$d][0]['total_checked_ok']}}</td>
                @endfor
              </tr>
              </tbody>
            </table>
          </div>
        </div>
        @endif
      </div>
    </div>
  </div>

  <div class="nav-align-top mb-4">
    <div class="card mb-4">

      <div class="card-header p-0">
        <div class="nav-align-top">
          <ul class="nav nav-tabs nav-fill" role="tablist">
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
            <li class="nav-item">
              <button type="button" class="nav-link " role="tab"
                      data-bs-toggle="tab" data-bs-target="#datatable-listing-food"
                      aria-controls="datatable-listing-food" aria-selected="false">
                <i class="tf-icons mdi mdi-view-list me-1"></i> List of dishes
              </button>
            </li>
          </ul>
        </div>
      </div>

      <div class="card-body p-0">
        <div class="tab-content">
          <div class="tab-pane fade show active" id="datatable-listing-scan" role="tabpanel">
            <div class="card mb-4">
              <div class="card-header border-bottom wrap-search-form">
                <h5 class="card-title">Search Conditions</h5>

                <form onsubmit="event.preventDefault(); return datatable_listing_scan_refresh();">
                  <div class="d-flex justify-content-between align-items-center row py-1 gap-3 gap-md-0">
                    <div class="col-md-6 mb-2">
                      <div class="form-floating form-floating-outline wrap-select-food-category">
                        <div class="form-control acm-wrap-selectize" id="scan-search-food-category">
                          <select name="categories" multiple onchange="restaurant_search_food_scan(this)">
                            <option value="">All</option>
                          </select>
                        </div>
                        <label for="scan-search-food-category">Dish Categories</label>
                      </div>
                    </div>
                    <div class="col-md-6 mb-2">
                      <div class="form-floating form-floating-outline wrap-select-food">
                        <div class="form-control acm-wrap-selectize" id="scan-search-food">
                          <select name="foods" multiple onchange="restaurant_search_food_scan(this)">
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
                               onchange="restaurant_search_food_scan(this)"/>
                        <label for="scan-search-time-upload">Time upload</label>
                      </div>
                    </div>
                    <div class="col-md-6 mb-2">
                      <div class="form-floating form-floating-outline">
                        <input type="text" class="form-control text-center date_time_picker" name="time_scan"
                               id="scan-search-time-scan" autocomplete="off" data-value="last_and_current_day"
                               onchange="restaurant_search_food_scan(this)"/>
                        <label for="scan-search-time-scan">Time scanned</label>
                      </div>
                    </div>
                    <div class="col-md-6 mb-2">
                      <div class="form-floating form-floating-outline">
                        <div class="form-control acm-wrap-selectize" id="scan-search-status">
                          <select name="statuses" class="opt_selectize" multiple onchange="restaurant_search_food_scan(this)">
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
                          <select name="missing" class="opt_selectize" onchange="restaurant_search_food_scan(this)">
                            <option value="">All dishes</option>
                            <option value="yes">Dish with missing ingredients only</option>
                            <option value="no">Dish has all the ingredients</option>
                          </select>
                        </div>
                        <label for="scan-search-missing">Type</label>
                      </div>
                    </div>
                    <div class="col-md-12 mb-2">
                      <div class="form-floating form-floating-outline wrap-select-users">
                        <div class="form-control acm-wrap-selectize" id="scan-search-users">
                          <select name="users" multiple onchange="restaurant_search_food_scan(this)"
                                  data-value="user" class="ajx_selectize"
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

              <div class="card-datatable table-responsive">
                <table class="table table-hover">
                  <thead class="table-light">
                  <tr>
                    <th></th>
                    <th>Status</th>
                    <th class="acm-width-300-min">Dish</th>
                    <th>Confidence</th>
                    <th class="acm-width-300-min">Ingredients missing</th>
                    <th class="acm-width-120-min">Time upload</th>
                    <th class="acm-width-120-min">Time scanned</th>
                    <th class="acm-width-400-min">Note</th>
                    <th class="acm-width-200-min">Category</th>
                    <th class="d-none"></th>
                  </tr>
                  </thead>
                </table>
              </div>
            </div>
          </div>

          <div class="tab-pane fade" id="datatable-listing-error" role="tabpanel">
            <div class="card mb-4">
              <div class="card-header border-bottom wrap-search-form">
                <h5 class="card-title">Search Conditions</h5>

                <form onsubmit="event.preventDefault(); return datatable_listing_error_refresh();">
                  <div class="d-flex justify-content-between align-items-center row py-1 gap-3 gap-md-0">
                    <div class="col-md-6 mb-2">
                      <div class="form-floating form-floating-outline wrap-select-food-category">
                        <div class="form-control acm-wrap-selectize" id="error-search-food-category">
                          <select name="categories" multiple
                                  onchange="restaurant_search_food_scan_error(this)">
                            <option value="">All</option>
                          </select>
                        </div>
                        <label for="error-search-food-category">Dish Categories</label>
                      </div>
                    </div>
                    <div class="col-md-6 mb-2">
                      <div class="form-floating form-floating-outline wrap-select-food">
                        <div class="form-control acm-wrap-selectize" id="error-search-food">
                          <select name="foods" multiple onchange="restaurant_search_food_scan_error(this)">
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
                               onchange="restaurant_search_food_scan_error(this)"/>
                        <label for="error-search-time-upload">Time upload</label>
                      </div>
                    </div>
                    <div class="col-md-6 mb-2">
                      <div class="form-floating form-floating-outline">
                        <input type="text" class="form-control text-center date_time_picker" name="time_scan"
                               id="error-search-time-scan" autocomplete="off" data-value="last_and_current_day"
                               onchange="restaurant_search_food_scan_error(this)"/>
                        <label for="error-search-time-scan">Time scanned</label>
                      </div>
                    </div>
                  </div>
                </form>
              </div>

              <div class="card-datatable table-responsive">
                <table class="table table-hover">
                  <thead class="table-light">
                  <tr>
                    <th></th>
                    <th>Category</th>
                    <th>Dish</th>
                    <th>Ingredients missing</th>
                    <th>Total number of errors</th>
                  </tr>
                  </thead>
                </table>
              </div>
            </div>
          </div>

          <div class="tab-pane fade" id="datatable-listing-food" role="tabpanel">
            <div class="card mb-4">
              <div class="card-header border-bottom d-none">
                <h5 class="card-title">List of dishes</h5>
              </div>
              <div class="card-datatable table-responsive">
                <table class="table table-hover">
                  <thead class="table-light">
                  <tr>
                    <th></th>
                    <th>Category</th>
                    <th>Dish</th>
                    <th>Latest updated</th>
                    <th></th>
                  </tr>
                  </thead>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Offcanvas to add food into restaurant -->
  <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvas_add_foods"
       aria-labelledby="offcanvas_add_foods_label">
    <div class="offcanvas-header">
      <h5 id="offcanvas_add_foods_label" class="offcanvas-title">Add Dishes To Restaurant</h5>
      <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body mx-0 flex-grow-0 h-100">
      <form class="pt-0" onsubmit="return restaurant_add_foods(event, this);">
        <div class="form-floating form-floating-outline mb-4 wrap-select-food-category">
          <div class="form-control acm-wrap-selectize" id="add-item-category">
            <select name="category"></select>
          </div>
          <label for="add-item-category">Select Category</label>
        </div>
        <div class="form-floating form-floating-outline mb-4 wrap-select-food">
          <div class="form-control acm-wrap-selectize" id="add-item-food">
            <select name="foods" multiple required
                    data-restaurant="{{$pageConfigs['item']->id}}"
            ></select>
          </div>
          <label for="add-item-food">Dishes <b class="text-danger">*</b></label>
        </div>

        <button class="btn btn-primary me-sm-3 me-1 data-submit">Submit</button>
        <button type="reset" class="btn btn-outline-secondary" data-bs-dismiss="offcanvas">Cancel</button>

        <input type="hidden" name="item" value="{{$pageConfigs['item']->id}}"/>
      </form>
    </div>
  </div>
  <!-- modal confirm to delete food from restaurant -->
  <div class="modal animate__animated animate__rollIn" id="modal_delete_food_out_restaurant" tabindex="-1"
       aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title">Remove Confirmation?</h4>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="row">
            <div class="col mb-12 mt-2">
              <div class="alert alert-danger">Are you sure you want to remove this item?</div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
          <button type="button" onclick="restaurant_delete_food(this)" class="btn btn-primary" data-bs-dismiss="modal">
            Confirm
          </button>
          <input type="hidden" name="restaurant_id" value="{{$pageConfigs['item']->id}}"/>
          <input type="hidden" name="food_id"/>
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
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
          <button type="button" onclick="roboflow_retraining()" class="btn btn-primary" data-bs-dismiss="modal">
            Confirm
          </button>
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
  <!-- modal confirm to delete food scan result restaurant -->
  <div class="modal animate__animated animate__rollIn" id="modal_delete_food_scan" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title">Remove Confirmation?</h4>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="row">
            <div class="col mb-12 mt-2">
              <div class="alert alert-danger">Are you sure you want to remove this item?</div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
          <button type="button" onclick="restaurant_delete_food_scan(this)" class="btn btn-primary"
                  data-bs-dismiss="modal">Confirm
          </button>
          <input type="hidden" name="itd"/>
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
      stats_total();

      //selectize
      var selectize_food_category = $('.wrap-select-food-category select');
      selectize_food_category.selectize({
        valueField: 'id',
        labelField: 'name',
        searchField: 'name',
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
      datatable_listing_food = $('#datatable-listing-food table').DataTable(Object.assign(datatable_listing_food_cfs, acmcfs.datatable_init));
      datatable_listing_scan = $('#datatable-listing-scan table').DataTable(Object.assign(datatable_listing_scan_cfs, acmcfs.datatable_init));
      datatable_listing_error = $('#datatable-listing-error table').DataTable(Object.assign(datatable_listing_error_cfs, acmcfs.datatable_init));

      //keyCode
      $(document).keydown(function(e) {
        // console.log(e.keyCode);
        if ($('#modal_food_scan_info').hasClass('show')) {
          if (e.keyCode == 37) {
            restaurant_food_scan_result_info_action();
          } else if (e.keyCode == 39) {
            restaurant_food_scan_result_info_action(1);
          }
        }
      });

    });

    var datatable_listing_food;
    var datatable_listing_food_cfs = {
      "ajax": "{{ url('datatable/restaurant-foods?restaurant=' . $pageConfigs['item']->id) }}",
      "createdRow": function (row, data, dataIndex) {
        $(row).attr('data-restaurant_id', '{{$pageConfigs['item']->id}}');
        $(row).attr('data-food_id', data.food_id);
      },
      "columns": [
        //stt
        {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false},
        {data: 'category_name', name: 'food_categories.name'},
        {data: 'food_name', name: 'foods.name'},
        {data: 'updated_at', name: 'updated_at'},
        {data: null},
      ],
      columnDefs: [
        {
          targets: 3,
          render: $.fn.dataTable.render.moment('YYYY-MM-DDTHH:mm:ss.SSSSZ', 'DD/MM/YY HH:mm:ss')
        },
        {
          // Actions
          targets: 4,
          title: '',
          searchable: false,
          orderable: false,
          render: function (data, type, full, meta) {
            return (
              '<div class="dropdown">' +
              '<button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown"><i class="mdi mdi-dots-vertical"></i></button>' +
              '<div class="dropdown-menu">' +
              // '<a class="dropdown-item" href="javascript:void(0);" data-bs-toggle="offcanvas" data-bs-target="#offcanvas_edit_item" onclick="restaurant_edit_prepare(this)"><i class="mdi mdi-pencil-outline me-1"></i> Edit</a>' +
              '<a class="dropdown-item" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#modal_delete_food_out_restaurant" onclick="restaurant_delete_food_confirm(this)"><i class="mdi mdi-trash-can-outline me-1"></i> Remove</a>' +
              '</div>' +
              '</div>'
            );
          }
        }
      ],
      buttons: [
        {
          text: '<i class="mdi mdi-plus me-0 me-sm-1"></i><span class="d-none d-sm-inline-block">Add Dishes To Restaurant</span>',
          className: 'add-new btn btn-primary waves-effect waves-light acm-mr-px-10',
          attr: {
            'data-bs-toggle': 'offcanvas',
            'data-bs-target': '#offcanvas_add_foods',
            'onclick': 'setTimeout(function () { $("#offcanvas_add_foods form select[name=foods]").selectize()[0].selectize.focus(); }, 500)',
          }
        },
        {
          text: '<i class="mdi mdi-reload me-0 me-sm-1"></i><span class="d-none d-sm-inline-block">Refresh</span>',
          className: 'add-new btn btn-dark waves-effect waves-light',
          attr: {
            'onclick': 'datatable_listing_food_refresh()',
          }
        }
      ],
    };

    var datatable_listing_scan;
    var datatable_listing_scan_cfs = {
      "ajax": {
        'url': '{{url('datatable/restaurant-food-scans')}}',
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
        {data: 'text_texts', name: 'text_texts'},
        {data: 'food_name', name: 'foods.name'},
        {data: 'confidence', name: 'confidence'},
        {data: 'missing_texts', name: 'missing_texts'},
        {data: 'time_photo', name: 'time_photo'},
        {data: 'time_scan', name: 'time_scan'},
        {data: 'note', name: 'note'},
        {data: 'id', name: 'id'},
      ],
      columnDefs: [
        {
          targets: 1,
          render: function (data, type, full, meta) {
            var html = '';
            var retrain = parseInt(full['rbf_retrain']);

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

            if (retrain) {
              switch (retrain) {
                case 1:
                  html += '<div class="mt-1"><span class="badge bg-primary">retraining</span></div>';
                  break;
              }
            }

            return ('<div class="cursor-pointer" onclick="restaurant_food_scan_result_info(' + full['id'] + ')">' + html + '</div>');
          }
        },
        {
          targets: 2,
          render: function (data, type, full, meta) {

            var food_name = !full['food_name'] || full['food_name'] === 'null' ? 'Unknown...'
              : full['food_name'];

            return (
              // '<div class="dropdown z-5">' +
              // '<button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown"><i class="mdi mdi-dots-vertical"></i></button>' +
              // '<div class="dropdown-menu">' +
              // '<a class="dropdown-item" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#modal_delete_food_scan" onclick="restaurant_delete_food_scan_confirm(this)"><i class="mdi mdi-trash-can-outline me-1"></i> Remove</a>' +
              // '</div>' +
              // '</div>' +
              '<div class="clearfix cursor-pointer" onclick="restaurant_food_scan_result_info(' + full['id'] + ')">' +
              '<div class="acm-float-left acm-mr-px-5">' +
              '<img class="acm-border-css" loading="lazy" width="50" height="50px" src="' + full['photo_url'] + '" />' +
              '</div>' +
              '<div class="overflow-hidden acm-max-line-2">' +
              '<div>ID: ' + full['id'] + '</div>' +
              '<div>' + food_name + '</div>' +
              '</div>' +
              '</div>'
            );
          }
        },
        {
          targets: 3,
          render: function (data, type, full, meta) {
            var html = '';
            if (full['confidence'] && parseInt(full['confidence'])) {
              html = full['confidence'] + '%';
            }
            return ('<div class="cursor-pointer" onclick="restaurant_food_scan_result_info(' + full['id'] + ')">' + html + '</div>');
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
              return ('<div class="cursor-pointer" onclick="restaurant_food_scan_result_info(' + full['id'] + ')">' + html + '</div>');
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
            return ('<div class="cursor-pointer" onclick="restaurant_food_scan_result_info(' + full['id'] + ')">' + html + '</div>');
          }
        },
        {
          targets: 6,
          render: function (data, type, full, meta) {
            var html = '';
            var arr = full['time_scan'].split(' ');
            if (arr.length) {
              html = '<div>' + arr[0] + '</div>' +
                '<div>' + arr[1] + '</div>';
            } else {
              html = full['time_scan'];
            }
            return ('<div class="cursor-pointer" onclick="restaurant_food_scan_result_info(' + full['id'] + ')">' + html + '</div>');
          }
        },
        {
          targets: 7,
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

              return ('<div class="cursor-pointer" onclick="restaurant_food_scan_result_info(' + full['id'] + ')">' + html + '</div>');
            }
          }
        },
      ],
      buttons: [
        // {
        //   text: '<i class="mdi mdi-robot-confused me-0 me-sm-1"></i><span class="d-none d-sm-inline-block">Re-train Roboflow</span>',
        //   className: 'add-new btn btn-danger waves-effect waves-light acm-mr-px-10',
        //   attr: {
        //     'onclick': 'roboflow_retraining_confirm()',
        //   }
        // },
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
        'url': '{{url('datatable/restaurant-food-scan-errors')}}',
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
        $(row).attr('onclick', 'restaurant_food_scan_error_info(this)');
      },
      "columns": [
        //stt
        {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false},
        {data: 'food_category_name', name: 'food_categories.name'},
        {data: 'food_name', name: 'foods.name'},
        {data: 'missing_texts', name: 'missing_texts'},
        {data: 'total_error', name: 'total_error'},
      ],
      columnDefs: [
        {
          targets: 3,
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

    function datatable_listing_food_refresh() {
      datatable_listing_food.ajax.reload();
    }

    function datatable_listing_scan_refresh() {
      datatable_listing_scan.ajax.reload();
    }

    function datatable_listing_error_refresh() {
      datatable_listing_error.ajax.reload();
    }
  </script>
@endsection
