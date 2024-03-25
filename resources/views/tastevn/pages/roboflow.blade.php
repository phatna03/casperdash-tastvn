@extends('tastevn/layouts/layoutMaster')

@section('title', 'Admin - Roboflow')

@section('vendor-style')
{{--  <link rel="stylesheet" href="{{asset('assets/vendor/libs/spinkit/spinkit.css')}}" />--}}
@endsection

@section('vendor-script')
{{--  <script src="{{asset('assets/vendor/libs/dropzone/dropzone.js')}}"></script>--}}
@endsection

@section('page-script')
{{--  <script src="{{asset('assets/js/forms-file-upload.js')}}"></script>--}}
@endsection

@section('content')
  <h4 class="mb-2">
    <span class="text-muted fw-light">Admin /</span> Roboflow
  </h4>

  <div class="row">
    <!-- Basic  -->
    <div class="col-12">
      <div class="card mb-4">
        <h5 class="card-header">Testing</h5>
        <div class="card-body">
          <div class="row">
            <div class="col-lg-6">
              <div class="p-2 acm-border-css border-2 border-dark wrap-selected-food">
                <div class="position-relative w-100">
                  <div class="form-floating form-floating-outline mb-4 wrap-select-food">
                    <div class="form-control acm-wrap-selectize" id="select-item-food">
                      <select name="food" onchange="food_selected(this)"></select>
                    </div>
                    <label for="select-item-food">Select Dish</label>
                  </div>
                </div>

                <div class="position-relative w-100">
                  <div class="text-center w-auto">
                    <h3 class="food-name">{{$pageConfigs['item']['food_4']->name}}</h3>
                  </div>
                </div>

                <div class="row">
                  <div class="col-lg-6">
                    <div class="position-relative w-100">
                      <div class="text-center w-auto">
                        <img class="w-100 mt-2 food-photo" src="{{url('uploaded/food/food_4.jpg')}}" />
                      </div>
                    </div>
                  </div>
                  <div class="col-lg-6">
                    <div class="position-relative w-100 food-ingredients">
                      @include('tastevn.htmls.item_food_selected', ['ingredients' => $pageConfigs['item']['food_4_ingredients']])
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div class="col-lg-6" id="wrap-form-testing">
              <div class="p-2 acm-border-css border-2">
                <div class="row">
                  <div class="col-lg-6">
                    <form autocomplete="off" onsubmit="event.preventDefault(); return roboflow_detect(this);">
                      <div class="mb-2">
                        <button type="submit" class="btn btn-primary w-100">Call Roboflow API</button>
                      </div>
                      <div class="mb-2">
                        <input type="file" name="thumb" accept=".gif,.jpg,.jpeg,.png,.bmp,.webp"
                               id="img_roboflow" required class="form-control" />
                      </div>
                      <div class="position-relative w-100 mb-2 d-none" id="wrap_img">
                        <div class="text-center w-auto">
                          <img class="w-100 mt-2" src="{{url('uploaded/food/food_4.jpg')}}" />
                        </div>
                        <div class="mt-2" id="wrap_return"></div>
                      </div>
                    </form>
                  </div>

                  <div class="col-lg-6">
                    <div class="position-relative w-100 mt-2" id="wrap_results">

                    </div>
                  </div>
                </div>
              </div>

            </div>

          </div>
        </div>
      </div>
    </div>

  </div>
@endsection

@section('js_end')
  <script type="text/javascript">
    $(document).ready(function() {

      var formTesting = $('#wrap-form-testing');
      formTesting.find('input[type=file]').change(function () {
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
              formTesting.find('#wrap_img').removeClass('d-none');
              formTesting.find('#wrap_img img').attr('src', e.target.result);
            }
            reader.readAsDataURL(input.files[i]);
          }
        }
      });

      var selectize_food = $('.wrap-select-food select');
      selectize_food.selectize({
        valueField: 'id',
        labelField: 'name',
        searchField: 'name',
        preload: true,
        clearCache: function (template) {},
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
    });

    function food_selected(ele) {
      var wrap = $(ele).closest('.wrap-selected-food');
      var chosen = $(ele).val();
      if (!chosen || !parseInt(chosen)) {
        return false;
      }

      axios.post('/admin/food/get', {
        item: chosen,
      })
        .then(response => {

          wrap.find('.food-name').empty().text(response.data.item.name);
          wrap.find('.food-photo').attr('src', response.data.item_photo);
          wrap.find('.food-ingredients').empty().append(response.data.html_selected);

        })
        .catch(error => {
          if (error.response.data && Object.values(error.response.data).length) {
            Object.values(error.response.data).forEach(function (v, k) {
              message_from_toast('error', 'Invalid Credentials', v);
            });
          }
        });

      return false;
    }

    function roboflow_detect(ele) {
      var form = $(ele).closest('form');
      var formData = new FormData();
      formData.append('_token', acmcfs.var_csrf);
      // formData.append('dataset', form.find('input[name=dataset]').val());

      // Read selected files
      if (form.find('#img_roboflow')[0].files.length) {
        formData.append('image[]', form.find('#img_roboflow')[0].files[0]);
      } else {
        alert('please choose image to test...');
        return false;
      }

      $('#wrap_results').empty()
        .append('<div class="m-auto">' + acmcfs.html_loading + '</div>');

      $.ajax({
        url: acmcfs.link_base_url + '/admin/roboflow/detect',
        type: 'post',
        dataType: "json",
        data: formData,
        contentType: false, // NEEDED, DON'T OMIT THIS (requires jQuery 1.6+)
        processData: false, // NEEDED, DON'T OMIT THIS
        complete: function (xhr, textStatus) {

          var html = '';
          var htmlReturn = '';

          if (xhr.responseJSON.status) {

            //rbf
            html += '<div class="text-dark fw-bold mb-1 mt-1">+ Roboflow found ingredients</div>';
            if (xhr.responseJSON.data.rbf.ingredients_found && xhr.responseJSON.data.rbf.ingredients_found.length) {
              xhr.responseJSON.data.rbf.ingredients_found.forEach(function (v, k) {
                html += '<div>'
                  + '- <b class="text-dark fw-bold">' + v.quantity + '</b> '
                  + v.title
                  + '</div>';
              });
            } else {
              html += '<div>---</div>';
            }

            html += '<div class="text-dark fw-bold mb-1 mt-1">+ Roboflow predict core dish</div>';
            if (xhr.responseJSON.data.rbf.food_id) {
              html += '<div>'
                + '- <b class="text-danger fw-bold">' + xhr.responseJSON.data.rbf.food_confidence + '%</b> - '
                + xhr.responseJSON.data.rbf.food_name
                + '</div>';
            } else {
              html += '<div>---</div>';
            }

            html += '<div class="text-dark fw-bold mb-1 mt-1">+ Ingredients Missing</div>';
            if (xhr.responseJSON.data.rbf.ingredients_missing && xhr.responseJSON.data.rbf.ingredients_missing.length) {
              xhr.responseJSON.data.rbf.ingredients_missing.forEach(function (v, k) {
                html += '<div>'
                  + '- <b class="text-dark fw-bold">' + v.quantity + '</b> '
                  + v.name + ' (' + v.name_vi + ')'
                  + '</div>';
              });
            } else {
              html += '<div>---</div>';
            }

            //sys
            html += '<div class="text-primary fw-bold mb-1 mt-1">+ System predict core dishes</div>';
            if (xhr.responseJSON.data.sys.foods_predict && xhr.responseJSON.data.sys.foods_predict.length) {
              xhr.responseJSON.data.sys.foods_predict.forEach(function (v, k) {
                html += '<div>'
                  + '- <b class="text-danger fw-bold">' + v.confidence + '%</b> - '
                  + v.food_name
                  + '</div>';
              });
            } else {
              html += '<div>---</div>';
            }

            html += '<div class="text-primary fw-bold mb-1 mt-1">+ System predict dish</div>';
            if (xhr.responseJSON.data.sys.food_id) {
              html += '<div>'
                + '- <b class="text-danger fw-bold">' + xhr.responseJSON.data.sys.food_confidence + '%</b> - '
                + xhr.responseJSON.data.sys.food_name
                + '</div>';
            } else {
              html += '<div>---</div>';
            }

            html += '<div class="text-primary fw-bold mb-1 mt-1">+ Ingredients Missing</div>';
            if (xhr.responseJSON.data.sys.ingredients_missing && xhr.responseJSON.data.sys.ingredients_missing.length) {
              xhr.responseJSON.data.sys.ingredients_missing.forEach(function (v, k) {
                html += '<div>'
                  + '- <b class="text-dark fw-bold">' + v.quantity + '</b> '
                  + v.name + ' (' + v.name_vi + ')'
                  + '</div>';
              });
            } else {
              html += '<div>---</div>';
            }

            htmlReturn += '<div class="text-primary fw-bold mb-1 mt-1">+ Roboflow API return</div>';
            if (xhr.responseJSON.data.food.predictions && xhr.responseJSON.data.food.predictions.length) {
              xhr.responseJSON.data.food.predictions.forEach(function (v, k) {
                htmlReturn += '<div>- <b class="text-danger fw-bold">' + parseInt(v.confidence * 100) + '%</b> - ' + v.class + '</div>';
                htmlReturn += '<div><span class="acm-mr-px-10">[x = ' + v.x + ']</span>';
                htmlReturn += '<span>[y = ' + v.y + '</span>]</div>';
                htmlReturn += '<div><span class="acm-mr-px-10">[width = ' + v.width + ']</span>';
                htmlReturn += '<span>[height = ' + v.height + ']</span></div>';
              });
            } else {
              htmlReturn += '<div>---</div>';
            }

            $('#wrap_return').empty().append(htmlReturn);
            $('#wrap_results').empty().append(html);
            sound_play();

          } else {

            // console.log(xhr.responseJSON);

            $('#wrap_results').empty()
              .append('<div class="alert alert-danger">Dish not found</div>');

            message_from_toast('error', acmcfs.message_title_error, xhr.responseJSON.error, true);
          }
        },
      });

      return false;
    }
  </script>
@endsection
