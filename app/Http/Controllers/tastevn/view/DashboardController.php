<?php

namespace App\Http\Controllers\tastevn\view;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;

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
    $pageConfigs = [
      'myLayout' => 'horizontal',
      'hasCustomizer' => false,
    ];

    return view('tastevn.pages.dashboard', ['pageConfigs' => $pageConfigs]);
  }

  public function notification(Request $request)
  {
    $values = $request->all();
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

    $items = [];
    $ids = [];
    $updated = false;

    if (!empty($user->time_notification)) {

      $notifications = $user->notifications()
        ->where('created_at', '>', $user->time_notification)
        ->orderBy('id', 'asc')
        ->limit(5)
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
          $updated = true;
        }
      }

    } else {

      $updated = true;
    }

    if ($updated) {
      $user->update([
        'time_notification' => date('Y-m-d H:i:s')
      ]);
    }

    return response()->json([
      'items' => $items,
      'ids' => $ids,
      'role' => $user->role,
    ]);
  }


}
