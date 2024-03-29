<?php

namespace App\Http\Controllers\tastevn\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;

use Validator;
use App\Models\User;
use App\Models\PasswordResetToken;

use Illuminate\Support\Facades\Notification;
use App\Notifications\ForgotPassword;

use App\Api\SysCore;

class AuthController extends Controller
{

  public function login(Request $request)
  {
    $values = $request->all();

    $validator = Validator::make($values, [
      'email' => 'required|email',
      'password' => 'required|string|min:4',
    ]);

    if ($validator->fails()) {
      return response()->json($validator->errors(), 422);
    }

    $credentials = request(['email', 'password']);

    $user = User::where('deleted', 0)
      ->where('status', 'active')
      ->where('email', $credentials['email'])
      ->first();
    if (!$user) {
      return response()->json([
        'error' => 'User inactive or deleted'
      ], 422);
    }

    if (!Auth::attempt($credentials, true)) {
      return response()->json([
        'error' => 'Wrong data'
      ], 422);
    }

    $user = Auth::user();

    $user->add_log([
      'type' => 'login'
    ]);

    $redirect_url = Redirect::getIntendedUrl();
    if ($user->role == 'user') {
      $redirect_url = url('admin/photos');
    }

    return response()->json([
      'status' => true,
      'user' => $user->info_public(),
      'redirect' => $redirect_url,
    ], 200);
  }

  public function send_code(Request $request)
  {
    $values = $request->all();
    $api_core = new SysCore();

    $validator = Validator::make($values, [
      'email' => 'required|email',
    ]);

    if ($validator->fails()) {
      return response()->json($validator->errors(), 422);
    }

    $credentials = request(['email', 'code', 'step']);

    $user = User::where('deleted', 0)
      ->where('status', 'active')
      ->where('email', $credentials['email'])
      ->first();
    if (!$user) {
      return response()->json([
        'error' => 'User inactive or deleted'
      ], 422);
    }

    $user = User::where('email', $credentials['email'])
      ->first();
    if (!$user) {
      return response()->json([
        'error' => 'User not found'
      ], 422);
    }

    if ($credentials['step'] == 'email') {
      //token
      $token = strtoupper($api_core->random_str(6));

      $row = PasswordResetToken::where('email', $credentials['email'])
        ->first();
      if ($row) {
        $row->update([
          'token' => $token,
        ]);
      } else {
        PasswordResetToken::create([
          'email' => $credentials['email'],
          'token' => $token,
        ]);
      }

      //mail
      Notification::send($user, new ForgotPassword([
        'email' => $credentials['email'],
        'code' => $token,
      ]));

    } elseif ($credentials['step'] == 'code') {

      $token = $credentials['code'];

      $row = PasswordResetToken::where('email', $credentials['email'])
        ->where('token', $token)
        ->first();
      if (!$row) {
        return response()->json([
          'error' => 'Invalid code'
        ], 422);
      }

      PasswordResetToken::where('email', $credentials['email'])
        ->where('token', $token)
        ->delete();
    }

    return response()->json([
      'status' => true,
      'user' => $token,
    ], 200);
  }

  public function update_pwd(Request $request)
  {
    $values = $request->all();

    $validator = Validator::make($values, [
      'email' => 'required|email',
      'password' => 'min:8|required_with:password_confirmation|same:password_confirmation',
      'password_confirmation' => 'min:8',
    ]);

    if ($validator->fails()) {
      return response()->json($validator->errors(), 422);
    }

    $credentials = request(['email', 'password']);

    $user = User::where('email', $credentials['email'])
      ->first();
    if (!$user) {
      return response()->json([
        'error' => 'User not found'
      ], 422);
    }

    $user->add_log([
      'type' => 'edit_pwd'
    ]);

    $user->update([
      'password' => Hash::make($credentials['password']),
    ]);

    return response()->json([
      'status' => true,
      'user' => $user->name,
    ], 200);
  }

  public function logout(Request $request)
  {
    $user = Auth::user();
    if ($user) {

      $user->add_log([
        'type' => 'logout'
      ]);

      Auth::logout();
    }

    return response()->json(['status' => true]);
  }


}
