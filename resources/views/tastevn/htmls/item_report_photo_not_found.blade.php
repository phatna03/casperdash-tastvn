<div class="text-center w-auto p-1">
  <div class="clearfix position-relative">
    <div class="text-uppercase acm-fs-18 fw-bold">
      <span class="text-dark">photo sensor id:  <b class="text-danger">{{$rfs->id}}</b></span>
    </div>
    @if(count($comments))
      <span class="badge bg-danger cmt-count">{{count($comments) . ' notes'}}</span>
    @endif
  </div>
  <img class="acm-width-max-100 h-auto acm-border-css" loading="lazy" src="{{$rfs->get_photo()}}"/>
</div>

@if(count($comments))
  <ul class="cmt-wrapper">
    @php
      $count = 0;
      foreach($comments as $comment):
      $count++;
    @endphp
    <li class="cmt-itm @if($count%2) cmt-itm-odd @else cmt-itm-even @endif">
      <div class="d-flex overflow-hidden">
        <div class="chat-message-wrapper flex-grow-1">
          <div class="chat-message-owner position-relative clearfix">
            <div class="acm-float-right">
              <span class="badge bg-secondary">{{date('d/m/Y H:i:s', strtotime($comment->created_at))}}</span>
            </div>
            <div class="overflow-hidden">
              <span class="badge bg-primary">{{$comment->owner->name}}</span>
            </div>
          </div>
          <div class="chat-message-text text-dark">
              <?php echo nl2br($comment->content) ?>
          </div>
        </div>
      </div>
    </li>
    @endforeach
  </ul>
@endif
