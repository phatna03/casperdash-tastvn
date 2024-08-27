<?php

namespace App\Http\Controllers\tastevn\auth;
use App\Http\Controllers\Controller;
use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
//lib
use App\Api\SysApp;
use App\Models\RestaurantFoodScan;

class PhotoController extends Controller
{
  protected $_viewer = null;
  protected $_sys_app = null;

  public function __construct()
  {
    $this->_sys_app = new SysApp();

    $this->middleware(function ($request, $next) {

      $this->_viewer = Auth::user();

      return $next($request);
    });

    $this->middleware('auth');
  }

  public function index(Request $request)
  {
    $values = $request->all();
    $photo = isset($values['photo']) && !empty($values['photo']) ? trim($values['photo']) : '';

    $pageConfigs = [
      'myLayout' => 'horizontal',
      'hasCustomizer' => false,

      'photo' => $photo,
    ];

    $this->_viewer->add_log([
      'type' => 'view_listing_photo',
    ]);

    return view('tastevn.pages.photos', ['pageConfigs' => $pageConfigs]);
  }

  public function get(Request $request)
  {
    $values = $request->all();
//    echo '<pre>';var_dump($values);die;
    $existed = isset($values['existed']) ? (array)$values['existed'] : [];
    $restaurants = isset($values['restaurants']) ? (array)$values['restaurants'] : [];
    $users = isset($values['users']) ? (array)$values['users'] : [];
    $noted = isset($values['noted']) && !empty($values['noted']) ? $values['noted'] : NULL;
    $time_upload = isset($values['time_upload']) && !empty($values['time_upload']) ? $values['time_upload'] : NULL;
    $keyword = isset($values['keyword']) && !empty($values['keyword']) ? trim($values['keyword']) : NULL;

    $select = RestaurantFoodScan::query('restaurant_food_scans')
      ->select('restaurant_food_scans.id', 'restaurant_food_scans.created_at',
        'restaurant_food_scans.photo_url', 'restaurant_food_scans.photo_name', 'restaurant_food_scans.local_storage',
        'restaurant_food_scans.time_photo', 'restaurants.name as restaurant_name')
      ->leftJoin('restaurants', 'restaurant_food_scans.restaurant_id', '=', 'restaurants.id')
      ->orderBy('restaurant_food_scans.time_photo', 'desc')
      ->orderBy('restaurant_food_scans.id', 'desc');

    //dev
    if ($this->_viewer->is_dev()) {

    } else {
      $select->where('restaurants.deleted', 0)
        ->where('restaurant_food_scans.deleted', 0)
        ->whereIn('restaurant_food_scans.status', [
          'checked', 'edited', 'failed',
        ])
      ;
    }

    //one_case
    $one_case = false;
    $rows = NULL;
    $query = NULL;

    if (!empty($keyword)) {

      $select1 = clone $select;
      $select1->where('restaurant_food_scans.id', $keyword);
      $rows = $select1->get();
      if (count($rows) == 1) {
        $one_case = true;

        $query = $this->_sys_app->parse_to_query($select1);
      }
      else {
        $select->where('restaurant_food_scans.id', 'LIKE', "%{$keyword}%");
      }
    }

    if (!$one_case) {

      if (count($existed)) {
        $select->whereNotIn("restaurant_food_scans.id", $existed);
      }
      if (count($restaurants)) {
        $select->whereIn("restaurant_food_scans.restaurant_id", $restaurants);
      }
      if (!empty($time_upload)) {
        $times = $this->_sys_app->parse_date_range($time_upload);
        if (!empty($times['time_from'])) {
          $select->where('restaurant_food_scans.time_photo', '>=', $times['time_from']);
        }
        if (!empty($times['time_to'])) {
          $select->where('restaurant_food_scans.time_photo', '<=', $times['time_to']);
        }
      }

      if (count($users)) {
        $select->whereIn("restaurant_food_scans.id", function ($q) use ($users) {
          $q->select('object_id')
            ->distinct()
            ->from('comments')
            ->where('object_type', 'restaurant_food_scan')
            ->whereIn('user_id', $users);
        });
      }

      if (!empty($noted)) {
        switch ($noted) {
          case 'yes':
            $select->where(function ($q) {
              $q->where('restaurant_food_scans.note', '<>', NULL)
                ->orWhereIn("restaurant_food_scans.id", function ($q1) {
                  $q1->select('object_id')
                    ->distinct()
                    ->from('comments')
                    ->where('object_type', 'restaurant_food_scan')
                    ->where('user_id', '>', 0);
                });
            });
            break;
        }
      }

      $select->limit(24);

      $query = $this->_sys_app->parse_to_query($select);

      $rows = $select->get();
    }

    $html = view('tastevn.htmls.item_photo')
      ->with('items', $rows)
      ->render();

    return response()->json([
      'html' => $html,
      'query' => $query,
    ]);
  }

  public function view(Request $request)
  {
    $values = $request->post();

    $row = RestaurantFoodScan::find((int)$values['item']);
    if ($row) {
      $this->_viewer->add_log([
        'type' => 'view_item_photo',
        'restaurant_id' => (int)$row->restaurant_id,
        'item_id' => (int)$row->id,
        'item_type' => $row->get_type(),
      ]);
    }

    return response()->json([

    ]);
  }

  public function note_get(Request $request)
  {
    $values = $request->post();

    $rfs = RestaurantFoodScan::find((int)$values['item']);
    if (!$rfs) {
      return response()->json([
        'error' => 'Invalid item'
      ], 404);
    }

    $user_comment = '';
    $comments = [];

    $select = Comment::query('comments')
      ->select('users.name as user_name', 'users.id as user_id', 'comments.content', 'comments.created_at')
      ->leftJoin('users', 'users.id', '=', 'comments.user_id')
      ->where('comments.deleted', 0)
      ->where('comments.object_id', $rfs->id)
      ->where('comments.object_type', 'restaurant_food_scan')
      ->orderBy('comments.id', 'asc');
    $rows = $select->get();
    if (count($rows)) {
      foreach ($rows as $row) {

        if ($row->user_id == $this->_viewer->id) {
          $user_comment = $row->content;
        }

        $comments[] = [
          'user_name' => $row->user_name,
          'user_noted' => $row->content,
          'created_at_1' => date('d/m/Y', strtotime($row->created_at)),
          'created_at_2' => date('H:i:s', strtotime($row->created_at)),
        ];
      }
    }

    return response()->json([
      'note' => $rfs->note,
      'noter' => $rfs->get_noter(),
      'comments' => $comments,
      'user_comment' => $user_comment,

      'customer_requested' => $rfs->customer_requested,
      'count_foods' => $rfs->count_foods,

    ]);
  }
}
