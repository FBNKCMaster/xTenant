<?php

namespace FBNKCMaster\xTenant\Console\Commands;

use Artisan;
use Illuminate\Console\Command;

use FBNKCMaster\xTenant\Models\Tenant;
use FBNKCMaster\xTenant\Helpers\XTenantHelper;

class Create extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'xtenant:new';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create new tenant';

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
        // Show message to the console
        $this->line('Create new tenant...');

        // Check if it's already setup
        if (\Schema::hasTable('tenants') && \Schema::hasTable('x_tenant_settings')) {

            // Ask for tenant's settings
            $tenantSettings = $this->askForSettings();
            
            if (isset($tenantSettings['database']) && isset($tenantSettings['subdomain']) && isset($tenantSettings['name']) && isset($tenantSettings['description'])) {
                // Tenant already exists
                $this->warn('This tenant [' . $tenantSettings['subdomain'] . '] already exists.');
                // Choose what to do
                $choice = $this->choice('Do you want to override/destory it?', ['Override', 'Destroy', 'Cancel'], 2);
                
                switch ($choice) {
                    case 'Override':
                        $this->call('xtenant:edit', ['tenant_subdomain' => $tenantSettings['subdomain']]);
                        $this->line(Artisan::output());
                        break;
                        
                    case 'Destroy':
                        $this->call('xtenant:destroy', ['tenant_subdomain' => $tenantSettings['subdomain']]);
                        $this->line(Artisan::output());
                        // Now let's create it
                        $this->createTenant($tenantSettings);
                        break;
                    
                    default:
                        $this->info(' > ' . $tenantSettings['subdomain'] . ' url: http://' . $tenantSettings['subdomain'] . '.[your_domain]');
                        break;
                }
            } else if (isset($tenantSettings['subdomain']) && isset($tenantSettings['name']) && isset($tenantSettings['description'])) {
                // Let's create this tenant
                $this->createTenant($tenantSettings);
            }

        } else {
            // Show error message
            $this->error('ERROR: xTenant not set up yet. You should run `artisan xtenant:setup` first');
        }   
    }

    private function askForSettings()
    {
        // Ask for subdomain
        $subdomain = $this->ask('Enter subdomain');
        $subdomain = strtolower(trim($subdomain));

        // Check if tenant with same subdomain doesn't already exists
        $tenant = Tenant::where('subdomain', $subdomain)->first();
        if (!$tenant) {
            // Ask for name
            $name = $this->ask('Enter name');
            // Ask for description
            $description = $this->ask('Enter description');

            return [
                'subdomain' => $subdomain,
                'name' => trim($name),
                'description' => trim($description),
            ];
        } else {
            return [
                'database' => $tenant->database,
                'subdomain' => $tenant->subdomain,
                'name' => $tenant->name,
                'description' => $tenant->description
            ];
        }
    }

    private function createTenant($tenantSettings)
    {
        // Create Database
        $database = XTenantHelper::createDatabase($tenantSettings['subdomain']);
        
        if ($database) {
            $newTenant = Tenant::create([
                'subdomain' => $tenantSettings['subdomain'],
                'database' => $database,
                'name' => trim($tenantSettings['name']),
                'description' => trim($tenantSettings['description']),
            ]);

            if ($newTenant) {
                // Ask to run migrations
                $this->call('xtenant:migrate', ['tenant_subdomain' => $newTenant->subdomain]);
                
                // Ask to run seeds
                $this->call('xtenant:seed', ['tenant_subdomain' => $newTenant->subdomain]);

                // Ask to create directory for this tenant in storage/app/public
                $this->call('xtenant:directory', ['tenant_subdomain' => $newTenant->subdomain]);

                // Create symbolic link
                XTenantHelper::createSymlink($newTenant->subdomain, $this);
                
                // Show success message and url info
                $this->info(' > ' . $newTenant->subdomain . ' created successfully!');
                $this->info(' > ' . $newTenant->subdomain . ' url: http://' . $newTenant->subdomain . '.[your_domain]');
                
            }

        }

        if (!$database || !$newTenant) {
            $this->error('ERROR: Could not create this tenant. Please check your database connection.');
        }
    }

}