<?php


namespace Sandervankasteel\LaravelDuskScreenrecordings;


use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;

class ServiceProvider extends BaseServiceProvider
{

    public function boot()
    {
        $this->loadViewsFrom(__DIR__ . '/../views', 'screenrecording');

        if(!$this->app->environment('production')) {
            Route::group([
                'prefix' => '_screenrecording',
                'domain' => null,
            ], function() {
                Route::get('/bootstrap', function () {
                    return View::make('screenrecording::bootstrap');
                });
            });
        }
    }

    public function register()
    {
        parent::register();
    }
}
