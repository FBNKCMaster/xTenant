<?php

namespace FBNKCMaster\xTenant\Console\Commands;

use Artisan;
//use Illuminate\Console\Command;

use FBNKCMaster\xTenant\Models\Tenant;
use FBNKCMaster\xTenant\Helpers\XTenantHelper;

class Destroy extends CommandWeb
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'xtenant:destroy
                            {tenant_subdomain? : Tenant subdomain}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Destroy the specified tenant';

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
        // Check if it's already setup
        if (\Schema::hasTable('tenants') && \Schema::hasTable('x_tenant_settings')) {
            $subdomain = $this->argument('tenant_subdomain');
            if (is_null($subdomain)) {
                $subdomain = $this->ask('Enter tenant\'s subdomain');
            }
            
            $subdomain = strtolower(trim($subdomain));
            
            // Check if it exists
            $tenant = Tenant::where('subdomain', $subdomain)->first();
            if ($tenant) {
                // Ask confirmation
                $choice = $this->choice('Are you sure you want to completely destroy [' . $subdomain . ']?', ['Yes', 'No'], 1);

                if ($choice == 'Yes') {
                    XTenantHelper::destroyTenant($tenant, 'ask', $this);
                    $this->info(' > ' . $subdomain . ' destroyed successfully!');
                } else {
                    $this->info(' > Operation canceled. You can still check  [' . $subdomain . '] at:');
                    $this->info(' > ' . $subdomain . ' url: http://' . $subdomain . '.[your_domain]');
                }
            } else {
                // Show message
                $this->warn(' ! This tenant [' . $subdomain . '] doesn\'t exist. Please make sure that you have entered it correctly.');
            }

        } else {
            // Show error message
            $this->error('ERROR: xTenant not set up yet. You should run `artisan xtenant:setup` first');
        }
    }

}