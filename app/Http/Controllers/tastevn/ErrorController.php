<?php

namespace App\Http\Controllers\tastevn;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ErrorController extends Controller
{

  public function index()
  {
    $pageConfigs = [
      'myLayout' => 'blank'
    ];
    return view('tastevn.pages.page_not_found', ['pageConfigs' => $pageConfigs]);
  }

}
