<?php

namespace App\Http\Controllers\tastevn\auth;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
//lib
use App\Api\SysApp;
//model
use App\Models\Comment;

class CommentController extends Controller
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

  public function note(Request $request)
  {
    $values = $request->post();

    $type = isset($values['type']) && !empty($values['type']) ? $values['type'] : NULL;
    $content = isset($values['content']) && !empty($values['content']) ? trim($values['content']) : NULL;
    $object_type = isset($values['object_type']) && !empty($values['object_type']) ? trim($values['object_type']) : NULL;
    $object_id = isset($values['object_id']) && !empty($values['object_id']) ? (int)$values['object_id'] : 0;
    $customer_requested = isset($values['customer_requested']) && !empty($values['customer_requested']) ? (int)$values['customer_requested'] : 0;
    $food_multi = isset($values['food_multi']) && !empty($values['food_multi']) ? (int)$values['food_multi'] : 0;
    $food_count = isset($values['food_count']) && !empty($values['food_count']) ? (int)$values['food_count'] : 0;
    if (empty($object_type) || !$object_id) {
      return response()->json([
        'error' => 'Invalid data'
      ], 422);
    }

    $item = $this->_sys_app->get_item($object_id, $object_type);
    if (!$item) {
      return response()->json([
        'error' => 'Invalid data'
      ], 422);
    }

    switch ($object_type) {
      case 'restaurant_food_scan':

        //customer_requested
        if (!$customer_requested) {
          $item->update([
            'customer_requested' => 0,
          ]);
        }
        if (!$item->customer_requested && $customer_requested) {
          $item->update([
            'customer_requested' => $this->_viewer->id,
          ]);
        }

        //count_foods
        if (!$food_multi) {
          $item->update([
            'count_foods' => 0,
          ]);
        }
        if ($food_multi && $food_count) {
          $item->update([
            'count_foods' => $food_count,
          ]);
        }

        break;
    }

    if ($type == 'kitchens') {

      $item->update([
        'note' => !empty($content) ? $content : '',
        'noter_id' => $this->_viewer->id,
      ]);

      $item->update_main_note($this->_viewer);

      return response()->json([
        'status' => true,
      ], 200);
    }

    $row = Comment::where('user_id', $this->_viewer->id)
      ->where('object_type', $object_type)
      ->where('object_id', $object_id)
      ->first();

    if ($row) {

      $diffs['before'] = $row->get_log();

      $row->update([
        'content' => !empty($content) ? $content : '',
        'edited' => 1,
      ]);

      $row = Comment::find($row->id);
      $diffs['after'] = $row->get_log();
      if (json_encode($diffs['before']) !== json_encode($diffs['after'])) {

        $row->on_update_after();

        $this->_viewer->add_log([
          'type' => 'edit_photo_note',
          'restaurant_id' => $item ? (int)$item->restaurant_id : 0,
          'item_id' => $item ? (int)$item->id : null,
          'item_type' => $item ? $item->get_type() : null,
          'params' => json_encode($diffs),
        ]);
      }

    } else {

      if (!empty($content)) {
        $row = Comment::create([
          'user_id' => $this->_viewer->id,
          'object_type' => $object_type,
          'object_id' => $object_id,
          'content' => $content,
        ]);

        $row->on_create_after();

        $this->_viewer->add_log([
          'type' => 'add_photo_note',
          'restaurant_id' => $item ? (int)$item->restaurant_id : 0,
          'item_id' => $item ? (int)$item->id : null,
          'item_type' => $item ? $item->get_type() : null,
          'params' => json_encode([
            'content' => $content,
          ])
        ]);
      }
    }

    return response()->json([
      'status' => true,
    ], 200);
  }
}
