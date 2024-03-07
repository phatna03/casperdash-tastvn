<?php

namespace App\Providers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

use App\Api\SysCore;

class AppServiceProvider extends ServiceProvider
{
  /**
   * Register any application services.
   */
  public function register(): void
  {
    //
  }

  /**
   * Bootstrap any application services.
   */
  public function boot(): void
  {
    //custome
    $api_core = new SysCore();

    //viewer
    view()->composer('*', function($view) {
      if (Auth::check()) {
        $view->with('viewer', Auth::user());
      } else {
        $view->with('viewer', null);
      }
    });

    View::share('baseURL', url(''));
  }
}
