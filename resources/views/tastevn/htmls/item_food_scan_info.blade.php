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
            @if($food_rbf && $confidence_group < 3)
              - <b class="acm-mr-px-5 text-danger">{{$food_rbf_confidence}}
                %</b> <span class="text-dark">{{$food_rbf->name}}</span>
            @else
              ---
            @endif
          </div>
        </div>

        @if($rfs->found_by == 'rbf' && count($ingredients_missing) && $confidence_group < 3)
          <div class="col-12 mb-1">
            <div class="acm-lbl-dark text-primary">+ Ingredients Missing:</div>
            <div>
              @php
              foreach($ingredients_missing as $ing):
              $ing_name = \App\Api\SysRobo::burger_ingredient_chicken_beef($ing['name']);

              @endphp
                <div class="acm-text-line-one">
                  - <b class="acm-mr-px-5 text-danger">{{$ing['ingredient_quantity']}}</b>
                  <span class="text-dark">
                    @if(!empty($ing['name_vi']))
                      {{$ing_name . ' - ' . $ing['name_vi']}}
                    @else
                      {{$ing_name}}
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
            @if(count($ingredients_found) && $confidence_group < 3)
            @php
              foreach($ingredients_found as $ing):

            @endphp
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

      </div>
    </div>

    @if(count($predictions))
      <ul class="cmt-wrapper">
        @if(count($versions) && isset($versions['dataset']))
          <li class="cmt-itm">
            <div class="d-flex overflow-hidden">
              <span>Dataset: {{$versions['dataset'] . '/' . $versions['version']}}</span>
            </div>
          </li>
        @endif
        @php
          $count = 0;
          foreach($predictions as $prediction):
          $prediction = (array)$prediction;

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
    <div class="acm-border-css p-1">
      <div class="row">
        <div class="col-12 mb-1 text-center">
          <div class="text-uppercase">
            <span class="badge bg-secondary">system</span>
          </div>
        </div>

        <div class="col-12 mb-1">
          <div class="acm-lbl-dark text-primary">+ Recipe Ingredients:</div>
          <div>
            @if(count($ingredients_recipe) && $confidence_group < 3)
              @php
                foreach($ingredients_recipe as $ing):
              @endphp
                <div class="acm-text-line-one">
                  - <b class="acm-mr-px-5 text-danger d-none">{{$ing['ingredient_quantity']}}</b>
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
  </div>

  <div class="col-lg-4 mb-1">
    <div class="acm-border-css p-1 @if($rfs->found_by == 'usr') bg-success-subtle @endif">
      <form onsubmit="return event.preventDefault();">
        <div class="row">
          <div class="col-12 mb-1 text-center position-relative clearfix overflow-hidden acm-height-30-min">
            @if($viewer->is_moderator())
            <div class="position-absolute acm-top-0">
              <button type="button" class="btn btn-sm btn-danger p-1" onclick="sensor_food_scan_update_prepare()">
                Update Result
              </button>
            </div>
            @endif
            <div class="position-absolute acm-top-0 acm-right-15px text-uppercase">
              <span class="badge bg-secondary">final status</span>
            </div>
          </div>
          <div class="col-12 mb-2 mt-2">
            <div class="form-floating form-floating-outline" id="final-status-wrapper">
              <div class="form-control acm-height-px-auto p-1">
                <div class="form-group clearfix p-1">
                  <div class="overflow-hidden">
                    <label class="acm-lbl-dark text-primary">+ Dish:</label>
                  </div>
                  @if($food)
                  <div class="overflow-hidden">
                    <div class="fw-bold">- {{$food->name}}</div>
                  </div>
                  @endif
                </div>

                <div class="form-group clearfix p-1">
                  <div class="overflow-hidden">
                    <label class="acm-lbl-dark text-primary">+ Ingredients Missing:</label>
                  </div>
                  <div class="overflow-hidden">
                    @if(count($ingredients_missing) && $confidence_group < 3)
                      @php
                        foreach($ingredients_missing as $ing):
                        $ing_name = \App\Api\SysRobo::burger_ingredient_chicken_beef($ing['name']);
                      @endphp
                        <div class="acm-text-line-one">
                          - <b class="acm-mr-px-5 text-danger">{{$ing['ingredient_quantity']}}</b>
                          <span class="text-dark">
                            @if(!empty($ing['name_vi']))
                              {{$ing_name . ' - ' . $ing['name_vi']}}
                            @else
                              {{$ing_name}}
                            @endif
                          </span>
                        </div>
                      @endforeach
                    @endif
                  </div>
                </div>

                <div class="form-group clearfix p-1">
                  @if($rfs->note_kitchen)
                  <div class="acm-float-right">
                      <span class="badge bg-success">Note Kitchen</span>
                  </div>
                  @endif
                  <div class="overflow-hidden">
                    <label class="acm-lbl-dark text-primary">+ Note:</label>
                  </div>
                  <div class="overflow-hidden">
                    @if(count($texts))
                      @foreach($texts as $text)
                        <div>- {{$text->name}}</div>
                      @endforeach
                    @endif
                    <div class="@if($rfs->note_kitchen) fw-bold @endif">- <?php echo nl2br($rfs->note)?></div>
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
