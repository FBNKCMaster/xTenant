<?php

namespace FBNKCMaster\xTenant\Providers;

use Illuminate\Support\ServiceProvider as Base;

class ServiceProvider extends Base
{
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        // Register package's Artisan commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                \FBNKCMaster\xTenant\Console\Commands\Setup::class,
                \FBNKCMaster\xTenant\Console\Commands\Create::class,
                \FBNKCMaster\xTenant\Console\Commands\Destroy::class,
                \FBNKCMaster\xTenant\Console\Commands\Edit::class,
                \FBNKCMaster\xTenant\Console\Commands\Migrate::class,
                \FBNKCMaster\xTenant\Console\Commands\Seed::class,
                \FBNKCMaster\xTenant\Console\Commands\Directory::class,
            ]);
        }

        $router = $this->app['router'];
        
        if ($router->hasMiddlewareGroup('web')) {
            // Register package's routes & views
            if (\Schema::hasTable('tenants') && \Schema::hasTable('x_tenant_params')) {
                $this->loadRoutesFrom(__DIR__.'/../../routes/web.php');
            }
            
            // Register  package's  middleware
            // To make it work properly, it
            // should be registred right
            // after StartSession
            // middleware
            // ;-)
            $middlewareGroups = $router->getMiddlewareGroups();
            $webMiddlewareGroups = $middlewareGroups['web'];
            $newWebMiddlewareGroups = [];
            foreach ($webMiddlewareGroups as $middleware) {
                $newWebMiddlewareGroups[] = $middleware;
                if ($middleware == 'Illuminate\Session\Middleware\StartSession') {
                    $newWebMiddlewareGroups[] = '\FBNKCMaster\xTenant\Middleware\SelectTenant::class';
                }
            }
            $router->middlewareGroup('web', $newWebMiddlewareGroups);
        }

    }
    
    /**
     * Register any package services.
     *
     * @return void
     */
    public function register()
    {
        // 
    }
}