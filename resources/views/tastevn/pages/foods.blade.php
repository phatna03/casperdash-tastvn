@extends('tastevn/layouts/layoutMaster')

@section('title', 'Admin - Dishes')

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
  <h4 class="py-3 mb-4"><span class="text-muted fw-light">Admin /</span> Dishes</h4>

  <div class="card">
    <div class="card-header border-bottom">
      <h5 class="card-title mb-0">List of dishes</h5>
    </div>

    <div class="card-datatable table-responsive">
      <table class="table table-hover" id="datatable-listing">
        <thead class="table-light">
        <tr>
          <th></th>
          <th>Name</th>
          <th>Total restaurants</th>
          <th>Latest updated</th>
          <th></th>
        </tr>
        </thead>
      </table>
    </div>
  </div>

  <!-- offcanvas to add new item -->
  <div class="offcanvas offcanvas-start" tabindex="-1" id="offcanvas_add_item" aria-labelledby="offcanvas_add_item_label">
    <div class="offcanvas-header">
      <h5 id="offcanvas_add_item_label" class="offcanvas-title">Add Dish</h5>
      <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body mx-0 flex-grow-0 h-100">
      <form class="pt-0" onsubmit="return food_add(event, this);">
        <div class="row wrap-photo-upload">
          <div class="col-lg-6">
            <div class="form-floating form-floating-outline mb-4">
              <input type="file" class="form-control" id="add-item-file" name="photo" />
              <label for="add-item-file">Photo</label>
            </div>
          </div>
          <div class="col-lg-6">
            <div class="wrap-photo-preview">
              <div class="text-center w-auto">
                <img width="80" src="{{url('custom/img/food_photo.jpg')}}" />
              </div>
            </div>
          </div>
        </div>
        <div class="form-floating form-floating-outline mb-4">
          <input type="text" class="form-control" id="add-item-name" name="name" />
          <label for="add-item-name">Name <b class="text-danger">*</b></label>
        </div>
        <div class="form-floating form-floating-outline mb-4">
          <div class="form-control acm-height-px-auto p-1" id="add-item-food-ingredient-custom">
            <div class="wrap-add-item-ingredients">
              <div class="wrap-ingredients wrap-custom p-1">
                <div class="ingredient-item-add mb-1 acm-text-right">
                  <button class="btn btn-sm btn-info me-sm-3 me-1" type="button" onclick="ingredient_item_add(this)"><i class="mdi mdi-plus me-0 me-sm-1"></i> Add Ingredient</button>
                </div>
              </div>
              <div class="wrap-ingredients wrap-fetch p-1">

              </div>
            </div>
          </div>
          <label for="add-item-food-ingredient-custom" class="text-danger">Ingredients</label>
        </div>
        <button class="btn btn-primary me-sm-3 me-1 data-submit" type="submit">Submit</button>
        <button type="reset" class="btn btn-outline-secondary" data-bs-dismiss="offcanvas">Cancel</button>
      </form>
    </div>
  </div>
  <!-- offcanvas to edit item -->
  <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvas_edit_item" aria-labelledby="offcanvas_edit_item_label">
    <div class="offcanvas-header">
      <h5 id="offcanvas_edit_item_label" class="offcanvas-title">Edit Dish</h5>
      <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body mx-0 flex-grow-0 h-100">
      <form class="pt-0" onsubmit="return food_edit(event, this);">
        <div class="row wrap-photo-upload">
          <div class="col-lg-6">
            <div class="form-floating form-floating-outline mb-4">
              <input type="file" class="form-control" id="edit-item-file" name="photo" />
              <label for="edit-item-file">Photo</label>
            </div>
          </div>
          <div class="col-lg-6">
            <div class="wrap-photo-preview">
              <div class="text-center w-auto">
                <img width="80" src="{{url('custom/img/food_photo.jpg')}}" />
              </div>
            </div>
          </div>
        </div>
        <div class="form-floating form-floating-outline mb-4">
          <input type="text" class="form-control" id="edit-item-name" name="name" />
          <label for="edit-item-name">Name <b class="text-danger">*</b></label>
        </div>
        <div class="form-floating form-floating-outline mb-4">
          <div class="form-control acm-height-px-auto p-1" id="edit-item-food-ingredient-custom">
            <div class="wrap-add-item-ingredients">
              <div class="wrap-ingredients wrap-custom p-1">
                <div class="ingredient-item-add mb-1 acm-text-right">
                  <button class="btn btn-sm btn-info me-sm-3 me-1" type="button" onclick="ingredient_item_add(this)"><i class="mdi mdi-plus me-0 me-sm-1"></i> Add Ingredient</button>
                </div>
              </div>
              <div class="wrap-ingredients wrap-fetch p-1">

              </div>
            </div>
          </div>
          <label for="edit-item-food-ingredient-custom" class="text-danger">Ingredients</label>
        </div>
        <button class="btn btn-primary me-sm-3 me-1 data-submit" type="submit">Submit</button>
        <button type="reset" class="btn btn-outline-secondary" data-bs-dismiss="offcanvas">Cancel</button>
        <input type="hidden" name="item" />
      </form>
    </div>
  </div>
@endsection

@section('js_end')
  <script type="text/javascript">
    var $ = jQuery.noConflict();
    $(document).ready(function() {

      //photo upload
      var formAdd = $('#offcanvas_add_item form');
      formAdd.find('input[type=file]').change(function () {
        var input = this;
        var url = $(this).val();
        var ext = url.substring(url.lastIndexOf('.') + 1).toLowerCase();
        var filed = jQuery(this);
        var valid_exts = ['png', 'jpeg', 'jpg', 'webp'];

        if (!valid_exts.includes(ext)) {
          filed.val("");
          message_from_toast('error', acmcfs.message_title_error, 'Error image upload');
          return false;
        }

        if (input.files && input.files[0]) {
          var filesAmount = input.files.length;
          for (var i = 0; i < filesAmount; i++) {
            var reader = new FileReader();
            reader.onload = function (e) {
              formAdd.find('.wrap-photo-preview img').attr('src', e.target.result);
            }
            reader.readAsDataURL(input.files[i]);
          }
        }
      });

      var formEdit = $('#offcanvas_edit_item form');
      formEdit.find('input[type=file]').change(function () {
        var input = this;
        var url = $(this).val();
        var ext = url.substring(url.lastIndexOf('.') + 1).toLowerCase();
        var filed = jQuery(this);
        var valid_exts = ['png', 'jpeg', 'jpg', 'webp'];

        if (!valid_exts.includes(ext)) {
          filed.val("");
          message_from_toast('error', acmcfs.message_title_error, 'Error image upload');
          return false;
        }

        if (input.files && input.files[0]) {
          var filesAmount = input.files.length;
          for (var i = 0; i < filesAmount; i++) {
            var reader = new FileReader();
            reader.onload = function (e) {
              formEdit.find('.wrap-photo-preview img').attr('src', e.target.result);
            }
            reader.readAsDataURL(input.files[i]);
          }
        }
      });

      //datatable
      datatable_listing = $('#datatable-listing').DataTable(Object.assign(datatable_cfs, acmcfs.datatable_init));

    });

    var datatable_listing;
    var datatable_cfs = {
      "ajax": "{{ url('datatable/foods') }}",
      "createdRow": function( row, data, dataIndex ) {
        $(row).attr('data-id', data.id);
        $(row).attr('data-name', data.name);
        $(row).attr('data-photo', data.photo);
      },
      "columns": [
        //stt
        {data: 'DT_RowIndex', name: 'DT_RowIndex' , orderable: false, searchable: false},
        {data: 'name', name: 'name'},
        {data: 'count_restaurants', name: 'count_restaurants'},
        {data: 'updated_at', name: 'updated_at'}
      ],
      columnDefs: [
        {
          targets: 1,
          render: function (data, type, full, meta) {
            var img_src = acmcfs.link_base_url + '/custom/img/food_photo.jpg';
            if (full['photo'] && full['photo'] !== '' && full['photo'] != 'null') {
              img_src = acmcfs.link_base_url + full['photo'];
            }
            return (
              '<div class="cursor-pointer" onclick="food_info(' + full['id'] + ')">' +
              '<span class="acm-mr-px-10">' +
              '<img width="50" src="' + img_src + '" />' +
              '</span>' +
              '<span>' + full['name'] + '</span>' +
              '</div>'
            );
          }
        },
        {
          targets: 3,
          render: $.fn.dataTable.render.moment('YYYY-MM-DDTHH:mm:ss.SSSSZ', 'DD/MM/YY H:mm:ss' )
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
              '<a class="dropdown-item" href="javascript:void(0);" data-bs-toggle="offcanvas" data-bs-target="#offcanvas_edit_item" onclick="food_edit_prepare(this)"><i class="mdi mdi-pencil-outline me-1"></i> Edit</a>' +
              // '<a class="dropdown-item" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#modalDeleteItem" onclick="food_delete_confirm(this)"><i class="mdi mdi-trash-can-outline me-1"></i> Delete</a>' +
              '</div>' +
              '</div>'
            );
          }
        }
      ],
      buttons: [
        {
          text: '<i class="mdi mdi-plus me-0 me-sm-1"></i><span class="d-none d-sm-inline-block">Add Dish</span>',
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
