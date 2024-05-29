<?php

namespace App\Providers;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

use App\Api\SysCore;
use App\Api\SysMobi;

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
    $api_mobi = new SysMobi();

    //viewer
    view()->composer('*', function($view) {
      if (Auth::check()) {
        $view->with('viewer', Auth::user());
      } else {
        $view->with('viewer', null);
      }
    });

    View::share('api_core', $api_core);

    View::share('baseURL', url(''));
    View::share('isMobi', $api_mobi->isMobile());
    View::share('devMode', App::environment());
  }
}
