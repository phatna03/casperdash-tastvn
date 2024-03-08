<?php

namespace App\Http\Controllers\tastevn\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;

use App\Notifications\ForgotPassword;
use App\Api\SysCore;

use Validator;
use App\Models\User;
use App\Models\RestaurantAccess;
use App\Models\PasswordResetToken;

class UserController extends Controller
{
  public function __construct()
  {
    $this->middleware(function ($request, $next) {
      return $next($request);
    });

    $this->middleware('auth');
  }

  /**
   * Display a listing of the resource.
   */
  public function index(Request $request)
  {
    $user = Auth::user();
    $invalid_roles = ['moderator', 'user'];
    if (in_array($user->role, $invalid_roles)) {
      return redirect('page_not_found');
    }

    $pageConfigs = [
      'myLayout' => 'horizontal',
      'hasCustomizer' => false,
    ];

    return view('tastevn.pages.users', ['pageConfigs' => $pageConfigs]);
  }

  /**
   * Show the form for creating a new resource.
   */
  public function create()
  {
    //
  }

  /**
   * Store a newly created resource in storage.
   */
  public function store(Request $request)
  {
    $values = $request->all();
    $viewer = Auth::user();
    //required
    $validator = Validator::make($values, [
      'name' => 'required|string',
      'email' => 'required|email',
    ]);
    if ($validator->fails()) {
      return response()->json($validator->errors(), 422);
    }
    //restore
    $row = User::where('email', $values['email'])
      ->first();
    if ($row) {
      if ($row->deleted) {
        return response()->json([
          'type' => 'can_restored',
          'error' => 'Item deleted'
        ], 422);
      }
      //existed
      return response()->json([
        'error' => 'Email existed'
      ], 422);
    }

    $row = User::create([
      'name' => trim($values['name']),
      'email' => trim($values['email']),
      'password' => Hash::make('cspr'),
      'phone' => $values['phone'],
      'status' => $values['status'],
      'role' => $values['role'],
      'note' => $values['note'],
      'creator_id' => $viewer->id,
      'access_full' => $values['role'] == 'admin' ? 1 : (int)$values['access_full'],
    ]);

    if (count($values['access_restaurants']) && $values['role'] == 'moderator') {
      foreach ($values['access_restaurants'] as $restaurant_id) {
        RestaurantAccess::create([
          'user_id' => $row->id,
          'restaurant_id' => (int)$restaurant_id,
        ]);
      }

      $row->access_restaurants();
    }

    return response()->json([
      'status' => true,
      'item' => $row->name,
    ], 200);
  }

  /**
   * Display the specified resource.
   */
  public function show(string $id)
  {
    //
  }

  /**
   * Show the form for editing the specified resource.
   */
  public function edit(string $id)
  {
    //
  }

  /**
   * Update the specified resource in storage.
   */
  public function update(Request $request)
  {
    $values = $request->all();
    $viewer = Auth::user();
    //required
    $validator = Validator::make($values, [
      'item' => 'required',
      'name' => 'required|string',
      'email' => 'required|email',
    ]);
    if ($validator->fails()) {
      return response()->json($validator->errors(), 422);
    }
    //invalid
    $row = User::findOrFail((int)$values['item']);
    if (!$row) {
      return response()->json([
        'error' => 'Invalid item'
      ], 422);
    }
    //restore
    $row1 = User::where('email', $values['email'])
      ->first();
    if ($row1) {
      if ($row1->deleted) {
        return response()->json([
          'type' => 'can_restored',
          'error' => 'Item deleted'
        ], 422);
      }
      //existed
      if ($row1->id != $row->id) {
        return response()->json([
          'error' => 'Email existed'
        ], 422);
      }
    }

    $row->update([
      'name' => trim($values['name']),
      'email' => trim($values['email']),
      'phone' => $values['phone'],
      'status' => $values['status'],
      'role' => $values['role'],
      'note' => $values['note'],
      'access_full' => $values['role'] == 'admin' ? 1 : (int)$values['access_full'],
    ]);

    RestaurantAccess::where('user_id', $row->id)
      ->delete();

    if (count($values['access_restaurants']) && $values['role'] == 'moderator') {
      foreach ($values['access_restaurants'] as $restaurant_id) {
        RestaurantAccess::create([
          'user_id' => $row->id,
          'restaurant_id' => (int)$restaurant_id,
        ]);
      }
    }

    $row->access_restaurants();

    return response()->json([
      'status' => true,
      'item' => $row->name,
    ], 200);
  }

  /**
   * Remove the specified resource from storage.
   */
  public function destroy(string $id)
  {
    //
  }

  public function delete(Request $request)
  {
    $values = $request->all();
    $viewer = Auth::user();
    //required
    $validator = Validator::make($values, [
      'item' => 'required',
    ]);
    if ($validator->fails()) {
      return response()->json($validator->errors(), 422);
    }

    $row = User::findOrFail((int)$values['item']);
    if (!$row) {
      return response()->json([
        'error' => 'Invalid item'
      ], 422);
    }

    $row->update([
      'deleted' => $viewer->id,
    ]);

    return response()->json([
      'status' => true,
      'item' => $row->name,
    ], 200);
  }

  public function restore(Request $request)
  {
    $values = $request->all();
    $viewer = Auth::user();
    //required
    $validator = Validator::make($values, [
      'item' => 'required',
    ]);

    if ($validator->fails()) {
      return response()->json($validator->errors(), 422);
    }

    $row = User::where('email', $values['item'])
      ->first();
    if (!$row) {
      return response()->json([
        'error' => 'Invalid item'
      ], 422);
    }

    $row->update([
      'deleted' => 0,
      'status' => 'active',
    ]);

    return response()->json([
      'status' => true,
      'item' => $row->name,
    ], 200);
  }

  public function profile(Request $request)
  {
    $user = Auth::user();

    $pageConfigs = [
      'myLayout' => 'horizontal',
      'hasCustomizer' => false,

    ];

    return view('tastevn.pages.profile', ['pageConfigs' => $pageConfigs]);
  }

  public function profile_update(Request $request)
  {
    $values = $request->all();
    $viewer = Auth::user();
    //required
    $validator = Validator::make($values, [
//      'item' => 'required',
      'name' => 'required|string',
      'email' => 'required|email',
    ]);
    if ($validator->fails()) {
      return response()->json($validator->errors(), 422);
    }
    //invalid
    $row = Auth::user();
    if (!$row) {
      return response()->json([
        'error' => 'Invalid item'
      ], 422);
    }
    //restore
    $row1 = User::where('email', trim($values['email']))
      ->where('id', '<>', $row->id)
      ->first();
    if ($row1) {
      return response()->json([
        'error' => 'Email existed'
      ], 422);
    }

    $row->update([
      'name' => trim($values['name']),
      'email' => trim($values['email']),
      'phone' => $values['phone'],
    ]);

    return response()->json([
      'status' => true,
      'item' => $row->name,
    ], 200);
  }

  public function profile_pwd_code(Request $request)
  {
    $values = $request->all();
    $api_core = new SysCore();

    $user = Auth::user();

    //token
    $token = strtoupper($api_core->random_str(6));

    $row = PasswordResetToken::where('email', $user->email)
      ->first();
    if ($row) {
      $row->update([
        'token' => $token,
      ]);
    } else {
      PasswordResetToken::create([
        'email' => $user->email,
        'token' => $token,
      ]);
    }

    //mail
    Notification::send($user, new ForgotPassword([
      'email' => $user->email,
      'code' => $token,
    ]));

    return response()->json([
      'status' => true,
      'user' => $token,
    ], 200);
  }

  public function profile_pwd_update(Request $request)
  {
    $values = $request->all();

    $validator = Validator::make($values, [
      'code' => 'required',
      'password' => 'min:8|required_with:password_confirmation|same:password_confirmation',
      'password_confirmation' => 'min:8'
    ]);

    if ($validator->fails()) {
      return response()->json($validator->errors(), 422);
    }

    $user = Auth::user();

    $user->update([
      'password' => Hash::make($values['password']),
    ]);

    PasswordResetToken::where('email', $user->email)
      ->delete();

    return response()->json([
      'status' => true,
      'user' => $user->name,
    ], 200);
  }

  public function profile_setting_update(Request $request)
  {
    $values = $request->all();
    $viewer = Auth::user();

    $viewer->update([
      'allow_printer' => isset($values['printer']) && $values['printer'] == 'yes' ? 1 : 0,
      'ips_printer' => $values['ips_printer'],
    ]);

    return response()->json([
      'status' => true,
      'item' => $viewer->name,
    ], 200);
  }
}
