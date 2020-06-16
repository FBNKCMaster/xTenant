<?php

namespace FBNKCMaster\xTenant\Middleware;

use Closure;
use DB;
use Str;

use FBNKCMaster\xTenant\Models\Tenant;
use FBNKCMaster\xTenant\Models\XTenantSetting;
use FBNKCMaster\xTenant\Helpers\XTenantHelper;

use Illuminate\Support\Facades\Route;

class SelectTenant
{
    /**
     * Handle an incoming request.
     *
     * @setting  \Illuminate\Http\Request  $request
     * @setting  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // Check if xTenant is setup
        if (\Schema::hasTable('tenants') && \Schema::hasTable('x_tenant_settings')) {

            // Check if it's SuperAdmin
            if (XTenantSetting::isSuperAdmin($request)) {
                return $next($request);
            }

            // Get settings
            $xTenantSettings = XTenantSetting::getSettings();

            // Check if tenant was registred
            $result = Tenant::findTenant($request, $xTenantSettings->allow_www);
            $subdomain = $result['subdomain'];
            $tenant = $result['tenant'];
            if (!is_null($tenant)) {
                // Check if the tenant is enabled
                if (!$tenant->isEnabled()) {
                    abort(403, 'This tenant is currently disabled.');
                }

                // Get tenant's database
                $database = $this->getTenantsDatabase($subdomain);
                // and check it
                if (!is_null($database) && $database == $tenant->database) {
                    // Save config values
                    $defaultConnection = DB::getDefaultConnection();
                    $defaultDatabase = config()->get('database.connections.' . $defaultConnection . '.database');
                    config([
                        'xtenant.connection' => $defaultConnection,
                        'xtenant.database.default' => $defaultDatabase,
                        'xtenant.database.current' => $database,

                        'xtenant.domain' => XTenantSetting::getDomain(),
                        'xtenant.super_admin_subdomain' => $xTenantSettings->super_admin_subdomain,
                        'xtenant.subdomain' => $subdomain,
                        'xtenant.allow_www' => $xTenantSettings->allow_www,
                        
                        'xtenant.name' => $tenant->name,
                        'xtenant.status' => $tenant->status,
                    ]);

                    // Check session
                    if (!$this->checkSession($request, $tenant->id)) {
                        abort(401);
                    }

                    // Connect to database
                    $this->connectToTenantDatabase($defaultConnection, $database);

                    // Create symbolic link
                    $this->createSymlinkIfNotExists($subdomain);

                    // Change Filesystem's root path
                    $this->setFilesystemRootPath($subdomain);

                    // Set cache prefix to avoid collisions
                    $this->setCachePrefix($subdomain);
                }
            }

            if (is_null($tenant) || is_null($database)) {
                abort(403, '[' . $subdomain . '] doesn\'t exist. You should run `php artisan xtenant:new` to register it.');
            }
        } else {
            abort(403, 'You should run `php artisan xtenant:setup` first to setup xTenant.');
        }

        return $next($request);
    }

    private function getTenantsDatabase($subdomain)
    {
        $dbConnectionType = XTenantHelper::getDatabaseConnectionType();
        
        switch($dbConnectionType) {
            case 'SQLiteConnection':
                return is_file(database_path($databaseName)) ? database_path($databaseName) : null;
                break;
            
            case 'MySqlConnection': case 'PostgresConnection': case 'SqlServerConnection':
                return $databaseName;
                break;
            
            default:
                return null;
                break;
        }
    }

    private function checkSession($request, $tenantId)
    {
        if (!$request->session()->has('tenant_id')) {
            $request->session()->put('tenant_id', $tenantId);
        }

        if ($request->session()->get('tenant_id') != $tenantId) {
            return false;
        }

        return true;
    }

    private function connectToTenantDatabase($defaultConnection, $database)
    {
        DB::purge($defaultConnection);
        config()->set('database.connections.' . $defaultConnection . '.database', $database);
        DB::reconnect();
    }

    private function setFilesystemRootPath($subdomain)
    {
        $localRoot = storage_path('app/' . $subdomain); // storage_path('app')
        $publicRoot = storage_path('app/' . $subdomain . '/public'); // storage_path('app/public')
        $publicUrl = env('APP_URL') . '/storage/' . $subdomain; // env('APP_URL').'/storage'

        config([
            'filesystems.disks.local.root'  => $localRoot,
            'filesystems.disks.public.root' => $publicRoot,
            'filesystems.disks.public.url'  => $publicUrl,
        ]);
    }

    private function setCachePrefix($subdomain)
    {
        $prefix = Str::slug(env('APP_NAME', 'laravel'), '_') . '_' . $subdomain . '_cache';
        config()->set('cache.prefix', $prefix);
    }

    private function createSymlinkIfNotExists($subdomain)
    {
        $link = public_path($subdomain);
        if (!is_link($link)) {
            $target = storage_path('app/' . $subdomain);
            symlink($target, $link);
        }
    }

}
