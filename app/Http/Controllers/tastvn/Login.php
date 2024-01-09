<?php

namespace App\Http\Controllers\tastvn;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

use App\Models\User;

class Login extends Controller
{
  public function index()
  {
    $pageConfigs = ['myLayout' => 'blank'];
    return view('tastvn.pages.auth.login', ['pageConfigs' => $pageConfigs]);
  }

  public function forgot_email()
  {
    $pageConfigs = ['myLayout' => 'blank'];
    return view('tastvn.pages.auth.forgot_email', ['pageConfigs' => $pageConfigs]);
  }

  public function forgot_code()
  {
    $pageConfigs = ['myLayout' => 'blank'];
    return view('tastvn.pages.auth.forgot_code', ['pageConfigs' => $pageConfigs]);
  }
}
