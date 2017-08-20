<?php

namespace Vuetified\Providers;

use Vuetified\Vuetified;
use Vuetified\Observers\UserObserver;
use Vuetified\Console\Commands\VersionCommand;
use Vuetified\Console\Commands\GenerateEchoAppID;
use Vuetified\Console\Commands\GenerateEchoAppKey;
use Vuetified\Console\Commands\GenerateEchoKeys;
use Vuetified\Console\Presets\PresetCommand;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Auth;


class VuetifiedServiceProvider extends ServiceProvider
{

    public function boot()
    {   
        /* publish User Model */
        $this->publishes([
        __DIR__.'/../../models/User.php' => base_path(Vuetified::userModel().'.php')
        ], 'user');
        /* add Config */
        $this->publishes([
        __DIR__.'/../../config/echo.php' => config_path('package.php')
        ], 'config');
        /* Load Migrations */
        $this->publishes([
        __DIR__.'/../../database/migrations' => database_path('migrations')
        ], 'migrations');
        /* add Middleware Alias */
        $this->addMiddlewareAlias('api.cors', \Barryvdh\Cors\HandleCors::class);
        $this->addMiddlewareAlias('web.token', \Laravel\Passport\Http\Middleware\CreateFreshApiToken::class);
        /* add Routes */
        $this->defineRoutes();
        /* Listen To User Class */
        Vuetified::userModel()::observe(UserObserver::class);
        // we need a custom service provider to declare $protected $userModel
        
    }

    public function register()
    {
        if (! class_exists('Vuetified')) {
            class_alias('Vuetified\Vuetified', 'Vuetified');
        }

        if ($this->app->runningInConsole()) {
            $this->commands([
                /* Add Vuetified Console Commands */
                VersionCommand::class,
                PresetCommand::class,
                GenerateEchoAppID::class,
                GenerateEchoAppKey::class,
                GenerateEchoKeys::class
            ]);
        }

        $this->registerServices();
    }

        /**
     * Define the Vuetified routes.
     *
     * @return void
     */
    protected function defineRoutes()
    {

        // If the routes have not been cached, we will include them in a route group
        // so that all of the routes will be conveniently registered to the given
        // controller namespace. After that we will load the Specific routes file.
        if (! $this->app->routesAreCached()) {
            Route::group([
                'middleware' => ['api','api.cors'],
                'namespace' => 'Vuetified\Http\Controllers\Api\Auth'],
                function ($router) {
                    require __DIR__.'/../Routes/api.php';
                }
            );
            Route::group([
                'middleware' => ['web.token','web']],
                function ($router) {
                    require __DIR__.'/../Routes/web.php';
                }
            );
            Route::group([
                'middleware' => ['web.token','web']],
                function ($router) {
                    require __DIR__.'/../Routes/web-example.php';
                }
            );
        }
    }

    /**
     * Register the Royalflush services.
     *
     * @return void
     */
    protected function registerServices()
    {
        
        $services = [
        //   Contracts                       =   Implementation Of Contracts
            'Contracts\InitialFrontendState' => 'InitialFrontendState',
        ];

        foreach ($services as $key => $value) {
            $this->app->singleton('Vuetified\\'.$key, 'Vuetified\\'.$value);
        }
    }
 
}
