@extends('tastevn/layouts/layoutMaster')

@section('title', 'Admin - Roboflow')

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

                <div class="row">
                  <div class="col-lg-6">
                    <div class="position-relative w-100 mt-2">
                      <div class="form-floating form-floating-outline mb-4">
                        <div class="form-control acm-wrap-selectize" id="select-item-restaurant">
                          <select name="restaurant_parent_id" class="ajx_selectize" required
                                  data-value="restaurant_parent" onchange="restaurant_selected(this)"
                                  data-placeholder="Please choose restaurant..."
                          ></select>
                        </div>
                        <label for="select-item-restaurant" class="text-danger">Restaurant</label>
                      </div>
                    </div>

                    <div class="position-relative w-100 mt-2">
                      <div class="form-floating form-floating-outline mb-4">
                        <div class="form-control acm-wrap-selectize" id="select-item-food">
                          <select name="food" class="opt_selectize" onchange="food_selected(this)"
                                  data-placeholder="Please choose dish..."
                          ></select>
                        </div>
                        <label for="select-item-food" class="text-danger">Dish</label>
                      </div>
                    </div>
                  </div>

                  <div class="col-lg-6">
                    <div class="position-relative w-100">
                      <div class="text-center w-auto d-none">
                        <h3 class="food-name"></h3>
                      </div>

                      <div class="text-center w-auto">
                        <img class="w-100 mt-2 food-photo" src="{{url('custom/img/no_photo.png')}}" />
                      </div>
                    </div>
                  </div>

                  <div class="col-lg-12 mt-3">
                    <div class="position-relative w-100 wrap-ingredients"></div>
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
                      <div class="position-relative w-100 mb-2" id="wrap_img">
                        <div class="text-center w-auto">
                          <img class="w-100 mt-2" src="{{url('custom/img/no_photo.png')}}" />
                        </div>
                      </div>
                    </form>
                  </div>

                  <div class="col-lg-6">
                    <div class="position-relative w-100 mt-2" id="wrap_results"></div>
                  </div>
                </div>
              </div>

              <div class="row">
                <div class="@if($pageConfigs['debug']) col-lg-6 @else col-lg-12 @endif">
                  <div class="mt-2" id="wrap_return"></div>
                </div>

                @if($pageConfigs['debug'])
                <div class="col-lg-6">
                  <div class="mt-2 d-none" id="wrap_return_js"></div>
                </div>
                @endif
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

  </div>
@endsection

@section('js_end')

  @if($pageConfigs['debug'])
    <script src="{{url('custom/library/roboflow/roboflow.js')}}"></script>
  @endif

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

    });

    function food_selected(ele) {
      var wrap = $(ele).closest('.wrap-selected-food');
      var chosen = $(ele).val();
      if (!chosen || !parseInt(chosen)) {
        return false;
      }

      axios.post('/admin/roboflow/food/get/info', {
        restaurant_parent_id: wrap.find('select[name=restaurant_parent_id]').val(),
        item: chosen,
      })
        .then(response => {

          wrap.find('.food-name').empty().text(response.data.food_name);
          wrap.find('.food-photo').attr('src', response.data.food_photo);
          wrap.find('.wrap-ingredients').empty().append(response.data.html_info);

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

    function restaurant_selected(ele) {
      var wrap = $(ele).closest('.wrap-selected-food');

      var chosen = $(ele).val();
      if (!chosen || !parseInt(chosen)) {
        wrap.find('select[name=food]').selectize()[0].selectize.destroy();
        wrap.find('select[name=food]').selectize({});
        return false;
      }

      axios.post('/admin/roboflow/restaurant/food/get', {
        item: chosen,
      })
        .then(response => {

          wrap.find('select[name=food]').selectize()[0].selectize.destroy();
          wrap.find('select[name=food]').selectize({
            maxItems: 1,
            valueField: 'id',
            labelField: 'name',
            searchField: 'name',
            options: response.data.items,
            create: false,
          });

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

          $('#wrap_return_js').addClass('d-none');

          @if($pageConfigs['debug'])
          var photo_img = new Image();
          photo_img.crossOrigin = "anonymous";
          photo_img.src = acmcfs.link_base_url + '/roboflow/test/roboflow_detect.jpg';

          setTimeout(function () {
            acmcfs.rbf_model.detect(photo_img).then(function (predictions) {
              console.log("Predictions: ", predictions);

              var htmlJS = '';

              htmlJS = '<div class="text-primary fw-bold mb-1 mt-1">+ Roboflow JS</div>';
              if (predictions.length) {
                predictions.forEach(function (v, k) {
                  htmlJS += '<div>- <b class="text-danger fw-bold">' + parseInt(v.confidence * 100) + '%</b> - ' + v.class + '</div>';
                  // htmlJS += '<div><span class="acm-mr-px-10">[x = ' + v.x + ']</span>';
                  // htmlJS += '<span>[y = ' + v.y + '</span>]</div>';
                  // htmlJS += '<div><span class="acm-mr-px-10">[width = ' + v.width + ']</span>';
                  // htmlJS += '<span>[height = ' + v.height + ']</span></div>'
                  ;
                });
              } else {
                htmlJS += '<div>---</div>';
              }

              $('#wrap_return_js').removeClass('d-none');
              $('#wrap_return_js').empty().append(htmlJS);
            });
          }, 888);
          @endif

          if (xhr.responseJSON.status) {

            //foods
            html += '<div class="text-primary fw-bold mb-1 mt-1">+ Roboflow found dishes</div>';
            if (xhr.responseJSON.data.rbf.foods_found && xhr.responseJSON.data.rbf.foods_found.length) {
              xhr.responseJSON.data.rbf.foods_found.forEach(function (v, k) {
                html += '<div>'
                  + '- <b class="text-dark fw-bold">' + v.confidence + '</b>% '
                  + v.title
                  + '</div>';
              });
            } else {
              html += '<div>---</div>';
            }

            //ingredients
            html += '<div class="text-primary fw-bold mb-1 mt-1">+ Roboflow found ingredients</div>';
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

            htmlReturn += '<div class="text-primary fw-bold mb-1 mt-1">+ Roboflow API</div>';
            if (xhr.responseJSON.data.food.predictions && xhr.responseJSON.data.food.predictions.length) {
              xhr.responseJSON.data.food.predictions.forEach(function (v, k) {
                htmlReturn += '<div>- <b class="text-danger fw-bold">' + parseInt(v.confidence * 100) + '%</b> - ' + v.class + '</div>';
                // htmlReturn += '<div><span class="acm-mr-px-10">[x = ' + v.x + ']</span>';
                // htmlReturn += '<span>[y = ' + v.y + '</span>]</div>';
                // htmlReturn += '<div><span class="acm-mr-px-10">[width = ' + v.width + ']</span>';
                // htmlReturn += '<span>[height = ' + v.height + ']</span></div>'
                ;
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

    @if($pageConfigs['debug'])
    //roboflow init
    roboflow.auth({
      publishable_key: "rf_3DtUFXV7oiSXMh2VkXK8d0EHcRD2"
    });
    async function rbf_load_model() {
      var model = await roboflow.load({
        model: "missing-dish-ingredients",
        version: 29
      });

      model.configure({
        threshold: 0.3,
        overlap: 0.6,
        max_objects: 100
      });

      acmcfs.rbf_model = model;

      return model;
    }
    //roboflow check ready
    rbf_load_model().then(model => {
      console.log("==============================================");
      console.log("RBF load success......");

      // Do something with the model
      console.log(model.getMetadata());
      console.log(model.getConfiguration());
      console.log('ok...');

      sys_ready = 1;

    }).catch(error => {
      console.log("==============================================");
      console.error('Error loading model:', error);
    });
    @endif
  </script>
@endsection
