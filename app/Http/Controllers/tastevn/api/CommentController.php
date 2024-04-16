<?php

namespace App\Http\Controllers\tastevn\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Validator;
use App\Api\SysCore;
use App\Models\Comment;

class CommentController extends Controller
{
  public function __construct()
  {
    $this->middleware(function ($request, $next) {
      return $next($request);
    });

    $this->middleware('auth');
  }

  public function note(Request $request)
  {
    $values = $request->post();
    $user = Auth::user();
    $api_core = new SysCore();

    $content = isset($values['content']) && !empty($values['content']) ? trim($values['content']) : NULL;
    $object_type = isset($values['object_type']) && !empty($values['object_type']) ? trim($values['object_type']) : NULL;
    $object_id = isset($values['object_id']) && !empty($values['object_id']) ? (int)$values['object_id'] : 0;
    if (empty($object_type) || !$object_id) {
      return response()->json([
        'error' => 'Invalid data'
      ], 422);
    }

    $item = $api_core->get_item($object_id, $object_type);

    $row = Comment::where('user_id', $user->id)
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

        $user->add_log([
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
          'user_id' => $user->id,
          'object_type' => $object_type,
          'object_id' => $object_id,
          'content' => $content,
        ]);

        $row->on_create_after();

        $user->add_log([
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
      'item' => $row->id,
    ], 200);
  }
}
