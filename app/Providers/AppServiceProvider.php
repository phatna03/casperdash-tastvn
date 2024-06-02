<?php

namespace App\Providers;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

use App\Api\SysMobi;
use App\Api\SysApp;

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
    $api_mobi = new SysMobi();

    //https
    $this->app['request']->server->set('HTTPS', $this->app->environment() != 'local');

    //viewer
    view()->composer('*', function($view) {
      if (Auth::check()) {
        $view->with('viewer', Auth::user());
      } else {
        $view->with('viewer', null);
      }
    });

    View::share('sys_app', new SysApp());

    View::share('baseURL', url(''));
    View::share('isMobi', $api_mobi->isMobile());
    View::share('devMode', App::environment());
  }
}
