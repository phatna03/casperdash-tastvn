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
  <h4 class="mb-2"><span class="text-muted fw-light">Admin /</span> Dishes</h4>

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
        <div class="form-floating form-floating-outline mb-4">
          <input type="text" class="form-control" id="add-item-name" name="name" />
          <label for="add-item-name">Name <b class="text-danger">*</b></label>
        </div>
        <div class="form-floating form-floating-outline mb-4" id="add-item-live-group">
          <div class="form-control acm-wrap-selectize">
            <select name="live_group" class="opt_selectize"
            >
              @for($i=1;$i<=3;$i++)
                <option value="{{$i}}">Group {{$i}}</option>
              @endfor
            </select>
          </div>
          <label for="add-item-live-group">Confidence</label>
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
      <h5 id="offcanvas_edit_item_label" class="offcanvas-title">Edit Dish Name</h5>
      <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body mx-0 flex-grow-0 h-100">
      <form class="pt-0" onsubmit="return food_edit(event, this);">
        <div class="form-floating form-floating-outline mb-4">
          <input type="text" class="form-control" id="edit-item-name" name="name" />
          <label for="edit-item-name">Name <b class="text-danger">*</b></label>
        </div>
        <div class="form-floating form-floating-outline mb-4" id="edit-item-live-group">
          <div class="form-control acm-wrap-selectize">
            <select name="live_group" class="opt_selectize"
            >
              @for($i=1;$i<=3;$i++)
              <option value="{{$i}}">Group {{$i}}</option>
              @endfor
            </select>
          </div>
          <label for="edit-item-live-group">Confidence</label>
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
  <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvas_edit_recipe" aria-labelledby="offcanvas_edit_recipe_label">
    <div class="offcanvas-header">
      <h5 id="offcanvas_edit_recipe_label" class="offcanvas-title">Edit Dish Recipe</h5>
      <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body mx-0 flex-grow-0 h-100">
      <form class="pt-0" onsubmit="return food_edit_recipe(event, this);">
        <div class="form-floating form-floating-outline mb-4">
          <div class="form-control acm-wrap-selectize" id="edit-item-restaurant-recipe">
            <select class="ajx_selectize" required
                    data-value="restaurant_parent"
                    name="restaurant_parent_id"
                    onchange="food_edit_select_recipe(this)"
            >
              <option value="">Please choose valid restaurant</option>
            </select>
          </div>
          <label for="edit-item-restaurant-recipe" class="text-danger">Restaurant</label>
        </div>
        <div class="form-floating form-floating-outline mb-4 wrap-edit-ingredients">
          <div class="form-control acm-height-px-auto p-1" id="edit-item-food-recipe-custom">
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
          <label for="edit-item-food-recipe-custom" class="text-danger">Recipe Ingredients</label>
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
  <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvas_edit_ingredient" aria-labelledby="offcanvas_edit_ingredient_label">
    <div class="offcanvas-header">
      <h5 id="offcanvas_edit_ingredient_label" class="offcanvas-title">Edit Dish Roboflow</h5>
      <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body mx-0 flex-grow-0 h-100">
      <form class="pt-0" onsubmit="return food_edit_ingredient(event, this);">
        <div class="form-floating form-floating-outline mb-4">
          <div class="form-control acm-wrap-selectize" id="edit-item-restaurant-ingredient">
            <select class="ajx_selectize" required
                    data-value="restaurant_parent"
                    name="restaurant_parent_id"
                    onchange="food_edit_select_ingredient(this)"
            >
              <option value="">Please choose valid restaurant</option>
            </select>
          </div>
          <label for="edit-item-restaurant-ingredient" class="text-danger">Restaurant</label>
        </div>
        <div class="form-floating form-floating-outline mb-4 wrap-edit-ingredients">
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
          <label for="edit-item-food-ingredient-custom" class="text-danger">Roboflow Ingredients</label>
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
  <!-- modal to import item -->
  <div class="modal animate__animated animate__rollIn" id="modal_import" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content">
        <form onsubmit="return food_import(event, this);">
          <div class="modal-header">
            <h4 class="modal-title">Import Excel</h4>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="row">
              <a class="text-primary fw-bold" href="{{url('import_food.xlsx')}}" download="" style="margin-bottom: 10px;">Download excel template file</a>

              <div class="col-12 mb-3">
                <select name="restaurant_parent_id" class="ajx_selectize" required
                        data-value="restaurant_parent"
                >
                  <option value="">Please choose valid restaurant</option>
                </select>
              </div>
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
          </div>
        </form>
      </div>
    </div>
  </div>
  <div class="modal animate__animated animate__rollIn" id="modal_import_recipe" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content">
        <form onsubmit="return food_import_recipe(event, this);">
          <div class="modal-header">
            <h4 class="modal-title">Import Recipes</h4>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="row">
              <a class="text-primary fw-bold" href="{{url('import_food.xlsx')}}" download="" style="margin-bottom: 10px;">Download excel template file</a>

              <div class="col-12 mb-3">
                <select name="restaurant_parent_id" class="ajx_selectize" required
                        data-value="restaurant_parent"
                >
                  <option value="">Please choose valid restaurant</option>
                </select>
              </div>
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
      "ajax": "{{ url('datatable/foods') }}",
      "createdRow": function( row, data, dataIndex ) {
        $(row).attr('data-id', data.id);
        $(row).attr('data-name', data.name);
        $(row).attr('data-photo', data.photo);
        $(row).attr('data-live_group', data.live_group);
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
            return (
              '<div class="cursor-pointer" onclick="food_info(' + full['id'] + ')">' +
              '<span>' + full['name'] + '</span>' +
              '</div>'
            );
          }
        },
        {
          targets: 3,
          render: $.fn.dataTable.render.moment('YYYY-MM-DDTHH:mm:ss.SSSSZ', 'DD/MM/YY HH:mm:ss' )
        },
        {
          // Actions
          targets: 4,
          title: '',
          searchable: false,
          orderable: false,
          render: function (data, type, full, meta) {
            @if($viewer->is_admin())
            return (
              '<div class="dropdown">' +
              '<button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown"><i class="mdi mdi-dots-vertical"></i></button>' +
              '<div class="dropdown-menu">' +
              '<a class="dropdown-item" href="javascript:void(0);" data-bs-toggle="offcanvas" data-bs-target="#offcanvas_edit_item" onclick="food_edit_prepare(this)"><i class="mdi mdi-pencil-outline me-1"></i> Edit Dish Name</a>' +
              '<a class="dropdown-item" href="javascript:void(0);" data-bs-toggle="offcanvas" data-bs-target="#offcanvas_edit_ingredient" onclick="food_edit_prepare_ingredient(this)"><i class="mdi mdi-robot me-1"></i> Edit Dish Roboflow</a>' +
              '<a class="dropdown-item" href="javascript:void(0);" data-bs-toggle="offcanvas" data-bs-target="#offcanvas_edit_recipe" onclick="food_edit_prepare_recipe(this)"><i class="mdi mdi-hoop-house me-1"></i> Edit Dish Recipe</a>' +
              '</div>' +
              '</div>'
            );
            @else
            return ('<div></div>');
            @endif
          }
        }
      ],
      buttons: [
        @if($viewer->is_admin())
        {
          text: '<i class="mdi mdi-plus me-0 me-sm-1"></i><span class="d-none d-sm-inline-block">Ingredients</span>',
          className: 'add-new btn btn-info waves-effect waves-light',
          attr: {
            'data-bs-toggle': 'modal',
            'data-bs-target': '#modal_import',
          }
        },
        // {
        //   text: '<i class="mdi mdi-plus me-0 me-sm-1"></i><span class="d-none d-sm-inline-block">Recipes</span>',
        //   className: 'add-new btn btn-warning waves-effect waves-light',
        //   attr: {
        //     'data-bs-toggle': 'modal',
        //     'data-bs-target': '#modal_import_recipe',
        //   }
        // },
        {
          text: '<i class="mdi mdi-plus me-0 me-sm-1"></i><span class="d-none d-sm-inline-block">Add Dish</span>',
          className: 'add-new btn btn-primary waves-effect waves-light',
          attr: {
            'data-bs-toggle': 'offcanvas',
            'data-bs-target': '#offcanvas_add_item',
            'onclick': 'food_add_clear()',
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
