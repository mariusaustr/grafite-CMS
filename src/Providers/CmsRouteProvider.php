<?php

namespace Grafite\Cms\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Config;

class CmsRouteProvider extends ServiceProvider
{
    /**
     * This namespace is applied to the controller routes in your routes file.
     *
     * In addition, it is set as the URL generator's root namespace.
     */
    protected $namespace = 'Grafite\Cms\Controllers';

    /**
     * Define your route model bindings, pattern filters, etc.
     */
    public function boot()
    {
        parent::boot();
    }

    /**
     * Define the routes for the application.
     */
    public function map(Router $router)
    {
        if (Config::get('cms.load-routes', false)) {
            require __DIR__.'/../Routes/web.php';
            require __DIR__.'/../Routes/api.php';
        }
    }
}
