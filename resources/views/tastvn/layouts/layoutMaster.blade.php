@isset($pageConfigs)
{!! Helper::updatePageConfig($pageConfigs) !!}
@endisset
@php
$configData = Helper::appClasses();
@endphp

@isset($configData["layout"])
@include((( $configData["layout"] === 'horizontal') ? 'tastvn.layouts.horizontalLayout' :
(( $configData["layout"] === 'blank') ? 'tastvn.layouts.blankLayout' :
(($configData["layout"] === 'front') ? 'tastvn.layouts.layoutFront' : 'tastvn.layouts.contentNavbarLayout') )))
@endisset

{{--tastvn--}}
<link rel="stylesheet" href="{{asset('assets/vendor/libs/toastr/toastr.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/animate-css/animate.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/animate-css/animate.css')}}">
<link rel="stylesheet" href="{{url('tastvn/css/app.css')}}" />

<script src="{{asset('assets/vendor/libs/toastr/toastr.js')}}"></script>

@yield('js_end')

<div class="modal animate__animated animate__rollIn" id="deleteModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title" id="exampleModalLabel5">Delete Confirmation?</h4>
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
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
        <button type="button" onclick="submit_ok()" class="btn btn-primary" data-bs-dismiss="modal">Confirm</button>
      </div>
    </div>
  </div>
</div>

<div class="modal animate__animated animate__rollIn" id="compareModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-simple modal-pricing">
    <div class="modal-content p-2 p-md-5">
      <div class="modal-body py-3 py-md-0">
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        <div class="pb-3 rounded-top">
          <div class="row mx-0 gy-3">
            <div class="col-lg-8 mb-4">
              <div class="form-floating form-floating-outline">
                <textarea class="form-control h-px-100" id="add-user-noted" name="noted" ></textarea>
                <label for="add-user-noted">Note</label>
              </div>
            </div>
            <div class="col-lg-4 mb-4">
              <div class="form-floating form-floating-outline">
                <div class="d-flex align-items-center justify-content-center flex-wrap gap-2 py-3 mb-0 mb-md-3">
                  <label class="switch switch-primary ms-sm-5 ps-sm-5 me-0">
                    <span class="switch-label">Failed?</span>
                    <input type="checkbox" class="switch-input price-duration-toggler" />
                    <span class="switch-toggle-slider">
                      <span class="switch-on"></span>
                      <span class="switch-off"></span>
                    </span>
                    <span class="switch-label">Approved?</span>
                  </label>
                </div>
              </div>
            </div>
            <div class="col-lg-6 mb-4">
              <div class="w-auto">
                <h5 class="text-center">Image Upload</h5>
                <img class="w-100" src="{{url('tastvn/img/pizza_failed.jpg')}}" />
              </div>
            </div>
            <div class="col-lg-6 mb-4">
              <div class="w-auto">
                <h5 class="text-center">Image Standard</h5>
                <img class="w-100" src="{{url('tastvn/img/pizza_standard.jpg')}}" />
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script type="text/javascript">
  function submit_ok () {

    toastr.options = {
      autoDismiss: true,
      newestOnTop: true,
      positionClass: 'toast-bottom-right',
      onclick: null,
      rtl: isRtl
    };

    toastr['success']('Your changes have been updated successfully!', 'Success');
  }
</script>
