<?php

namespace App\Http\Controllers\tastevn\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

use Validator;
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
//    echo '<pre>';var_dump($values);die;
    $user = Auth::user();

    $content = isset($values['content']) && !empty($values['content']) ? trim($values['content']) : NULL;
    $object_type = isset($values['object_type']) && !empty($values['object_type']) ? trim($values['object_type']) : NULL;
    $object_id = isset($values['object_id']) && !empty($values['object_id']) ? (int)$values['object_id'] : 0;
    if (empty($content) || empty($object_type) || !$object_id) {
      return response()->json([
        'error' => 'Invalid data'
      ], 422);
    }

    $row = Comment::where('user_id', $user->id)
      ->where('object_type', $object_type)
      ->where('object_id', $object_id)
      ->first();

    if ($row) {
      $row->update([
        'content' => $content,
        'edited' => 1,
      ]);
    } else {
      $row = Comment::create([
        'user_id' => $user->id,
        'object_type' => $object_type,
        'object_id' => $object_id,
        'content' => $content,
      ]);
    }

    return response()->json([
      'status' => true,
      'item' => $row->id,
    ], 200);
  }
}
