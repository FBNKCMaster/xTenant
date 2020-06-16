<?php

namespace FBNKCMaster\xTenant\Providers;

use Illuminate\Support\ServiceProvider as Base;

//use Illuminate\Support\Facades\Auth;
//use Illuminate\Contracts\Auth\Guard;

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
                \FBNKCMaster\xTenant\Console\Commands\BackupDatabase::class,
                \FBNKCMaster\xTenant\Console\Commands\BackupDirectory::class,
            ]);
        }

        $router = $this->app['router'];
        
        if ($router->hasMiddlewareGroup('web')) {
            // Register package's routes & views
            if (\Schema::hasTable('tenants') && \Schema::hasTable('x_tenant_settings')) {
                $this->loadRoutesFrom(__DIR__ . '/../../routes/web.php');
                $this->loadViewsFrom(__DIR__ . '/../../resources/views', 'xtenant');

                \View::share('tenants', \FBNKCMaster\xTenant\Models\Tenant::getAllTenants());
                \View::share('super_admin_subdomain', \FBNKCMaster\xTenant\Models\XTenantSetting::getSuperAdminSubdomain());
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
        $this->setBaseUrl();

        $this->setCustomGuard();
    }

    private function setBaseUrl()
    {
        // Update APP_URL & ASSET_URL base
        $request = $this->app->request;
        $scheme = $request->getScheme();
        $host = $request->getHost();

        //if (!is_null($allowWww) && $allowWww == true && strpos($host, 'www.') === 0) {
            $host = str_replace('www.', '', $host);
        //}

        // To avoid "Undefined offset" for list function
        // use array_pad() to prepend empty subdomain
        $nParts = substr_count($host, '.') > 1 ? 2 : 1;
        list($subdomain, $domain) = array_pad(explode('.', $host, $nParts), -2, null);
        
        $appUrl = $scheme . '://' . $domain;
        $assetUrl = $appUrl . '/' . ($subdomain ? $subdomain . '/' : '')  . 'public';
        
        config([
            'app.url' => $appUrl,
            'app.asset_url' => $assetUrl,
        ]);
    }

    private function setCustomGuard()
    {
        // Add custom guard
        /* Auth::extend('superadmin', function ($app, $name, array $config) {
            return new UserProvider(Auth::createUserProvider($config['provider']), $app->make('request'));
        });

        // Add custom guard provider
        Auth::provider('superadmin', function ($app, array $config) {
            return new Guard($app->make(\FBNKCMaster\xTenant\Models\XTenantSetting::class));
        }); */

        // I don't know why it didn't work with the above method
        // so I hard code it the way below and it worked :)
        config(['auth.guards.superadmin.driver' => 'session']);
        config(['auth.guards.superadmin.provider' => 'superadmin']);

        config(['auth.providers.superadmin.driver' => 'eloquent']);
        config(['auth.providers.superadmin.model' => \FBNKCMaster\xTenant\Models\XTenantSetting::class]);
    }
}