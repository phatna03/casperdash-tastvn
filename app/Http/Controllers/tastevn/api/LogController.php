<?php

namespace App\Http\Controllers\tastevn\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Api\SysCore;

use Validator;
use App\Models\Log;

class LogController extends Controller
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
    $invalid_roles = ['user', 'moderator'];
    if (in_array($user->role, $invalid_roles)) {
      return redirect('page_not_found');
    }

    $api_core = new SysCore();

    $pageConfigs = [
      'myLayout' => 'horizontal',
      'hasCustomizer' => false,

      'options_type' => $api_core->get_log_types(),
      'options_item' => $api_core->get_log_items(),
    ];

    $user->add_log([
      'type' => 'view_listing_log',
    ]);

    return view('tastevn.pages.logs', ['pageConfigs' => $pageConfigs]);
  }

  public function create(Request $request)
  {
    //
  }

  /**
   * Store a newly created resource in storage.
   */
  public function store(Request $request)
  {
    //
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
    //
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
    //
  }

  public function restore(Request $request)
  {
    //
  }
}
