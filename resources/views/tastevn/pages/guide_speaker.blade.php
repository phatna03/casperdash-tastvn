@extends('tastevn/layouts/layoutMaster')

@section('title', 'Setup Speaker')

@section('vendor-style')
  {{--  <link rel="stylesheet" href="{{asset('assets/vendor/libs/flatpickr/flatpickr.css')}}" />--}}
  {{--  <link rel="stylesheet" href="{{asset('assets/vendor/libs/select2/select2.css')}}" />--}}
@endsection

@section('vendor-script')
  {{--  <script src="{{asset('assets/vendor/libs/cleavejs/cleave.js')}}"></script>--}}
  {{--  <script src="{{asset('assets/vendor/libs/cleavejs/cleave-phone.js')}}"></script>--}}
  {{--  <script src="{{asset('assets/vendor/libs/moment/moment.js')}}"></script>--}}
  {{--  <script src="{{asset('assets/vendor/libs/flatpickr/flatpickr.js')}}"></script>--}}
  {{--  <script src="{{asset('assets/vendor/libs/select2/select2.js')}}"></script>--}}
@endsection

@section('page-script')
  {{--  <script src="{{asset('assets/js/form-layouts.js')}}"></script>--}}
@endsection

@section('content')
  <h4 class="mb-2"><span class="text-muted fw-light">Admin /</span> Setup Speaker</h4>

  <div class="row mt-2">
    <div class="col-12 mb-2">
      <div class="acm-clearfix">
        <div class="acm-float-right ml-2">
          <button type="button" class="btn btn-secondary me-1" onclick="user_test_speaker()">Test Speaker</button>
        </div>
      </div>
    </div>
    <div class="col-lg-3 col-md-4 col-12 mb-md-0 mb-3">
      <div class="d-flex justify-content-between flex-column mb-2 mb-md-0">
        <ul class="nav nav-align-left nav-pills flex-column">
          <li class="nav-item">
            <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#step_1">
              <i class="mdi mdi-list-status me-1"></i>
              <span class="align-middle">How to configure</span>
            </button>
          </li>
        </ul>
        <div class="d-none d-md-block">
          <div class="mt-5 text-center">
            <img src="{{asset('assets/img/illustrations/faq-illustration.png')}}" class="img-fluid w-px-120" alt="FAQ Image">
          </div>
        </div>
      </div>
    </div>

    <div class="col-lg-9 col-md-8 col-12">
      <div class="tab-content p-0">
        <div class="tab-pane fade show active" id="step_1" role="tabpanel">
          <div id="accordionDelivery" class="accordion">
            <div class="accordion-item active">
              <h2 class="accordion-header">
                <button class="accordion-button" type="button" data-bs-toggle="collapse" aria-expanded="true" data-bs-target="#step_1_info" aria-controls="step_1_info">
                  <b>Ensure that the user settings are turned on in the Profile Settings page.</b>
                </button>
              </h2>

              <div id="step_1_info" class="accordion-collapse collapse show">
                <div class="accordion-body">
                  <div>
                    1. Go to page URL: <a href="{{url('admin/profile/setting')}}" target="_blank">{{url('admin/profile/setting')}}</a>
                  </div>
                  <br />
                  <div>
                    2. Configure the settings as shown in the photo below.
                  </div>
                  <div class="text-center mt-2 mb-2">
                    <img src="{{url('custom/img/setup_speaker_1.png')}}" style="width: 100%;" />
                  </div>
                  <div>
                    3. Press the button as shown in the image below to check if the audio connection is complete.
                  </div>
                  <div class="text-center mt-2 mb-2">
                    <img src="{{url('custom/img/setup_speaker_2.png')}}" style="width: 50%;" />
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
