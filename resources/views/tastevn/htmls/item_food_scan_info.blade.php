<div class="row">
  <div class="col-lg-6 mb-1">
    <div class="acm-border-css p-1">
      <div class="text-center w-auto p-1">
        <div class="text-uppercase">
          <span class="badge bg-secondary">photo standard</span>
        </div>
        <img class="acm-width-max-100 h-auto acm-border-css" src="{{$data['food']['photo']}}"/>
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
        <img class="acm-width-max-100 h-auto acm-border-css" src="{{$item['photo_url']}}"/>
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

  @if($viewer->role != 'user')
    @if($viewer->role == 'moderator')
      <div class="col-lg-4 mb-1">
        <div class="acm-border-css p-1">
          <div class="row">
            <div class="col-12 mb-1 text-center">
              <div class="text-uppercase">
                <span class="badge bg-secondary">dish info</span>
              </div>
            </div>
            <div class="col-12 mb-1">
              <div class="acm-lbl-dark text-primary">+ Predicted dish:</div>
              <div class="acm-text-line-one">
                @if(!empty($data['food']['name']))
                  - <span class="text-dark">{{$data['food']['name']}}</span>
                @else
                  ---
                @endif
              </div>
            </div>
            <div class="col-12 mb-1">
              <div class="acm-lbl-dark text-primary">
                @if(count($data['food']['recipes']))
                  + Recipe Ingredients:
                @else
                  + Roboflow Ingredients:
                @endif
              </div>
              <div>
                @if(count($data['food']['recipes']))
                  @foreach($data['food']['recipes'] as $ing)
                    <div class="acm-text-line-one">
                      - <span class="text-dark">{{$ing['name']}}</span>
                    </div>
                  @endforeach
                @elseif(count($data['food']['ingredients']))
                  @foreach($data['food']['ingredients'] as $ing)
                    <div class="acm-text-line-one">
                      - <b class="acm-mr-px-5 text-danger">{{$ing['ingredient_quantity']}}</b> <span class="text-dark">{{$ing['name']}}</span>
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
        <div class="acm-border-css p-1">
          <div class="row">
            <div class="col-12 mb-1 text-center">
              <div class="text-uppercase">
                <span class="badge bg-secondary">system</span>
              </div>
            </div>
            <div class="col-12 mb-1">
              <div class="acm-lbl-dark text-primary">+ Predicted dish:</div>
              <div class="acm-text-line-one">
                @if($item['found_by'] == 'rbf')
                  @if((int)$data['rbf']['food_id'])
                    - <b class="acm-mr-px-5 text-danger">{{$data['rbf']['food_confidence']}}
                      %</b> <span class="text-dark">{{$data['rbf']['food_name']}}</span>
                  @else
                    ---
                  @endif
                @elseif($item['found_by'] == 'sys')
                  @if((int)$data['sys']['food_id'])
                    - <b class="acm-mr-px-5 text-danger">{{$data['sys']['food_confidence']}}
                      %</b> <span class="text-dark">{{$data['sys']['food_name']}}</span>
                  @else
                    ---
                  @endif
                @endif
              </div>
            </div>
            @if($item['found_by'] == 'rbf')
              <div class="col-12 mb-1">
                <div class="acm-lbl-dark text-primary">+ Ingredients Missing:</div>
                <div>
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
                  @else
                    ---
                  @endif
                </div>
              </div>
            @elseif((int)$data['sys']['food_id'])
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
            @endif
          </div>
        </div>
      </div>
    @else
      <div class="col-lg-4 mb-1">
        <div class="acm-border-css p-1 @if($item['found_by'] == 'rbf') bg-success-subtle @endif">
          <div class="row">
            <div class="col-12 mb-1 text-center">
              <div class="text-uppercase">
                <span class="badge bg-secondary">roboflow</span>
              </div>
            </div>
            <div class="col-12 mb-1">
              <div class="acm-lbl-dark text-primary">+ Predicted dish:</div>
              <div class="acm-text-line-one">
                @if((int)$data['rbf']['food_id'])
                  - <b class="acm-mr-px-5 text-danger">{{$data['rbf']['food_confidence']}}
                    %</b> <span class="text-dark">{{$data['rbf']['food_name']}}</span>
                @else
                  ---
                @endif
              </div>
            </div>
            <div class="col-12 mb-1">
              <div class="acm-lbl-dark text-primary">+ Ingredients found:</div>
              <div>
                @if(count($data['rbf']['ingredients_found']))
                  @foreach($data['rbf']['ingredients_found'] as $ing)
                    <div class="acm-text-line-one">
                      - <b class="acm-mr-px-5 text-danger">{{$ing['quantity']}}</b> <span class="text-dark">{{$ing['title']}}</span>
                    </div>
                  @endforeach
                @else
                  ---
                @endif
              </div>
            </div>
            @if($item['found_by'] == 'rbf')
              <div class="col-12 mb-1">
                <div class="acm-lbl-dark text-primary">+ Ingredients Missing:</div>
                <div>
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
    @endif

    <div class="col-lg-4 mb-1">
      <div class="acm-border-css p-1 @if($item['found_by'] == 'usr') bg-success-subtle @endif">
        <form onsubmit="return event.preventDefault();">
          <div class="row">
            <div class="col-12 mb-1 text-center position-relative clearfix overflow-hidden acm-height-30-min">
              <div class="position-absolute acm-top-0">
                <button type="button" class="btn btn-sm btn-danger p-1" onclick="restaurant_food_scan_result_update_confirm()">
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
  @endif
</div>
