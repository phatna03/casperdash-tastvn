@extends('tastvn/layouts/layoutMaster')

@section('title', 'Admin Restaurant Info')

@section('vendor-style')
  <link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css')}}">
  <link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css')}}">
  <link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.css')}}">
  <link rel="stylesheet" href="{{asset('assets/vendor/libs/select2/select2.css')}}"/>
  <link rel="stylesheet" href="{{asset('assets/vendor/libs/@form-validation/umd/styles/index.min.css')}}"/>
  <link rel="stylesheet" href="{{asset('assets/vendor/libs/animate-css/animate.css')}}"/>
  <link rel="stylesheet" href="{{asset('assets/vendor/libs/sweetalert2/sweetalert2.css')}}"/>

  <link rel="stylesheet" href="{{asset('assets/vendor/libs/flatpickr/flatpickr.css')}}" />
  <link rel="stylesheet" href="{{asset('assets/vendor/libs/bootstrap-datepicker/bootstrap-datepicker.css')}}" />
  <link rel="stylesheet" href="{{asset('assets/vendor/libs/bootstrap-daterangepicker/bootstrap-daterangepicker.css')}}" />
  <link rel="stylesheet" href="{{asset('assets/vendor/libs/jquery-timepicker/jquery-timepicker.css')}}" />
  <link rel="stylesheet" href="{{asset('assets/vendor/libs/pickr/pickr-themes.css')}}" />
@endsection

@section('vendor-script')
  <script src="{{asset('assets/vendor/libs/moment/moment.js')}}"></script>
  <script src="{{asset('assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js')}}"></script>
  <script src="{{asset('assets/vendor/libs/select2/select2.js')}}"></script>
  <script src="{{asset('assets/vendor/libs/@form-validation/umd/bundle/popular.min.js')}}"></script>
  <script src="{{asset('assets/vendor/libs/@form-validation/umd/plugin-bootstrap5/index.min.js')}}"></script>
  <script src="{{asset('assets/vendor/libs/@form-validation/umd/plugin-auto-focus/index.min.js')}}"></script>
  <script src="{{asset('assets/vendor/libs/cleavejs/cleave.js')}}"></script>
  <script src="{{asset('assets/vendor/libs/cleavejs/cleave-phone.js')}}"></script>
  <script src="{{asset('assets/vendor/libs/sweetalert2/sweetalert2.js')}}"></script>

  <script src="{{asset('assets/vendor/libs/flatpickr/flatpickr.js')}}"></script>
  <script src="{{asset('assets/vendor/libs/bootstrap-datepicker/bootstrap-datepicker.js')}}"></script>
  <script src="{{asset('assets/vendor/libs/bootstrap-daterangepicker/bootstrap-daterangepicker.js')}}"></script>
  <script src="{{asset('assets/vendor/libs/jquery-timepicker/jquery-timepicker.js')}}"></script>
  <script src="{{asset('assets/vendor/libs/pickr/pickr.js')}}"></script>
@endsection

@section('page-script')
{{--  <script src="{{asset('js/laravel-user-management.js')}}"></script>--}}
@endsection

@section('content')

  @php
  $dishes_total = rand(1000, 10000);
  $dishes_error_percent = rand(10, 80);
  $dishes_error_count = (int)($dishes_total * $dishes_error_percent / 100);
  @endphp

  <h4 class="py-3 mb-4"><span class="text-muted fw-light">Admin /</span> Restaurant 1</h4>

  <div class="row g-4 mb-4">
    <div class="col-lg-4">
      <div class="card h-100">
        <div class="card-header">
          <div class="d-flex justify-content-between">
            <h4 class="mb-2">Total dished scanned</h4>
            <div class="dropdown">
              <button class="btn p-0" type="button" id="salesOverview" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <i class="mdi mdi-filter mdi-24px"></i>
              </button>
              <div class="dropdown-menu dropdown-menu-end" aria-labelledby="salesOverview">
                <div class="w-px-350 p-2">
                  <label for="dateRangePicker1" class="form-label">Date Range</label>
                  <div class="input-group input-daterange date_range_picker">
                    <input type="text" id="dateRangePicker1" placeholder="MM/DD/YYYY" class="form-control" />
                    <span class="input-group-text">to</span>
                    <input type="text" placeholder="MM/DD/YYYY" class="form-control" />
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="d-flex align-items-center">
            <div class="d-flex align-items-end mt-2">
              <h4 class="mb-0 me-2">{{$dishes_total}}</h4>
              <small>(today: <b class="text-success">{{rand(10, 500)}}</b>)</small>
            </div>
          </div>
        </div>
        <div class="card-body d-flex justify-content-between flex-wrap gap-3">
          <div class="d-flex gap-3">
            <div class="avatar">
              <div class="avatar-initial bg-label-primary rounded">
                <i class="mdi mdi-food mdi-24px"></i>
              </div>
            </div>
            <div class="card-info">
              <div>
                <h4 class="mb-0 d-inline-block">{{$dishes_error_count}}</h4>
                <small class="text-danger fw-bold d-inline-block">({{$dishes_error_percent}}%)</small>
              </div>
              <small>Total Dishes Error</small>
            </div>
          </div>
          <div class="d-flex gap-3">
            <div class="avatar">
              <div class="avatar-initial bg-label-warning rounded">
                <i class="mdi mdi-food-off mdi-24px"></i>
              </div>
            </div>
            <div class="card-info">
              <div>
                <h4 class="mb-0 d-inline-block">{{rand(10, 200)}}</h4>
                <small class="text-danger fw-bold d-inline-block">({{rand(5, 30)}}%)</small>
              </div>
              <small>Total Ingredients Missing</small>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="col-lg-4">
      <div class="card h-100">
        <div class="card-header">
          <div class="d-flex justify-content-between">
            <h4 class="mb-2">Top 5 dishes error</h4>
            <div class="dropdown">
              <button class="btn p-0" type="button" id="salesOverview" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <i class="mdi mdi-filter mdi-24px"></i>
              </button>
              <div class="dropdown-menu dropdown-menu-end" aria-labelledby="salesOverview">
                <div class="w-px-350 p-2">
                  <label for="dateRangePicker2" class="form-label">Date Range</label>
                  <div class="input-group input-daterange date_range_picker">
                    <input type="text" id="dateRangePicker2" placeholder="MM/DD/YYYY" class="form-control" />
                    <span class="input-group-text">to</span>
                    <input type="text" placeholder="MM/DD/YYYY" class="form-control" />
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="card-body pt-0 text-right">
          @php
            $count = rand(70, 100);
          @endphp
          @for($i=1;$i<=5;$i++)
            <div>Pizza margherita {{$i}} <b class="text-dark">({{$count}})</b></div>
            @php
              $count -= rand(10, 20);
            @endphp
          @endfor
        </div>
      </div>
    </div>

    <div class="col-lg-4">
      <div class="card h-100">
        <div class="card-header">
          <div class="d-flex justify-content-between">
            <h4 class="mb-2">Top 5 ingredients missing</h4>
            <div class="dropdown">
              <button class="btn p-0" type="button" id="salesOverview" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <i class="mdi mdi-filter mdi-24px"></i>
              </button>
              <div class="dropdown-menu dropdown-menu-end" aria-labelledby="salesOverview">
                <div class="w-px-350 p-2">
                  <label for="dateRangePicker3" class="form-label">Date Range</label>
                  <div class="input-group input-daterange date_range_picker">
                    <input type="text" id="dateRangePicker3" placeholder="MM/DD/YYYY" class="form-control" />
                    <span class="input-group-text">to</span>
                    <input type="text" placeholder="MM/DD/YYYY" class="form-control" />
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="card-body pt-0 text-right">
          @php
            $count = rand(100, 200);
          @endphp
          @for($i=1;$i<=5;$i++)
            <div>Pizza dough {{$i}} <b class="text-dark">({{$count}})</b></div>
            @php
              $count -= rand(10, 20);
            @endphp
          @endfor
        </div>
      </div>
    </div>
  </div>

  <!-- Users List Table -->
  <div class="card">
    <div class="card-header border-bottom">
      <h5 class="card-title">List of dishes</h5>
      <div class="d-flex justify-content-between align-items-center row py-3 gap-3 gap-md-0 d-none">
        <div class="col-md-4 user_role"></div>
        <div class="col-md-4 user_plan"></div>
        <div class="col-md-4 user_status"></div>
      </div>
    </div>
    <div class="card-datatable table-responsive">
      <table class="datatables-users table" id="tbl_dashboard">
        <thead class="table-light">
        <tr>
          <th></th>
          <th>Dishes</th>
          <th>Ingredients</th>
          <th class="text-center">Total scanned</th>
          <th class="text-center">Last scanned</th>
          <th></th>
        </tr>
        </thead>
        <tbody>
        @for($i=1;$i<=rand(5, 18);$i++)
          <tr>
            <td class="text-center">
              <div>{{$i}}</div>
            </td>
            <td>
              <span>pizza margherita {{$i}}</span>
            </td>
            <td>
              <span>pizza dough, tomatoes, mozzarella balls, fresh basil</span>
            </td>
            <td class="text-center">
              <div>{{rand(10, 100)}}</div>
            </td>
            <td class="text-center">
              <div>{{date('d/m/Y H:i:s')}}</div>
            </td>
            <td class="text-center">
              <div class="dropdown ">
                <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown"><i class="mdi mdi-dots-vertical"></i></button>
                <div class="dropdown-menu">
                  <a class="dropdown-item no_export" href="javascript:void(0);" data-bs-toggle="offcanvas" data-bs-target="#offcanvasEditUser"><i class="mdi mdi-pencil-outline me-1"></i> Edit</a>
                  <a class="dropdown-item no_export" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#deleteModal"><i class="mdi mdi-trash-can-outline me-1"></i> Delete</a>
                </div>
              </div>
            </td>
          </tr>
        @endfor
        </tbody>
      </table>
    </div>

    <!-- Offcanvas to add new Dish -->
    <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasAddUser" aria-labelledby="offcanvasAddUserLabel">
      <div class="offcanvas-header">
        <h5 id="offcanvasAddUserLabel" class="offcanvas-title">Add Dish</h5>
        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
      </div>
      <div class="offcanvas-body mx-0 flex-grow-0 h-100">
        <form class="add-new-user pt-0" id="addNewUserForm" onsubmit="return false">
          <div class="form-floating form-floating-outline mb-4">
            <input type="text" class="form-control" id="add-user-fullname" name="name" />
            <label for="add-user-fullname">Name</label>
            <div id="add-user-fullname" class="form-text fw-bold text-primary mt-3">Each Ingredient is separated by a comma</div>
          </div>
          <div class="form-floating form-floating-outline mb-4">
            <textarea class="form-control h-px-300" id="add-user-Ingredients" name="Ingredients" placeholder="pizza dough, tomatoes, mozzarella balls, fresh basil"></textarea>
            <label for="add-user-Ingredients">Ingredients</label>
          </div>
          <button class="btn btn-primary me-sm-3 me-1 data-submit"
                  {{--                  type="submit"--}}
                  type="button" onclick="submit_ok()" data-bs-dismiss="offcanvas"
          >Submit</button>
          <button type="reset" class="btn btn-outline-secondary" data-bs-dismiss="offcanvas">Cancel</button>
        </form>
      </div>
    </div>

    <!-- Offcanvas to edit Dish -->
    <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasEditUser" aria-labelledby="offcanvasEditUserLabel">
      <div class="offcanvas-header">
        <h5 id="offcanvasEditUserLabel" class="offcanvas-title">Edit Dish</h5>
        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
      </div>
      <div class="offcanvas-body mx-0 flex-grow-0 h-100">
        <form class="add-new-user pt-0" id="editUserForm" onsubmit="return false">
          <div class="form-floating form-floating-outline mb-4">
            <input type="text" class="form-control" id="edit-user-fullname" name="name" />
            <label for="add-user-fullname">Name</label>
            <div id="edit-user-fullname" class="form-text fw-bold text-primary mt-3">Each Ingredient is separated by a comma</div>
          </div>
          <div class="form-floating form-floating-outline mb-4">
            <textarea class="form-control h-px-300" id="edit-user-Ingredients" name="Ingredients" placeholder="pizza dough, tomatoes, mozzarella balls, fresh basil"></textarea>
            <label for="add-user-Ingredients">Ingredients</label>
          </div>
          <button class="btn btn-primary me-sm-3 me-1 data-submit"
                  {{--                  type="submit"--}}
                  type="button" onclick="submit_ok()" data-bs-dismiss="offcanvas"
          >Submit</button>
          <button type="reset" class="btn btn-outline-secondary" data-bs-dismiss="offcanvas">Cancel</button>
        </form>
      </div>
    </div>
  </div>
@endsection

@section('js_end')
  <script type="text/javascript">
    jQuery(document).ready(function () {

      $('.date_range_picker').datepicker({
        todayHighlight: true,
        orientation: isRtl ? 'auto right' : 'auto left'
      });

      $('#tbl_dashboard').DataTable({
        pageLength: 10,
        // order: [[1, 'desc']],
        dom:
          '<"row mx-2"' +
          '<"col-md-2"<"me-3"l>>' +
          '<"col-md-10"<"dt-action-buttons text-xl-end text-lg-start text-md-end text-start d-flex align-items-center justify-content-end flex-md-row flex-column mb-3 mb-md-0 gap-3"fB>>' +
          '>t' +
          '<"row mx-2"' +
          '<"col-sm-12 col-md-6"i>' +
          '<"col-sm-12 col-md-6"p>' +
          '>',
        language: {
          sLengthMenu: 'Show _MENU_',
          search: '',
          searchPlaceholder: 'Search..'
        },
        buttons: [
          {
            extend: 'collection',
            className: 'btn btn-label-primary dropdown-toggle me-2 waves-effect waves-light',
            text: '<i class="mdi mdi-export-variant me-sm-1"></i> <span class="d-none d-sm-inline-block">Export</span>',
            buttons: [
              {
                extend: 'print',
                text: '<i class="mdi mdi-printer-outline me-1" ></i>Print',
                className: 'dropdown-item',
                exportOptions: {
                  columns: [1, 2, 3, 4],
                  // prevent avatar to be display
                  format: {
                    body: function (inner, coldex, rowdex) {
                      if (inner.length <= 0) return inner;
                      var el = $.parseHTML(inner);
                      var result = '';
                      $.each(el, function (index, item) {
                        if (item.classList !== undefined && item.classList.contains('user-name')) {
                          result = result + item.lastChild.firstChild.textContent;
                        } else if (item.classList !== undefined && item.classList.contains('no_export')) {
                          result = result;
                        } else if (item.innerText === undefined) {
                          result = result + item.textContent;
                        } else result = result + item.innerText;
                      });
                      return result;
                    }
                  }
                },
                customize: function (win) {
                  //customize print view for dark
                  $(win.document.body)
                    .css('color', config.colors.headingColor)
                    .css('border-color', config.colors.borderColor)
                    .css('background-color', config.colors.bodyBg);
                  $(win.document.body)
                    .find('table')
                    .addClass('compact')
                    .css('color', 'inherit')
                    .css('border-color', 'inherit')
                    .css('background-color', 'inherit');
                }
              },
              {
                extend: 'csv',
                text: '<i class="mdi mdi-file-document-outline me-1" ></i>Csv',
                className: 'dropdown-item',
                exportOptions: {
                  columns: [1, 2, 3, 4],
                  // prevent avatar to be display
                  format: {
                    body: function (inner, coldex, rowdex) {
                      if (inner.length <= 0) return inner;
                      var el = $.parseHTML(inner);
                      var result = '';
                      $.each(el, function (index, item) {
                        if (item.classList !== undefined && item.classList.contains('user-name')) {
                          result = result + item.lastChild.firstChild.textContent;
                        } else if (item.innerText === undefined) {
                          result = result + item.textContent;
                        } else result = result + item.innerText;
                      });
                      return result;
                    }
                  }
                }
              },
              {
                extend: 'excel',
                text: '<i class="mdi mdi-file-excel-outline me-1"></i>Excel',
                className: 'dropdown-item',
                exportOptions: {
                  columns: [1, 2, 3, 4],
                  // prevent avatar to be display
                  format: {
                    body: function (inner, coldex, rowdex) {
                      if (inner.length <= 0) return inner;
                      var el = $.parseHTML(inner);
                      var result = '';
                      $.each(el, function (index, item) {
                        if (item.classList !== undefined && item.classList.contains('user-name')) {
                          result = result + item.lastChild.firstChild.textContent;
                        } else if (item.innerText === undefined) {
                          result = result + item.textContent;
                        } else result = result + item.innerText;
                      });
                      return result;
                    }
                  }
                }
              },
              {
                extend: 'pdf',
                text: '<i class="mdi mdi-file-pdf-box me-1"></i>Pdf',
                className: 'dropdown-item',
                exportOptions: {
                  columns: [1, 2, 3, 4],
                  // prevent avatar to be display
                  format: {
                    body: function (inner, coldex, rowdex) {
                      if (inner.length <= 0) return inner;
                      var el = $.parseHTML(inner);
                      var result = '';
                      $.each(el, function (index, item) {
                        if (item.classList !== undefined && item.classList.contains('user-name')) {
                          result = result + item.lastChild.firstChild.textContent;
                        } else if (item.innerText === undefined) {
                          result = result + item.textContent;
                        } else result = result + item.innerText;
                      });
                      return result;
                    }
                  }
                }
              },
              {
                extend: 'copy',
                text: '<i class="mdi mdi-content-copy me-1" ></i>Copy',
                className: 'dropdown-item',
                exportOptions: {
                  columns: [1, 2, 3, 4],
                  // prevent avatar to be display
                  format: {
                    body: function (inner, coldex, rowdex) {
                      if (inner.length <= 0) return inner;
                      var el = $.parseHTML(inner);
                      var result = '';
                      $.each(el, function (index, item) {
                        if (item.classList !== undefined && item.classList.contains('user-name')) {
                          result = result + item.lastChild.firstChild.textContent;
                        } else if (item.innerText === undefined) {
                          result = result + item.textContent;
                        } else result = result + item.innerText;
                      });
                      return result;
                    }
                  }
                }
              }
            ]
          },
          {
            text: '<i class="mdi mdi-plus me-0 me-sm-1"></i><span class="d-none d-sm-inline-block">Add Dish</span>',
            className: 'add-new btn btn-primary waves-effect waves-light',
            attr: {
              'data-bs-toggle': 'offcanvas',
              'data-bs-target': '#offcanvasAddUser'
            }
          }
        ],
      });
    });
  </script>
@endsection
