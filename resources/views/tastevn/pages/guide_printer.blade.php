@extends('tastevn/layouts/layoutMaster')

@section('title', 'Setup Printer')

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
  <h4 class="mb-2"><span class="text-muted fw-light">Admin /</span> Setup Printer</h4>

  <div class="row mt-2">
    <div class="col-12 mb-2">
      <div class="acm-clearfix">
        <div class="acm-float-right ml-2">
          <button type="button" class="btn btn-secondary me-1" onclick="user_test_printer()">Test Printer</button>
        </div>
      </div>
    </div>
    <div class="col-lg-3 col-md-4 col-12 mb-md-0 mb-3">
      <div class="d-flex justify-content-between flex-column mb-2 mb-md-0">
        <ul class="nav nav-align-left nav-pills flex-column">
          <li class="nav-item">
            <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#step_1">
              <i class="mdi mdi-list-status me-1"></i>
              <span class="align-middle">Step 1</span>
            </button>
          </li>
          <li class="nav-item">
            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#step_2">
              <i class="mdi mdi-list-status me-1"></i>
              <span class="align-middle">Step 2</span>
            </button>
          </li>
          <li class="nav-item">
            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#step_3">
              <i class="mdi mdi-list-status me-1"></i>
              <span class="align-middle">Step 3</span>
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
          <div id="accordionPayment" class="accordion">
            <div class="accordion-item active">
              <h2 class="accordion-header">
                <button class="accordion-button" type="button" data-bs-toggle="collapse" aria-expanded="true" data-bs-target="#step_1_info" aria-controls="step_1_info">
                  <b>Set Up the Chrome Shortcut for Use with Auto-Print</b>
                </button>
              </h2>

              <div id="step_1_info" class="accordion-collapse collapse show">
                <div class="accordion-body">
                  <div>
                    1. Now on the desktop, right-click the shortcut you plan on using with auto-print and choose
                    properties. (Note that someone with Administrative rights for the computer may be needed for
                    this step).
                  </div>
                  <br />
                  <div>
                    2. Go to the Shortcut tab.
                  </div>
                  <br />
                  <div>
                    3. Add the term --kiosk-printing to the end of the Target box.
                    <br />
                    For example, if you are not using a profile, the target box may be similar to this after:
                    <br />
                    "C:\Program Files (x86)\Google\Chrome\Application\chrome.exe" --kiosk-printing
                  </div>
                  <br />
                  <div>
                    4. Click Apply then OK to save the changes and exit.
                  </div>
                  <br />
                  <div>
                    You are now all set. The next time you print something using the adjusted shortcut, you will see a very
                    quick flash of the print dialog screen, and the job will be automatically sent to the printer chosen in the
                    steps above.
                  </div>
                  <div class="text-center mt-2 mb-2">
                    <img src="{{url('custom/img/setup_printer_1.png')}}" />
                  </div>
                  <div class="text-center">
                    <img src="{{url('custom/img/setup_printer_2.png')}}" />
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="tab-pane fade" id="step_2" role="tabpanel">
          <div id="accordionDelivery" class="accordion">
            <div class="accordion-item active">
              <h2 class="accordion-header">
                <button class="accordion-button" type="button" data-bs-toggle="collapse" aria-expanded="true" data-bs-target="#step_2_info" aria-controls="step_2_info">
                  <b>Ensure that the user settings are turned on in the Profile Settings page.</b>
                </button>
              </h2>

              <div id="step_2_info" class="accordion-collapse collapse show">
                <div class="accordion-body">
                  <div>
                    1. Go to page URL: <a href="{{url('admin/profile/setting')}}" target="_blank">{{url('admin/profile/setting')}}</a>
                  </div>
                  <br />
                  <div>
                    2. Configure the settings as shown in the photo below.
                  </div>
                  <div class="text-center mt-2 mb-2">
                    <img src="{{url('custom/img/setup_printer_3.png')}}" style="width: 100%;" />
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="tab-pane fade" id="step_3" role="tabpanel">
          <div id="accordionCancellation" class="accordion">
            <div class="accordion-item active">
              <h2 class="accordion-header">
                <button class="accordion-button" type="button" data-bs-toggle="collapse" aria-expanded="true" data-bs-target="#step_3_info" aria-controls="step_3_info">
                  <b>Remember to enable pop-ups in your web browser.</b>
                </button>
              </h2>

              <div id="step_3_info" class="accordion-collapse collapse show">
                <div class="accordion-body">
                  <div>
                    1. Configure the settings as shown in the photo below.
                  </div>
                  <div class="text-center mt-2 mb-2">
                    <img src="{{url('custom/img/setup_printer_4.png')}}" style="width: 50%;" />
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
