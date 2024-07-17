@extends('tastevn/layouts/layoutMaster')

@section('title', 'Admin - Dishes Checker')

@section('content')
  @php
  $restaurants = $pageConfigs['restaurants'];
  $foods = $pageConfigs['foods'];

  @endphp

  <div class="card">
    <div class="card-body p-2">
      <div class="table_fixed_first">
        <table class="table">
          <thead>
          <tr>
            <th class="td_fixed">Dishes / Restaurants</th>
            @foreach($restaurants as $restaurant)
              <th class="td_content td_title_restaurant">{{$restaurant->name}}</th>
            @endforeach
          </tr>
          </thead>
          <tbody>
          @php
            $stt = 0;
              foreach($foods as $food):
            $stt++;
          @endphp
          <tr class="tr_food_{{$food->id}}">
            <th class="td_fixed">
              <div>{{$stt . '. '}}</div>
              <div>{{$food->name}}</div>
            </th>
            @foreach($restaurants as $restaurant)
              <td class="td_content tr_restaurant_food_{{$restaurant->id}}_{{$food->id}}"></td>
            @endforeach
          </tr>
          @endforeach
          </tbody>
        </table>
      </div>
    </div>
  </div>

@endsection

@section('js_end')
  <script type="text/javascript">
    var $ = jQuery.noConflict();

    $('.page_main_content').removeClass('container-xxl');

    $(document).ready(function() {

      @foreach($restaurants as $restaurant)
        restaurant_food_serve({{$restaurant->id}});
      @endforeach

    });
  </script>
@endsection
