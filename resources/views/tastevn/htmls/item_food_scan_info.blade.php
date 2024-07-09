@php

@endphp

<div class="row">
  <div class="col-lg-6 mb-1">
    <div class="acm-border-css p-1">
      <div class="text-center w-auto p-1">
        <div class="text-uppercase">
          <span class="badge bg-secondary">photo standard</span>
        </div>
        <img class="w-100" loading="lazy"
             src="{{$restaurant->get_photo_standard($food)}}"/>
      </div>
    </div>
  </div>
  <div class="col-lg-6 mb-1 position-relative">
    <div class="acm-border-css p-1 sensor-wrapper">
      <div class="text-center w-auto p-1">
        <div class="clearfix position-relative">
          <div class="text-uppercase">
            <span class="badge bg-secondary">photo sensor</span>
          </div>
          @if(count($comments))
            <span class="badge bg-danger cmt-count">{{count($comments) . ' notes'}}</span>
          @endif
        </div>
        <img class="w-100" loading="lazy" src="{{$rfs->get_photo()}}"/>
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
    </div>
  </div>

  <div class="col-lg-4 mb-1 wrap_rbf">
    <div class="acm-border-css p-1 @if($rfs->found_by == 'rbf') bg-success-subtle @endif">
      <div class="row">
        <div class="col-12 mb-1 text-center">
          <div class="text-uppercase">
            <span class="badge bg-secondary">roboflow</span>
          </div>
        </div>

        <div class="col-12 mb-1">
          <div class="acm-lbl-dark text-primary">+ Predicted dish:</div>
          <div class="acm-text-line-one">
            @if($food_rbf)
              - <b class="acm-mr-px-5 text-danger">{{$food_rbf_confidence}}
                %</b> <span class="text-dark">{{$food_rbf->name}}</span>
            @else
              ---
            @endif
          </div>
        </div>

        @if($rfs->found_by == 'rbf' && count($ingredients_missing))
          <div class="col-12 mb-1">
            <div class="acm-lbl-dark text-primary">+ Ingredients Missing:</div>
            <div>
              @foreach($ingredients_missing as $ing)
                <div class="acm-text-line-one">
                  - <b class="acm-mr-px-5 text-danger">{{$ing['ingredient_quantity']}}</b>
                  <span class="text-dark">
                    @if(!empty($ing['name_vi']))
                      {{$ing['name'] . ' - ' . $ing['name_vi']}}
                    @else
                      {{$ing['name']}}
                    @endif
                  </span>
                </div>
              @endforeach
            </div>
          </div>
        @endif

        <div class="col-12 mb-1">
          <div class="acm-lbl-dark text-primary">+ Ingredients found:</div>
          <div>
            @if(count($ingredients_found))
              @foreach($ingredients_found as $ing)
                <div class="acm-text-line-one">
                  - <b class="acm-mr-px-5 text-danger">{{$ing['ingredient_quantity']}}</b>
                  <span class="text-dark">
                    @if(!empty($ing['name_vi']))
                      {{$ing['name'] . ' - ' . $ing['name_vi']}}
                    @else
                      {{$ing['name']}}
                    @endif
                  </span>
                </div>
              @endforeach
            @else
              ---
            @endif
          </div>
        </div>

      </div>
    </div>

    @if(count($data['rbf']['predictions']))
      <ul class="cmt-wrapper">
        @if(count($data['rbf']['versions']))
          @if($data['rbf']['model'] > 0)
            @php
              foreach($data['rbf']['versions'] as $version):
              $version = (array)$version;
            @endphp
            <li class="cmt-itm">
              <div class="d-flex overflow-hidden">
                <span>Dataset: {{$version['dataset'] . '/' . $version['version']}}</span>
              </div>
            </li>
            @endforeach
          @else
            <li class="cmt-itm">
              <div class="d-flex overflow-hidden">
                <span>Dataset: {{$data['rbf']['versions']['dataset'] . '/' . $data['rbf']['versions']['version']}}</span>
              </div>
            </li>
          @endif
        @endif
        @php
          $count = 0;
          foreach($data['rbf']['predictions'] as $prediction):
          $count++;
          $confidence = round($prediction['confidence'] * 100);
        @endphp
        <li class="cmt-itm">
          <div class="d-flex overflow-hidden">
            <span class="fw-bold acm-mr-px-5">{{$confidence . '%'}}</span>
            <span>{{$prediction['class']}}</span>
          </div>
        </li>
        @endforeach
      </ul>
    @endif
  </div>
  <div class="col-lg-4 mb-1">
    <div class="acm-border-css p-1 @if($item['found_by'] == 'sys') bg-success-subtle @endif">
      <div class="row">
        <div class="col-12 mb-1 text-center">
          <div class="text-uppercase">
            <span class="badge bg-secondary">system</span>
          </div>
        </div>
        <div class="col-12 mb-1">
          <div class="acm-lbl-dark text-primary">+ Predicted dish:</div>
          <div class="acm-text-line-one">
            @if((int)$data['sys']['food_id'])
              - <b class="acm-mr-px-5 text-danger">{{$data['sys']['food_confidence']}}
                %</b> <span class="text-dark">{{$data['sys']['food_name']}}</span>
            @else
              ---
            @endif
          </div>
        </div>
        @if((int)$data['sys']['food_id'])
          <div class="col-12 mb-1">
            <div class="acm-lbl-dark text-primary">+ Ingredients Missing:</div>
            <div>
              @if(count($data['sys']['ingredients_missing']))
                @foreach($data['sys']['ingredients_missing'] as $ing)
                  <div class="acm-text-line-one">
                    - <b class="acm-mr-px-5 text-danger">{{$ing['quantity']}}</b>
                    <span class="text-dark">
                        @if(!empty($ing['name_vi']))
                        {{$ing['name'] . ' - ' . $ing['name_vi']}}
                      @else
                        {{$ing['name']}}
                      @endif
                        </span>
                  </div>
                @endforeach
              @else
                ---
              @endif
            </div>
          </div>
          <div class="col-12 mb-1">
            <div class="acm-lbl-dark text-primary">+ List of predicted dishes:</div>
            <div>
              @if(count($data['sys']['foods']))
                @php
                  foreach($data['sys']['foods'] as $foo):
                  $food = App\Models\Food::find($foo['food']);
                @endphp
                <div class="acm-text-line-one">
                  - <b class="acm-mr-px-5 text-danger">{{$foo['confidence']}}%</b> <span class="text-dark">{{$food->name}}</span>
                </div>
                @endforeach
              @else
                ---
              @endif
            </div>
          </div>
        @endif
      </div>
    </div>
  </div>
  <div class="col-lg-4 mb-1">
    <div class="acm-border-css p-1 @if($item['found_by'] == 'usr') bg-success-subtle @endif">
      <form onsubmit="return event.preventDefault();">
        <div class="row">
          <div class="col-12 mb-1 text-center position-relative clearfix overflow-hidden acm-height-30-min">
            <div class="position-absolute acm-top-0">
              <button type="button" class="btn btn-sm btn-danger p-1" onclick="sensor_food_scan_update_prepare()">
                Update Result
              </button>
            </div>
            <div class="position-absolute acm-top-0 acm-right-15px text-uppercase">
              <span class="badge bg-secondary">final status</span>
            </div>
          </div>
          <div class="col-12 mb-2 mt-2">
            <div class="form-floating form-floating-outline" id="final-status-wrapper">
              <div class="form-control acm-height-px-auto p-1">
                <div class="form-group clearfix p-2">
                  <div class="acm-float-right">
                    @if($item->get_food())
                      <span class="badge bg-primary acm-fs-14">{{$item->get_food()->name}}</span>
                    @endif
                  </div>
                  <div class="overflow-hidden">
                    <label class="acm-lbl-dark text-primary">+ Dish:</label>
                  </div>
                </div>

                <div class="form-group clearfix p-2">
                  <div class="overflow-hidden">
                    <label class="acm-lbl-dark text-primary">+ Ingredients Missing:</label>
                  </div>
                  <div class="overflow-hidden">
                    @if($item['found_by'] == 'rbf')
                      @if(count($data['rbf']['ingredients_missing']))
                        @foreach($data['rbf']['ingredients_missing'] as $ing)
                          <div class="acm-text-line-one">
                            - <b class="acm-mr-px-5 text-danger">{{$ing['quantity']}}</b>
                            <span class="text-dark">
                              @if(!empty($ing['name_vi']))
                                {{$ing['name'] . ' - ' . $ing['name_vi']}}
                              @else
                                {{$ing['name']}}
                              @endif
                              </span>
                          </div>
                        @endforeach
                      @endif
                    @elseif($item['found_by'] == 'sys')
                      @if(count($data['sys']['ingredients_missing']))
                        @foreach($data['sys']['ingredients_missing'] as $ing)
                          <div class="acm-text-line-one">
                            - <b class="acm-mr-px-5 text-danger">{{$ing['quantity']}}</b>
                            <span class="text-dark">
                              @if(!empty($ing['name_vi']))
                                {{$ing['name'] . ' - ' . $ing['name_vi']}}
                              @else
                                {{$ing['name']}}
                              @endif
                              </span>
                          </div>
                        @endforeach
                      @endif
                    @elseif($item['found_by'] == 'usr')
                      @if(count($data['usr']['ingredients_missing']))
                        @foreach($data['usr']['ingredients_missing'] as $ing)
                          <div class="acm-text-line-one">
                            - <b class="acm-mr-px-5 text-danger">{{$ing['ingredient_quantity']}}</b>
                            <span class="text-dark">
                              @if(!empty($ing['name_vi']))
                                {{$ing['name'] . ' - ' . $ing['name_vi']}}
                              @else
                                {{$ing['name']}}
                              @endif
                              </span>
                          </div>
                        @endforeach
                      @endif
                    @endif
                  </div>
                </div>

                <div class="form-group clearfix p-2">
                  <div class="overflow-hidden">
                    <label class="acm-lbl-dark text-primary">+ Note:</label>
                  </div>
                  <div class="overflow-hidden">
                    @if(count($texts))
                      @foreach($texts as $text)
                        <div>- {{$text->name}}</div>
                      @endforeach
                    @endif
                    <div>- <?php echo nl2br($item['note'])?></div>
                  </div>
                </div>
              </div>
              <label for="final-status-wrapper" class="text-danger fw-bold"></label>
            </div>
          </div>
        </div>
      </form>
    </div>
  </div>
</div>
