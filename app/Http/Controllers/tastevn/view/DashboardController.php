<?php

namespace App\Http\Controllers\tastevn\view;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Api\SysCore;

use App\Models\RestaurantFoodScan;

class DashboardController extends Controller
{
  public function __construct()
  {
    $this->middleware(function ($request, $next) {
      return $next($request);
    });

    $this->middleware('auth');
  }

  public function index(Request $request)
  {
    $user = Auth::user();
    $invalid_roles = ['user'];
    if (in_array($user->role, $invalid_roles)) {
      return redirect('admin/photos');
    }

    $pageConfigs = [
      'myLayout' => 'horizontal',
      'hasCustomizer' => false,
    ];

    $user->add_log([
      'type' => 'view_listing_restaurant',
    ]);

    return view('tastevn.pages.dashboard', ['pageConfigs' => $pageConfigs]);
  }

  public function notification(Request $request)
  {
    $values = $request->all();
    $user = Auth::user();

    $page = isset($values['page']) && (int)$values['page'] > 1 ? (int)$values['page'] : 1;

    $notifications = Auth::user()->notifications()
      ->orderBy('id', 'desc')
      ->paginate(10, ['*'], 'page', $page);

    $pageConfigs = [
      'myLayout' => 'horizontal',
      'hasCustomizer' => false,

      'notifications' => $notifications,
      'totalPages' => $notifications->lastPage(),
      'currentPage' => $page,

      'vars' => $values,
    ];

    $user->add_log([
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
    $notifications = Auth::user()->notifications()
      ->orderBy('id', 'desc')
      ->paginate(5, ['*'], 'page', 1);

    $html = '';
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
    $user = Auth::user();
    $api_core = new SysCore();

    $items = [];
    $ids = [];

    $text_to_speech = false;
    $text_to_speak = '';
    $valid_types = [];

    //user_setting
    if ((int)$user->get_setting('missing_ingredient_receive')
      && (int)$user->get_setting('missing_ingredient_alert_realtime')
    ) {
      $valid_types[] = 'App\Notifications\IngredientMissing';
    }

    if ((int)$user->get_setting('missing_ingredient_alert_speaker')) {
      $text_to_speech = true;
    }

    //allow_printer
    if ((int)$user->get_setting('allow_printer')) {
      $valid_types[] = 'App\Notifications\IngredientMissing';
    }

    if (!empty($user->time_notification)) {

      $notifications = $user->notifications()
        ->whereIn('type', $valid_types)
        ->where('created_at', '>', $user->time_notification)
        ->orderBy('id', 'asc')
        ->limit(1)
        ->get();

      if (count($notifications)) {
        foreach ($notifications as $notification) {
          $row = RestaurantFoodScan::find($notification->data['restaurant_food_scan_id']);
          if (!$row || empty($row->photo_url)) {
            continue;
          }

          $ingredients = array_filter(explode('&nbsp', $row->missing_texts));
          if (!count($ingredients)) {
            continue;
          }

          $items[] = [
            'itd' => $row->id,
            'photo_url' => $row->photo_url,
            'restaurant_name' => $row->get_restaurant()->name,
            'food_name' => $row->get_food()->name,
            'food_confidence' => $row->confidence,
            'ingredients' => $ingredients,
          ];

          $ids[] = $row->id;

          $user->update([
            'time_notification' => $notification->created_at->format('Y-m-d H:i:s')
          ]);

          if ($text_to_speech) {

            $text_ingredients_missing = '';
            foreach ($row->get_ingredients_missing() as $ing) {
              $text_ingredients_missing .= $ing['ingredient_quantity'] . ' ' . $ing['name'] . ', ';
            }

            $text_to_speak = '[alert]'
              . $row->get_restaurant()->name . ' occurred at '
              . date('H:i')
              . ", Ingredients Missing, "
              . $text_ingredients_missing;

            $api_core->s3_polly([
              'text_to_speak' => $text_to_speak,
            ]);
          }
        }

      } else {

        $user->update([
          'time_notification' => date('Y-m-d H:i:s')
        ]);
      }

    } else {

      $user->update([
        'time_notification' => date('Y-m-d H:i:s')
      ]);
    }

    return response()->json([
      'items' => $items,
      'ids' => $ids,
      'role' => $user->role,
      'speaker' => $text_to_speech && !empty($text_to_speak),
    ]);
  }


}
