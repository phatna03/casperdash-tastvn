<?php

namespace App\Http\Controllers\tastevn\auth;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
//lib
use App\Api\SysApp;
use App\Api\SysCore;
use App\Api\SysRobo;
//model
use App\Models\RestaurantFoodScan;

class NotificationController extends Controller
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

    $page = isset($values['page']) && (int)$values['page'] > 1 ? (int)$values['page'] : 1;

    $select = $this->_viewer->notifications()
      ->orderBy('created_at', 'desc');

    //dev
    if (!$this->_viewer->is_dev()) {
      $select->where('restaurant_id', '<>', 7);
    }

    $notifications = $select->paginate(10, ['*'], 'page', $page);

    $pageConfigs = [
      'myLayout' => 'horizontal',
      'hasCustomizer' => false,

      'notifications' => $notifications,
      'totalPages' => $notifications->lastPage(),
      'currentPage' => $page,

      'vars' => $values,
    ];

    $this->_viewer->add_log([
      'type' => 'view_listing_notification',
    ]);

    return view('tastevn.pages.notification', ['pageConfigs' => $pageConfigs]);
  }

  public function notification_read(Request $request)
  {
    $values = $request->post();

    if (isset($values['item'])) {
      Auth::user()
        ->unreadNotifications
        ->when($values['item'], function ($q) use ($values) {
          return $q->where('id', $values['item']);
        })
        ->markAsRead();
    }

    return response()->noContent();
  }

  public function notification_read_all(Request $request)
  {
    Auth::user()
      ->unreadNotifications
      ->markAsRead();

    return response()->noContent();
  }

  public function notification_latest(Request $request)
  {
    $html = '';

    $select = $this->_viewer->notifications()
      ->orderBy('created_at', 'desc');

    //dev
    if (!$this->_viewer->is_dev()) {
      $select->where('restaurant_id', '<>', 7);
    }

    $notifications = $select->paginate(20, ['*'], 'page', 1);

    if (count($notifications)) {
      $html = view('tastevn.htmls.item_notification_navbar')
        ->with('notifications', $notifications)
        ->render();
    }

    return response()->json([
      'html' => $html,
    ]);
  }

  public function notification_newest()
  {
    $items = [];
    $ids = [];

    $printer = false;
    $text_to_speech = false;
    $text_to_speak = '';
    $valid_types = [
      //force
      'App\Notifications\IngredientMissing'
    ];

    //temp off
//    //speaker
//    if ((int)$this->_viewer->get_setting('missing_ingredient_alert_speaker')) {
//      $text_to_speech = true;
//    }
//
//    //printer
//    if ((int)$this->_viewer->get_setting('missing_ingredient_alert_printer')) {
//      $printer = true;
//    }

    if (!empty($this->_viewer->time_notification)) {

      $select = DB::table('notifications')
        ->distinct()
        ->where('notifiable_type', 'App\Models\User')
        ->where('notifiable_id', $this->_viewer->id)
        ->whereIn('type', $valid_types)
        ->orderBy('created_at', 'desc')
        ->limit(1);

      //dev
      if (!$this->_viewer->is_dev()) {
        $select->where('restaurant_id', '<>', 7);
      }

      $notifications = $select->get();
      if (count($notifications)) {
        foreach ($notifications as $notification) {
          $row = RestaurantFoodScan::find($notification->restaurant_food_scan_id);
          if (!$row || ($row && empty($row->missing_texts)) || !$row->get_food()) {
            continue;
          }

          $ingredients = array_filter(explode('&nbsp', $row->missing_texts));
          if (!count($ingredients)) {
            continue;
          }

          //time
          if (strtotime(date('Y-m-d H:i:s', strtotime($this->_viewer->time_notification)))
            >= strtotime(date('Y-m-d H:i:s', strtotime($row->created_at)))) {
            continue;
          }

          $items[] = [
            'itd' => $row->id,
            'photo_url' => $row->get_photo(),
            'restaurant_name' => $row->get_restaurant()->name,
            'food_name' => $row->get_food()->name,
            'food_confidence' => $row->confidence,
            'ingredients' => $ingredients,

            'time_notification' => date('Y-m-d H:i:s', strtotime($this->_viewer->time_notification)),
            'created_at' => date('Y-m-d H:i:s', strtotime($row->created_at)),

            'time1' => strtotime(date('Y-m-d H:i:s', strtotime($this->_viewer->time_notification))),
            'time2' => strtotime(date('Y-m-d H:i:s', strtotime($row->created_at)))
          ];

          $ids[] = $row->id;

          if ($text_to_speech) {

            $text_ingredients_missing = '';
            foreach ($row->get_ingredients_missing() as $ing) {
              $text_ingredients_missing .= $ing['quantity'] . ' ' . SysRobo::burger_ingredient_chicken_beef($ing['name']) . ', ';
            }

            $text_to_speak = '[Missing], '
              . $text_ingredients_missing
              . ', [Need to re-check]'
            ;

            $this->_sys_app->aws_s3_polly([
              'text_to_speak' => $text_to_speak,
              'text_rate' => 'slow',
            ]);
          }
        }
      }
    }

    $this->_viewer->update([
      'time_notification' => count($items) ? $items[0]['created_at'] : $this->_viewer->time_notification,
    ]);

    return response()->json([
      'items' => $items,
      'ids' => $ids,
      'role' => $this->_viewer->role,
      'speaker' => $text_to_speech && !empty($text_to_speak),
      'speaker_text' => $text_to_speak,
      'printer' => $printer,
    ]);
  }

}
