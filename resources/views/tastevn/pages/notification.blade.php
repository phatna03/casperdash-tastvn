@extends('tastevn/layouts/layoutMaster')

@section('title', 'Admin Notifications')

@section('content')
  <h4 class="mb-2"><span class="text-muted fw-light">Admin /</span> Notifications</h4>

  <div class="card">
    <div class="card-body p-1">
      <div class="list-view acm-clearfix" id="wrap-notifications">
        @if(count($pageConfigs['notifications']))
          <div class="position-relative overflow-hidden acm-mr-px-10 mb-1">
            <div class="acm-float-right">
              <a class="link-primary fw-bold" href="javascript:void(0)" onclick="notification_read_all()">Mark all as read</a>
            </div>
          </div>

          @include('tastevn.htmls.item_notification', ['notifications' => $pageConfigs['notifications']])

          @if($pageConfigs['totalPages'] > 1)
            <div class="list-pagination position-relative">
              <div class="demo-inline-spacing acm-float-right">
                <nav aria-label="Page navigation">
                  <ul class="pagination pagination-rounded pagination-outline-primary">
                    @if($pageConfigs['totalPages'] < 6)
                      @if($pageConfigs['currentPage'] > 1)
                      <li class="page-item prev">
                        <a class="page-link" href="{{url('admin/notifications?page=' . ($pageConfigs['currentPage'] - 1))}}"><i class="tf-icon mdi mdi-chevron-left"></i></a>
                      </li>
                      @endif
                      @for($i=1;$i<=$pageConfigs['totalPages'];$i++)
                      <li class="page-item @if($pageConfigs['currentPage'] == $i) active @endif">
                        <a class="page-link @if($pageConfigs['currentPage'] == $i) bg-primary text-white @endif"
                           href="{{url('admin/notifications?page=' . $i)}}">{{$i}}</a>
                      </li>
                      @endfor
                      @if($pageConfigs['currentPage'] < $pageConfigs['totalPages'])
                      <li class="page-item next">
                        <a class="page-link" href="{{url('admin/notifications?page=' . ($pageConfigs['currentPage'] + 1))}}"><i class="tf-icon mdi mdi-chevron-right"></i></a>
                      </li>
                      @endif
                    @else
                      <li class="page-item prev">
                        <a class="page-link" href="{{url('admin/notifications?page=' . ($pageConfigs['currentPage'] - 1))}}"><i class="tf-icon mdi mdi-chevron-left"></i></a>
                      </li>

                      <li class="page-item next">
                        <a class="page-link" href="{{url('admin/notifications?page=' . ($pageConfigs['currentPage'] + 1))}}"><i class="tf-icon mdi mdi-chevron-right"></i></a>
                      </li>
                    @endif
                  </ul>
                </nav>
              </div>
            </div>
          @endif

        @else
          <div class="alert alert-warning">No notification found</div>
        @endif
      </div>
    </div>
  </div>
@endsection

@section('js_end')
  <script type="text/javascript">
    $(document).ready(function() {
      @if(count($pageConfigs['vars']) && isset($pageConfigs['vars']['rid']))
        sensor_food_scan_info('{{(int)$pageConfigs['vars']['rid']}}');
      @endif
    });
  </script>
@endsection
