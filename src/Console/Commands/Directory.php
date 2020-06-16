<?php

namespace FBNKCMaster\xTenant\Console\Commands;

use Artisan;
use Illuminate\Console\Command;

use FBNKCMaster\xTenant\Models\Tenant;
use FBNKCMaster\xTenant\Helpers\XTenantHelper;

class Directory extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'xtenant:directory
                            {tenant_subdomain? : Tenant subdomain}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create directory for specified tenant';

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
            $this->tenantName = $this->argument('tenant_subdomain');
            if (is_null($this->tenantName)) {
                $this->tenantName = $this->ask('Enter tenant\'s subdomain');
            }
            $subdomain = strtolower(trim($this->tenantName));
            // Check if it exists
            if (Tenant::where('subdomain', $subdomain)->first()) {

                // Ask confirmation
                $choice = $this->choice('Do you want to create a directory for [' . $this->tenantName . ']?', ['Yes', 'No'], 1);

                if ($choice == 'Yes') {
                    // Create directory, ask to backup if already exists
                    XTenantHelper::createDirectory($subdomain, 'ask', $this);
                }

            } else {
                // Show message
                $this->warn(' ! This tenant [' . $this->tenantName . '] doesn\'t exist. Please make sure that you have entered it correctly.');
            }

        } else {
            // Show error message
            $this->error('ERROR: xTenant not set up yet. You should run `artisan xtenant:setup` first');
        }
    }

}