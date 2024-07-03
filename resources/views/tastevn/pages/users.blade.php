@extends('tastevn/layouts/layoutMaster')

@section('title', 'Admin - Manage Users')

@section('vendor-style')
  <link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css')}}">
@endsection

@section('vendor-script')
  <script src="{{asset('assets/vendor/libs/moment/moment.js')}}"></script>
  <script src="{{asset('assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js')}}"></script>
  <script type="text/javascript" src="https://cdn.datatables.net/plug-ins/1.10.16/sorting/datetime-moment.js"></script>
  <script type="text/javascript" src="https://cdn.datatables.net/plug-ins/1.10.21/dataRender/datetime.js"></script>
@endsection

@section('content')
  <h4 class="mb-2"><span class="text-muted fw-light">Admin /</span> Manage Users</h4>

  <div class="card">
    <div class="card-header border-bottom">
      <h5 class="card-title mb-0">List of users</h5>
    </div>

    <div class="card-datatable table-responsive">
      <table class="table table-hover" id="datatable-listing">
        <thead class="table-light">
        <tr>
          <th class="acm-th-first"></th>
          <th>Name</th>
          <th>Email / Phone / Zalo ID</th>
          <th>Access Restaurants</th>
          <th>Note</th>
          <th class="d-none"></th>
          <th class="d-none"></th>
        </tr>
        </thead>
      </table>
    </div>
  </div>

  <!-- offcanvas to add new item -->
  <div class="offcanvas offcanvas-start" tabindex="-1" id="offcanvas_add_item" aria-labelledby="offcanvas_add_item_label">
    <div class="offcanvas-header">
      <h5 id="offcanvas_add_item_label" class="offcanvas-title">Add User</h5>
      <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body mx-0 flex-grow-0 h-100">
      <form class="pt-0" onsubmit="return user_add(event, this);">
        <div class="form-floating form-floating-outline mb-4">
          <span class="text-dark fw-bold">Default password for new user:</span> <span class="badge bg-primary">tastevietnam</span>
        </div>
        <div class="form-floating form-floating-outline mb-4">
          <input type="text" class="form-control" id="add-item-name" name="name" />
          <label for="add-item-name">Name <b class="text-danger">*</b></label>
        </div>
        <div class="form-floating form-floating-outline mb-4">
          <input type="email" class="form-control" id="add-item-email" name="email" />
          <label for="add-item-email">Email <b class="text-danger">*</b></label>
        </div>
        <div class="form-floating form-floating-outline mb-4">
          <input type="text" class="form-control" id="add-item-phone" name="phone" />
          <label for="add-item-phone">Phone</label>
        </div>
        <div class="form-floating form-floating-outline mb-4">
          <div class="form-control text-center" id="add-item-status">
            <span class="form-check d-inline-block acm-mr-px-10">
              <input name="status" class="form-check-input" type="radio" value="inactive" id="add-item-radio-inactive" />
              <label class="form-check-label" for="add-item-radio-inactive">
                inactive
              </label>
            </span>
            <span class="form-check d-inline-block">
              <input name="status" class="form-check-input" type="radio" value="active" id="add-item-radio-active" checked />
              <label class="form-check-label" for="add-item-radio-active">
                active
              </label>
            </span>
          </div>
          <label for="add-item-status">Status</label>
        </div>
        <div class="form-floating form-floating-outline mb-4">
          <div class="form-control text-center" id="add-item-role">
            <span class="form-check d-inline-block acm-mr-px-10">
              <input name="role" class="form-check-input" type="radio" value="admin" id="add-item-radio-admin" onchange="user_role(this)" />
              <label class="form-check-label" for="add-item-radio-admin">
                admin
              </label>
            </span>
            <span class="form-check d-inline-block">
              <input name="role" class="form-check-input" type="radio" value="moderator" id="add-item-radio-moderator" onchange="user_role(this)" />
              <label class="form-check-label" for="add-item-radio-moderator">
                moderator
              </label>
            </span>
            <span class="form-check d-inline-block">
              <input name="role" class="form-check-input" type="radio" value="user" id="add-item-radio-user" onchange="user_role(this)" checked />
              <label class="form-check-label" for="add-item-radio-user">
                end-user
              </label>
            </span>
          </div>
          <label for="add-item-role">Role</label>
        </div>
        <div class="form-floating form-floating-outline mb-4">
          <textarea class="form-control h-px-100" id="add-item-note" name="note"></textarea>
          <label for="add-item-note">Note</label>
        </div>
        <div class="form-floating form-floating-outline mb-4">
          <div class="form-control" id="add-item-manage">
            <div class="form-check">
              <input class="form-check-input" type="checkbox" onchange="user_full_restaurants(this)"
                     name="access_full" id="add-item-manage-full" checked />
              <label class="form-check-label text-dark fw-bold" for="add-item-manage-full">
                Full access to all restaurants
              </label>
            </div>
          </div>
          <label for="add-item-manage" class="text-danger">Manage Restaurants</label>
        </div>
        <div class="form-floating form-floating-outline mb-4 d-none access-restaurants">
          <div class="form-control acm-wrap-selectize" id="add-item-manage-select">
            <select name="access_restaurants" multiple></select>
          </div>
          <label for="add-item-manage-select">Select Restaurants Can Access</label>
        </div>

        <div class="wrap-btns">
          @include('tastevn.htmls.form_button_loading')
          <button type="submit" class="btn btn-primary btn-ok btn-submit acm-float-right" >Submit</button>
          <button type="button" class="btn btn-outline-secondary btn-ok btn-cancel" data-bs-dismiss="offcanvas">Cancel</button>
        </div>

      </form>
    </div>
  </div>
  <!-- offcanvas to edit item -->
  <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvas_edit_item" aria-labelledby="offcanvas_edit_item_label">
    <div class="offcanvas-header">
      <h5 id="offcanvas_edit_item_label" class="offcanvas-title">Edit User</h5>
      <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body mx-0 flex-grow-0 h-100">
      <form class="pt-0" onsubmit="return user_edit(event, this);">
        <div class="form-floating form-floating-outline mb-4">
          <input type="text" class="form-control" id="edit-item-name" name="name" />
          <label for="edit-item-name">Name</label>
        </div>
        <div class="form-floating form-floating-outline mb-4">
          <input type="email" class="form-control" id="edit-item-email" name="email" />
          <label for="edit-item-email">Email <b class="text-danger">*</b></label>
        </div>
        <div class="form-floating form-floating-outline mb-4">
          <input type="text" class="form-control" id="edit-item-phone" name="phone" />
          <label for="edit-item-phone">Phone</label>
        </div>
        <div class="form-floating form-floating-outline mb-4">
          <div class="form-control text-center" id="edit-item-status">
            <span class="form-check d-inline-block acm-mr-px-10">
              <input name="status" class="form-check-input" type="radio" value="inactive" id="edit-item-radio-inactive" />
              <label class="form-check-label" for="edit-item-radio-inactive">
                inactive
              </label>
            </span>
            <span class="form-check d-inline-block">
              <input name="status" class="form-check-input" type="radio" value="active" id="edit-item-radio-active" checked />
              <label class="form-check-label" for="edit-item-radio-active">
                active
              </label>
            </span>
          </div>
          <label for="edit-item-status">Status</label>
        </div>
        <div class="form-floating form-floating-outline mb-4">
          <div class="form-control text-center" id="edit-item-role">
            <span class="form-check d-inline-block acm-mr-px-10">
              <input name="role" class="form-check-input" type="radio" value="admin" id="edit-item-radio-admin" onchange="user_role(this)" />
              <label class="form-check-label" for="edit-item-radio-admin">
                admin
              </label>
            </span>
            <span class="form-check d-inline-block">
              <input name="role" class="form-check-input" type="radio" value="moderator" id="edit-item-radio-moderator" onchange="user_role(this)" />
              <label class="form-check-label" for="edit-item-radio-moderator">
                moderator
              </label>
            </span>
            <span class="form-check d-inline-block">
              <input name="role" class="form-check-input" type="radio" value="user" id="edit-item-radio-user" checked onchange="user_role(this)" />
              <label class="form-check-label" for="edit-item-radio-user">
                end-user
              </label>
            </span>
          </div>
          <label for="edit-item-role">Role</label>
        </div>
        <div class="form-floating form-floating-outline mb-4">
          <textarea class="form-control h-px-100" id="edit-item-note" name="note"></textarea>
          <label for="edit-item-note">Note</label>
        </div>
        <div class="form-floating form-floating-outline mb-4">
          <div class="form-control" id="edit-item-manage">
            <div class="form-check">
              <input class="form-check-input" type="checkbox" onchange="user_full_restaurants(this)"
                     name="access_full" id="edit-item-manage-full" checked />
              <label class="form-check-label text-dark fw-bold" for="edit-item-manage-full">
                Full access to all restaurants
              </label>
            </div>
          </div>
          <label for="edit-item-manage" class="text-danger">Manage Restaurants</label>
        </div>
        <div class="form-floating form-floating-outline mb-4 d-none access-restaurants">
          <div class="form-control acm-wrap-selectize" id="edit-item-manage-select">
            <select name="access_restaurants" multiple></select>
          </div>
          <label for="edit-item-manage-select">Select Restaurants Can Access</label>
        </div>

        <div class="wrap-btns">
          @include('tastevn.htmls.form_button_loading')
          <button type="submit" class="btn btn-primary btn-ok btn-submit acm-float-right" >Submit</button>
          <button type="button" class="btn btn-outline-secondary btn-ok btn-cancel" data-bs-dismiss="offcanvas">Cancel</button>
        </div>

        <input type="hidden" name="item" />
      </form>
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
            <button type="button" class="btn btn-primary btn-ok btn-submit acm-float-right" onclick="user_delete(this)">Submit</button>
            <button type="button" class="btn btn-outline-secondary btn-ok btn-cancel" data-bs-dismiss="modal">Cancel</button>
          </div>

          <input type="hidden" name="item" />
        </div>
      </div>
    </div>
  </div>
  <!-- modal confirm to restore item -->
  <div class="modal animate__animated animate__rollIn" id="modal_restore_item" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title">Restore Confirmation?</h4>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="row">
            <div class="col mb-12 mt-2">
              <div class="alert alert-danger"></div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <div class="wrap-btns">
            @include('tastevn.htmls.form_button_loading')
            <button type="button" class="btn btn-primary btn-ok btn-submit acm-float-right" onclick="user_restore(this)">Submit</button>
            <button type="button" class="btn btn-outline-secondary btn-ok btn-cancel" data-bs-dismiss="modal">Cancel</button>
          </div>

          <input type="hidden" name="item" />
        </div>
      </div>
    </div>
  </div>
  <!-- modal confirm to zalo sync -->
  <div class="modal animate__animated animate__rollIn" id="modal_zalo_sync" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title">Sync Zalo User</h4>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form onsubmit="return user_zalo(event, this);">
          <div class="modal-body">
            <div class="row">
              <div class="col mb-12 mt-2">
                <div class="form-floating form-floating-outline mb-1">
                  <div class="form-control acm-wrap-selectize" id="select-item-zalo-user">
                    <select name="zalo" class="opt_selectize"
                            data-placeholder="Please choose Zalo user..."
                    >
                      <option>Please choose Zalo user...</option>
                      @if(count($pageConfigs['zalos']))
                        @php
                          foreach($pageConfigs['zalos'] as  $zalo):
                        $zalo_name = empty($zalo->user_alias) ? $zalo->display_name : $zalo->display_name . ' (' . $zalo->user_alias . ')';
                          if (!empty($zalo->user_phone)) {
                              $zalo_name .= ' - ' . $zalo->user_phone;
                          }
                        @endphp
                          <option value="{{$zalo->id}}">{{$zalo_name}}</option>
                        @endforeach
                      @endif
                    </select>
                  </div>
                  <label for="select-item-zalo-user" class="text-danger">Zalo Users</label>
                </div>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <div class="wrap-btns">
              @include('tastevn.htmls.form_button_loading')
              <button type="submit" class="btn btn-primary btn-ok btn-submit acm-float-right">Submit</button>
              <button type="button" class="btn btn-outline-secondary btn-ok btn-cancel" data-bs-dismiss="modal">Cancel</button>
            </div>

            <input type="hidden" name="item" />
          </div>
        </form>
      </div>
    </div>
  </div>
  <!-- modal confirm to zalo message -->
  <div class="modal animate__animated animate__rollIn" id="modal_zalo_message" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title">Send Zalo Message</h4>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form onsubmit="return user_zalo_message(event, this);">
          <div class="modal-body">
            <div class="row">
              <div class="col mb-12 mt-2">
                <div class="form-control">
                  <span class="form-check d-inline-block acm-mr-px-10">
                    <input name="type" class="form-check-input"
                           type="radio" value="request" id="zalo-message-send-request" checked="checked" />
                    <label class="form-check-label" for="zalo-message-send-request">
                      Gửi tin nhắn yêu cầu thông tin người dùng
                    </label>
                  </span>
                  <span class="form-check d-inline-block">
                    <input name="type" class="form-check-input"
                           type="radio" value="custom" id="zalo-message-send-request" />
                    <label class="form-check-label" for="zalo-message-send-request">
                      Gửi tin nhắn với nội dung bên dưới
                    </label>
                  </span>
                </div>
                <div class="mt-2">
                  <textarea name="message" class="form-control h-px-100" placeholder="Nội dung tin nhắn..."></textarea>
                </div>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <div class="wrap-btns">
              @include('tastevn.htmls.form_button_loading')
              <button type="submit" class="btn btn-primary btn-ok btn-submit acm-float-right">Submit</button>
              <button type="button" class="btn btn-outline-secondary btn-ok btn-cancel" data-bs-dismiss="modal">Cancel</button>
            </div>

            <input type="hidden" name="item" />
          </div>
        </form>
      </div>
    </div>
  </div>
@endsection

@section('js_end')
  <script type="text/javascript">
    var $ = jQuery.noConflict();
    $(document).ready(function() {

      //selectize
      var selectize_restaurant_access = $('.access-restaurants select');
      selectize_restaurant_access.selectize({
        valueField: 'id',
        labelField: 'name',
        searchField: 'name',
        //multi_selectize
        plugins: ["remove_button"],
        preload: true,
        clearCache: function (template) {},
        load: function (query, callback) {
          jQuery.ajax({
            url: acmcfs.link_base_url + '/admin/restaurant/selectize',
            type: 'post',
            data: {
              keyword: query,
              _token: acmcfs.var_csrf,
            },
            complete: function (xhr, textStatus) {
              var rsp = xhr.responseJSON;

              if (xhr.status == 200) {
                selectize_restaurant_access.options = rsp.items;
                callback(rsp.items);
              }
            },
          });
        }
      });

      //datatable
      datatable_listing = $('#datatable-listing').DataTable(Object.assign(datatable_cfs, acmcfs.datatable_init));

    });

    var datatable_listing;
    var datatable_cfs = {
      "ajax": "{{url('datatable/user')}}",
      "createdRow": function( row, data, dataIndex ) {
        $(row).attr('data-id', data.id);
        $(row).attr('data-name', data.name);
        $(row).attr('data-email', data.email);
        $(row).attr('data-phone', data.phone);
        $(row).attr('data-status', data.status);
        $(row).attr('data-role', data.role);
        $(row).attr('data-note', data.note);
        $(row).attr('data-access-full', data.access_full);
        $(row).attr('data-access-ids', data.access_ids);
        $(row).attr('data-access-texts', data.access_texts);
        $(row).attr('data-zalo_id', data.zalo_id);
      },
      "columns": [
        //stt
        {data: 'DT_RowIndex', name: 'DT_RowIndex' , orderable: false, searchable: false},
        {data: 'name', name: 'name'},
        {data: 'email'},
        {data: 'access_texts', name: 'access_texts'},
        {data: 'note', name: 'note'},
        {data: 'status', name: 'status'},
        {data: 'role', name: 'role'},
      ],
      columnDefs: [
        {
          targets: 0,
          render: function (data, type, full, meta) {
            var html = '';
            var html_edit = '';
            var html_delete = '';
            var html_zalo = '';

            var todo = false;
            var user_id = parseInt($('#acmcfs_user_id').val());
            var user_role = $('#acmcfs_user_role').val();

            if (user_role == 'superadmin') {
              todo = true;
            } else if (user_role == 'admin') {
              if (user_id == parseInt(full['id'])) {
                todo = true;
              }
            }
            @if($viewer->is_super_admin())
              todo = true;
            @endif

            if (full['role'] == 'moderator' || full['role'] == 'user') {
              todo = true;
            }

            if (todo) {
              html_edit = '<a class="dropdown-item" href="javascript:void(0);" data-bs-toggle="offcanvas" data-bs-target="#offcanvas_edit_item" onclick="user_edit_prepare(this)"><i class="mdi mdi-pencil-outline me-1"></i> Edit</a>';
              html_zalo = '<a class="dropdown-item" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#modal_zalo_sync" onclick="user_zalo_prepare(this)"><i class="mdi mdi-pencil-outline me-1"></i> Sync Zalo User</a>';
              html_delete = '<a class="dropdown-item" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#modal_delete_item" onclick="user_delete_confirm(this)"><i class="mdi mdi-trash-can-outline me-1"></i> Delete</a>';

              if (user_id == parseInt(full['id'])) {
                html_delete = '';
              }

              html = '<div class="d-inline-block dropdown acm-mr-px-5">' +
                '<button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown"><i class="mdi mdi-dots-vertical"></i></button>' +
                '<div class="dropdown-menu">' +
                html_edit +
                html_zalo +
                html_delete +
                '</div>' +
                '</div>';
            }

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
            var html_name = '';
            var html_role = '';
            var html_status = '';
            var color = 'bg-secondary';

            html_name = '<span>' + full['name'] + '</span>';

            if (full['role'] == 'superadmin') {
              color = 'bg-primary';
            } else if (full['role'] == 'admin') {
              color = 'bg-warning';
            } else if (full['role'] == 'moderator') {
              color = 'bg-info';
            }

            html_role = '<span class="badge ' + color + '">' + full['role'] + '</span>';

            if (full['status'] != 'active') {
              html_status = '<span class="badge bg-danger">' + full['status'] + '</span>';
            }

            html = '<div>' + html_name +
              '</div>' +
              '<div>' + html_role + html_status +
              '</div>';

            return ('<div>' + html + '</div>');
          }
        },
        {
          targets: 2,
          render: function (data, type, full, meta) {
            var html = '';
            var html_email = '';
            var html_phone = '';
            var html_zalo = '';

            html_email = '<span>' + full['email'] + '</span>';

            if (full['phone'] && full['phone'] !== '' && full['phone'] !== 'null') {
              html_phone = '<span>' + full['phone'] + '</span>';
            }

            if (full['zalo_user_id'] && full['zalo_user_id'] !== '' && full['zalo_user_id'] !== 'null') {
              html_zalo = '<button type="button" class="btn btn-sm btn-primary p-1 acm-mr-px-5" onclick="user_zalo_message_prepare(this)"><i class="mdi mdi-message fs-6"></i></button>' +
                '<span>Zalo ID: ' + full['zalo_user_id'] + '</span>';
            }

            html = '<div>' + html_email +
              '</div>' +
              '<div>' + html_phone +
              '</div>' +
              '<div>' + html_zalo +
              '</div>';

            return ('<div>' + html + '</div>');
          }
        },
        {
          targets: 5,
          className: 'd-none',
        },
        {
          targets: 6,
          className: 'd-none',
        },
      ],
      buttons: [
        {
          text: '<i class="mdi mdi-plus me-0 me-sm-1"></i><span class="d-none d-sm-inline-block">Add User</span>',
          className: 'add-new btn btn-primary waves-effect waves-light acm-mr-px-10',
          attr: {
            'data-bs-toggle': 'offcanvas',
            'data-bs-target': '#offcanvas_add_item',
            'onclick': 'setTimeout(function () { $("#offcanvas_add_item form input[name=name]").focus(); }, 500)',
          }
        },
        {
          text: '<i class="mdi mdi-reload me-0 me-sm-1"></i><span class="d-none d-sm-inline-block">Refresh</span>',
          className: 'add-new btn btn-dark waves-effect waves-light',
          attr: {
            'onclick': 'datatable_refresh()',
          }
        }
      ],
    };

  </script>
@endsection
