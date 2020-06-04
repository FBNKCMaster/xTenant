<?php

namespace FBNKCMaster\xTenant\Console\Commands;

use Artisan;
use Illuminate\Console\Command;

use FBNKCMaster\xTenant\Models\XTenantParam;

class Setup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'xtenant:setup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Setup xTenant';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        // xTenant's tables creation

        // Show message to the console
        $this->line('Create `xTenant`\'s tables...');
        //dump(\App::environment());
        //dump(\DB::getDatabaseName());
        $bOverride = false;
        // Check if it's already setup
        if (\Schema::hasTable('tenants') && \Schema::hasTable('x_tenant_params')) {
            
            $params = $this->getStoredParams();
            
            if ($params && $params->super_admin_subdomain) {
                // Ask overriding existing params
                $choice = $this->choice('Override existing setup? [ superadmin\'s subdomain: ' . $params->super_admin_subdomain . ' ]', [
                    'No',
                    'Yes',
                ]);
                
                if ($choice == 'Yes') {
                    // Reset tables
                    $this->createTables(true);
                    // Ask for new params to store
                    $this->storeParams(true);
                    
                    $bOverride = true;
                    $params = null;
                }
            } else {
                // Ask for params to store
                $this->storeParams();
            }
        
        } else {
            // Create tables
            $this->createTables();
            // Ask for params to store
            $this->storeParams();
        }
        
        // Get stored params
        $params = $params ?? $this->getStoredParams();
        
        //dump(' > Stored Param > ' . $params->domain);
        if ($params && $params->super_admin_subdomain)
        {
            // Show success message and admin url
            $this->info(' >' . ($bOverride ? ' New admin' : ' Admin') . ' url: http://' . $params->super_admin_subdomain . '.[your_domain]');
            
        } else {
            // Show error message
            $this->info(' X Something went wrong. Please, check your database connection.');
        }
        
    }

    private function createTables($bReset = false)
    {
        // Define migrations' path
        $migrationsPath = dirname(__FILE__) . '/../../../database/migrations';

        // Run migrations
        Artisan::call('migrate' . ($bReset ? ':fresh' : '') , ['--path' => $migrationsPath, '--realpath' => true, '--env' => \App::environment(), '--force' => true]);
        
        // Show output
        $this->line(Artisan::output());
    }

    private function storeParams($bOverride = false)
    {
        // Ask for super admin subdomain
        $superAdminSubDomain = $this->ask('Enter' . ($bOverride ? ' new' : '') . ' SuperAdmin subdomain');
        // Allow "www" even with subdomain ?
        $allowWww = $this->choice('Allow "www"?', ['Yes', 'No'], 1);
        
        XTenantParam::create([
            'super_admin_subdomain' => $superAdminSubDomain,
            'allow_www' => $allowWww == 'Yes',
        ]);
    }

    private function getStoredParams()
    {
        return XTenantParam::first();
    }

}