@extends('tastevn/layouts/layoutMaster')

@section('title', 'Admin - Restaurants')

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
  <h4 class="mb-2"><span class="text-muted fw-light">Admin /</span> Restaurants</h4>

  <div class="card">
    <div class="card-header border-bottom">
      <h5 class="card-title mb-0">List of restaurants</h5>
    </div>

    <div class="card-datatable table-responsive">
      <table class="table table-hover" id="datatable-listing">
        <thead class="table-light">
        <tr>
          <th class="acm-th-first"></th>
          <th>Name</th>
          <th>Model</th>
          <th>Total sensors / dishes</th>
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
      <h5 id="offcanvas_add_item_label" class="offcanvas-title">Add Restaurant</h5>
      <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body mx-0 flex-grow-0 h-100">
      <form class="pt-0" onsubmit="return restaurant_add(event, this);">
        <div class="form-floating form-floating-outline mb-4">
          <input type="text" class="form-control" id="add-item-name" name="name" />
          <label for="add-item-name">Name <b class="text-danger">*</b></label>
        </div>
        <div class="form-floating form-floating-outline mb-4">
          <input type="text" class="form-control" id="add-item-model-name" name="model_name" />
          <label for="add-item-model-name">Model name</label>
        </div>
        <div class="form-floating form-floating-outline mb-4">
          <input type="text" class="form-control" id="add-item-model-version" name="model_version" />
          <label for="add-item-model-version">Model version</label>
        </div>
        <div class="form-floating form-floating-outline mb-4">
          <div class="form-control" id="add-item-model-scan">
            <div class="form-check">
              <input class="form-check-input" type="checkbox" name="model_scan" id="add-item-model-scan-yes" />
              <label class="form-check-label text-dark fw-bold" for="add-item-model-scan-yes">Yes</label>
            </div>
          </div>
          <label for="add-item-model-scan" class="text-danger">Model scan?</label>
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
      <h5 id="offcanvas_edit_item_label" class="offcanvas-title">Edit Restaurant</h5>
      <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body mx-0 flex-grow-0 h-100">
      <form class="pt-0" onsubmit="return restaurant_edit(event, this);">
        <div class="form-floating form-floating-outline mb-4">
          <input type="text" class="form-control" id="edit-item-name" name="name" />
          <label for="edit-item-name">Name <b class="text-danger">*</b></label>
        </div>
        <div class="form-floating form-floating-outline mb-4">
          <input type="text" class="form-control" id="edit-item-model-name" name="model_name" />
          <label for="edit-item-model-name">Model name</label>
        </div>
        <div class="form-floating form-floating-outline mb-4">
          <input type="text" class="form-control" id="edit-item-model-version" name="model_version" />
          <label for="edit-item-model-version">Model version</label>
        </div>
        <div class="form-floating form-floating-outline mb-4">
          <div class="form-control" id="edit-item-model-scan">
            <div class="form-check">
              <input class="form-check-input" type="checkbox" name="model_scan" id="edit-item-model-scan-yes" />
              <label class="form-check-label text-dark fw-bold" for="edit-item-model-scan-yes">Yes</label>
            </div>
          </div>
          <label for="edit-item-model-scan" class="text-danger">Model scan?</label>
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
            <button type="button" class="btn btn-primary btn-ok btn-submit acm-float-right" onclick="restaurant_delete(this)">Submit</button>
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
            <button type="button" class="btn btn-primary btn-ok btn-submit acm-float-right" onclick="restaurant_restore(this)">Submit</button>
            <button type="button" class="btn btn-outline-secondary btn-ok btn-cancel" data-bs-dismiss="modal">Cancel</button>
          </div>

          <input type="hidden" name="item" />
        </div>
      </div>
    </div>
  </div>
  <!-- modal food import -->
  <div class="modal animate__animated animate__rollIn" id="modal_food_import" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content">
        <form onsubmit="return restaurant_food_import(event, this);">
          <div class="modal-header">
            <h4 class="modal-title">Update Food Category & Photo</h4>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="row">
              <a class="text-primary fw-bold" href="{{url('import_food_to_restaurant.xlsx')}}" download="" style="margin-bottom: 10px;">Download excel template file</a>

              <div class="col-12">
                <input name="file" type="file"
                       accept="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel"
                       required onchange="excel_check(this)" class="form-control"
                />
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <div class="wrap-btns">
              @include('tastevn.htmls.form_button_loading')
              <button type="submit" class="btn btn-primary btn-ok btn-submit acm-float-right">Submit</button>
              <button type="button" class="btn btn-outline-secondary btn-ok btn-cancel" data-bs-dismiss="modal">Cancel</button>
            </div>

            <input type="hidden" name="restaurant_parent_id" />
          </div>
        </form>
      </div>
    </div>
  </div>
  <!-- modal food remove -->
  <div class="modal fade modal-second" id="modal_food_remove" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title">Remove Confirmation?</h4>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="row">
            <div class="col mb-12 mt-2">
              <div class="alert alert-danger">Are you sure you want to remove this dish from the restaurant?</div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <div class="wrap-btns">
            @include('tastevn.htmls.form_button_loading')
            <button type="button" class="btn btn-primary btn-ok btn-submit acm-float-right" onclick="restaurant_food_remove()">Submit</button>
            <button type="button" class="btn btn-outline-secondary btn-ok btn-cancel" data-bs-dismiss="modal">Cancel</button>
          </div>

          <input type="hidden" name="restaurant_parent_id" />
          <input type="hidden" name="food_id" />
        </div>
      </div>
    </div>
  </div>
  <!-- modal food update -->
  <div class="modal fade modal-second" id="modal_food_update" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title">Confirmation?</h4>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="row">
            <div class="col-lg-12 mt-2 mb-2 d-none wrap_updated wrap_live_group">
              <label class="text-dark fw-bold">Roboflow Confidence</label>
              <div class="form-check mt-3">
                <input name="live_group" class="form-check-input live_group live_group_1" type="radio" value="1" id="defaultRadio1" />
                <label class="form-check-label" for="defaultRadio1">
                  Super Confidence
                </label>
              </div>
              <div class="form-check">
                <input name="live_group" class="form-check-input live_group live_group_2" type="radio" value="2" id="defaultRadio2" />
                <label class="form-check-label" for="defaultRadio2">
                  Less Training
                </label>
              </div>
              <div class="form-check">
                <input name="live_group" class="form-check-input live_group live_group_3" type="radio" value="3" id="defaultRadio3" />
                <label class="form-check-label" for="defaultRadio3">
                  Not Trained Yet
                </label>
              </div>
            </div>
            <div class="col-lg-12 mt-2 mb-2 d-none wrap_updated wrap_model_name">
              <label class="text-dark fw-bold">Roboflow Model Name</label>
              <input type="text" class="form-control text-center" name="model_name" />
            </div>
            <div class="col-lg-12 mt-2 mb-2 d-none wrap_updated wrap_model_version">
              <label class="text-dark fw-bold">Roboflow Model Version</label>
              <input type="text" class="form-control text-center" name="model_version" />
            </div>
            <div class="col-lg-12 mt-2 mb-2 d-none wrap_updated wrap_confidence">
              <label class="text-dark fw-bold">Roboflow Food Confidence</label>
              <input type="text" class="form-control text-center fnumber" onblur="input_number_min_30_max_100(this);" name="confidence" />
            </div>
            <div class="col-lg-12 mt-2 mb-2 d-none wrap_updated wrap_category_name">
              <label class="text-dark fw-bold">Category Name</label>
              <input type="text" class="form-control text-center" name="category_name" />
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <div class="wrap-btns">
            @include('tastevn.htmls.form_button_loading')
            <button type="button" class="btn btn-primary btn-ok btn-submit acm-float-right" onclick="restaurant_food_update()">Submit</button>
            <button type="button" class="btn btn-outline-secondary btn-ok btn-cancel" data-bs-dismiss="modal">Cancel</button>
          </div>

          <input type="hidden" name="restaurant_parent_id" />
          <input type="hidden" name="food_id" />
          <input type="hidden" name="type" />
        </div>
      </div>
    </div>
  </div>
  <!-- modal item info -->
  <div class="modal animate__animated animate__rollIn" id="modal_info_item" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl acm-modal-xxl" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title text-danger fw-bold"></h4>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">

        </div>

        <input type="hidden" name="restaurant_parent_id"/>
      </div>
    </div>
  </div>
  <!-- modal food add -->
  <div class="modal fade modal-second" id="modal_food_add" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title">Add Dish</h4>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form class="pt-0" onsubmit="return restaurant_food_add(event, this);">
          <div class="modal-body">
            <div class="row">
              <div class="col mb-12 mt-2">
                <div class="form-floating form-floating-outline mb-4">
                  <input type="text" class="form-control" id="food-add-name" name="name" required />
                  <label for="food-add-name">Name <b class="text-danger">*</b></label>
                </div>

                <div class="form-floating form-floating-outline mb-4">
                  <input type="text" class="form-control" id="food-add-category-name" name="category_name"  />
                  <label for="food-add-category-name">Category Name</label>
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

            <input type="hidden" name="restaurant_parent_id" />
          </div>
        </form>
      </div>
    </div>
  </div>
  <!-- modal food recipe -->
  <div class="modal fade modal-second" id="modal_food_ingredient_recipe" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title">Edit Recipe Ingredients</h4>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form class="pt-0" onsubmit="return restaurant_food_recipe(event, this);">
          <div class="modal-body">
            <div class="row">
              <div class="col mb-12 mt-2">
                <div class="wrap-add-item-ingredients">
                  <div class="wrap-ingredients wrap-custom p-1">
                    <div class="ingredient-item-add mb-1 acm-text-right">
                      <button class="btn btn-sm btn-info me-sm-3 me-1" type="button" onclick="recipe_item_add(this)"><i class="mdi mdi-plus me-0 me-sm-1"></i> Add Ingredient</button>
                    </div>
                  </div>
                  <div class="wrap-ingredients wrap-fetch p-1">

                  </div>
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

            <input type="hidden" name="restaurant_parent_id" />
            <input type="hidden" name="food_id" />
          </div>
        </form>
      </div>
    </div>
  </div>
  <!-- modal food robot -->
  <div class="modal fade modal-second" id="modal_food_ingredient_robot" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title">Edit Roboflow Ingredients</h4>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form class="pt-0" onsubmit="return restaurant_food_robot(event, this);">
          <div class="modal-body">
            <div class="row">
              <div class="col mb-12 mt-2">
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
            </div>
          </div>
          <div class="modal-footer">
            <div class="wrap-btns">
              @include('tastevn.htmls.form_button_loading')
              <button type="submit" class="btn btn-primary btn-ok btn-submit acm-float-right">Submit</button>
              <button type="button" class="btn btn-outline-secondary btn-ok btn-cancel" data-bs-dismiss="modal">Cancel</button>
            </div>

            <input type="hidden" name="restaurant_parent_id" />
            <input type="hidden" name="food_id" />
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

      //datatable
      datatable_listing = $('#datatable-listing').DataTable(Object.assign(datatable_cfs, acmcfs.datatable_init));

    });

    var datatable_listing;
    var datatable_cfs = {
      "ajax": "{{ url('datatable/restaurant') }}",
      "createdRow": function( row, data, dataIndex ) {
        $(row).attr('data-id', data.id);
        $(row).attr('data-name', data.name);
        $(row).attr('data-model_name', data.model_name);
        $(row).attr('data-model_version', data.model_version);
        $(row).attr('data-model_scan', data.model_scan);
      },
      "columns": [
        {data: 'DT_RowIndex', name: 'DT_RowIndex' , orderable: false, searchable: false},
        {data: 'name', name: 'name'},
        {data: 'model_name', name: 'model_name'},
        {data: 'model_version', name: 'model_version'},
        {data: 'count_sensors', name: 'count_sensors'},
        {data: 'count_foods', name: 'count_foods'},
      ],
      columnDefs: [
        {
          targets: 0,
          render: function (data, type, full, meta) {
            var html = '';

            @if($viewer->is_admin())
              html += '<div class="d-inline-block dropdown acm-mr-px-5">' +
              '<button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown"><i class="mdi mdi-dots-vertical"></i></button>' +
              '<div class="dropdown-menu">' +
              '<a class="dropdown-item" href="javascript:void(0);" data-bs-toggle="offcanvas" data-bs-target="#offcanvas_edit_item" onclick="restaurant_edit_prepare(this)"><i class="mdi mdi-pencil-outline me-1"></i> Edit</a>' +
              // '<a class="dropdown-item" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#modal_food_import" onclick="restaurant_food_import_prepare(this)"><i class="mdi mdi-file-excel me-1"></i> Import Foods</a>' +
              '<a class="dropdown-item" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#modal_delete_item" onclick="restaurant_delete_prepare(this)"><i class="mdi mdi-trash-can-outline me-1"></i> Delete</a>' +
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
            html += '<div class="cursor-pointer" onclick="restaurant_info(' + full['id'] + ')">' +
              '<span>' +
              '<button type="button" class="btn btn-sm btn-icon btn-primary acm-mr-px-10">' +
              '<span class="mdi mdi-eye"></span>' +
              '</button>' +
              '</span>' +
              '<span class="text-dark">' + full['name'] + '</span>' +
              '</div>';

            return ('<div>' + html + '</div>');
          }
        },
        {
          targets: 2,
          render: function (data, type, full, meta) {
            var html = '';

            var model_scan = '';
            if (parseInt(full['model_scan'])) {
              model_scan = '<i class="mdi mdi-check text-success acm-mr-px-5"></i>';
            }

            var model_name = '';
            if (full['model_name'] && full['model_name'] != 'null') {
              model_name = full['model_name'] + ' / ';
            }

            var model_version = '';
            if (full['model_version'] && full['model_version'] != 'null') {
              model_version = full['model_version'];
            }

            html += '<div class="cursor-pointer" onclick="restaurant_info(' + full['id'] + ')">' +
              '<span class="text-dark">' + model_scan +
              '</span>' +
              '<span class="text-dark">' + model_name +
              '</span>' +
              '<span class="text-dark">' + model_version +
              '</span>' +
              '</div>';

            return ('<div>' + html + '</div>');
          }
        },
        {
          targets: 3,
          render: function (data, type, full, meta) {
            var html = '';
            html += '<div class="cursor-pointer" onclick="restaurant_info(' + full['id'] + ')">' +
              '<span class="text-dark">' + full['count_sensors'] + ' / ' + full['count_foods'] + '</span>' +
              '</div>';

            return ('<div>' + html + '</div>');
          }
        },
        {
          targets: 4,
          className: 'd-none',
        },
        {
          targets: 5,
          className: 'd-none',
        },
      ],
      buttons: [
        @if($viewer->is_admin())
        {
          text: '<i class="mdi mdi-plus me-0 me-sm-1"></i><span class="d-none d-sm-inline-block">Add Restaurant</span>',
          className: 'add-new btn btn-primary waves-effect waves-light acm-mr-px-10',
          attr: {
            'data-bs-toggle': 'offcanvas',
            'data-bs-target': '#offcanvas_add_item',
            'onclick': 'setTimeout(function () { $("#offcanvas_add_item form input[name=name]").focus(); }, 500)',
          }
        },
        @endif
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
