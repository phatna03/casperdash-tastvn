@extends('tastvn/layouts/layoutMaster')

@section('title', 'Admin User')

@section('vendor-style')
  <link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css')}}">
  <link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css')}}">
  <link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.css')}}">
  <link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-checkboxes-jquery/datatables.checkboxes.css')}}">
  <link rel="stylesheet" href="{{asset('assets/vendor/libs/select2/select2.css')}}" />
  <link rel="stylesheet" href="{{asset('assets/vendor/libs/@form-validation/umd/styles/index.min.css')}}" />

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
@endsection

@section('page-script')
{{--  <script src="{{asset('assets/js/app-user-list.js')}}"></script>--}}
@endsection

@section('content')
  <h4 class="py-3 mb-4"><span class="text-muted fw-light">Admin /</span> Users</h4>

  <!-- Users List Table -->
  <div class="card">
    <div class="card-header border-bottom">
      <h5 class="card-title">List of users</h5>
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
          <th>Name</th>
          <th class="text-center">Email</th>
          <th></th>
        </tr>
        </thead>
        <tbody>
        @for($i=1;$i<=15;$i++)
          <tr>
            <td class="text-center">
              <div>{{$i}}</div>
            </td>
            <td>
              <span>User {{$i}}</span>
            </td>
            <td class="text-center">
              <div>user{{$i}}@casperdash.io</div>
            </td>
            <td class="text-center">
              <div class="dropdown">
                <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown"><i class="mdi mdi-dots-vertical"></i></button>
                <div class="dropdown-menu">
                  <a class="dropdown-item" href="javascript:void(0);" data-bs-toggle="offcanvas" data-bs-target="#offcanvasEditUser"><i class="mdi mdi-pencil-outline me-1"></i> Edit</a>
                  <a class="dropdown-item" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#deleteModal"><i class="mdi mdi-trash-can-outline me-1"></i> Delete</a>
                </div>
              </div>
            </td>
          </tr>
        @endfor
        </tbody>
      </table>
    </div>

    <!-- Offcanvas to add new user -->
    <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasAddUser" aria-labelledby="offcanvasAddUserLabel">
      <div class="offcanvas-header">
        <h5 id="offcanvasAddUserLabel" class="offcanvas-title">Add User</h5>
        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
      </div>
      <div class="offcanvas-body mx-0 flex-grow-0 h-100">
        <form class="add-new-user pt-0" id="addNewUserForm" onsubmit="return false">
          <div class="form-floating form-floating-outline mb-4">
            <input type="text" class="form-control" id="add-user-fullname" name="name" />
            <label for="add-user-fullname">Name</label>
          </div>
          <div class="form-floating form-floating-outline mb-4">
            <input type="email" class="form-control" id="add-user-email" name="email" />
            <label for="add-user-fullname">Email</label>
          </div>
          <div class="form-floating form-floating-outline mb-4">
            <input type="text" class="form-control" id="add-user-pwd" name="pwd" />
            <label for="add-user-fullname">Password</label>
          </div>
          <button class="btn btn-primary me-sm-3 me-1 data-submit"
                  {{--                  type="submit"--}}
                  type="button" onclick="submit_ok()" data-bs-dismiss="offcanvas"
          >Submit</button>
          <button type="reset" class="btn btn-outline-secondary" data-bs-dismiss="offcanvas">Cancel</button>
        </form>
      </div>
    </div>

    <!-- Offcanvas to edit user -->
    <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasEditUser" aria-labelledby="offcanvasEditUserLabel">
      <div class="offcanvas-header">
        <h5 id="offcanvasEditUserLabel" class="offcanvas-title">Edit User</h5>
        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
      </div>
      <div class="offcanvas-body mx-0 flex-grow-0 h-100">
        <form class="add-new-user pt-0" id="editUserForm" onsubmit="return false">
          <div class="form-floating form-floating-outline mb-4">
            <input type="text" class="form-control" id="edit-user-fullname" name="name" />
            <label for="add-user-fullname">Name</label>
          </div>
          <div class="form-floating form-floating-outline mb-4">
            <input type="email" class="form-control" id="add-user-email" name="email" />
            <label for="add-user-fullname">Email</label>
          </div>
          <div class="form-floating form-floating-outline mb-4">
            <input type="text" class="form-control" id="add-user-pwd" name="pwd" />
            <label for="add-user-fullname">Password</label>
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
      $('#tbl_dashboard').DataTable({
        pageLength: 25,
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
            text: '<i class="mdi mdi-plus me-0 me-sm-1"></i><span class="d-none d-sm-inline-block">Add User</span>',
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
