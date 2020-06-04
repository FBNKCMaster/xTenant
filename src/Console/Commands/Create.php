<?php

namespace FBNKCMaster\xTenant\Console\Commands;

use Artisan;
use Illuminate\Console\Command;

use FBNKCMaster\xTenant\Models\Tenant;
use FBNKCMaster\xTenant\Models\XTenantParam;

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
        // xTenant's tables creation

        // Show message to the console
        $this->line('Create new tenant...');

        // Check if it's already setup
        if (\Schema::hasTable('tenants') && \Schema::hasTable('x_tenant_params')) {

            // Ask for tenant's params
            $tenantParams = $this->askForParams();
            
            if (isset($tenantParams['database']) && isset($tenantParams['subdomain']) && isset($tenantParams['name']) && isset($tenantParams['description'])) {
                // Tenant already exists
                $this->warn('This tenant [' . $tenantParams['subdomain'] . '] already exists.');
                // Choose what to do
                $choice = $this->choice('Do you want to override/destory it?', ['Override', 'Destroy', 'Cancel'], 2);
                
                switch ($choice) {
                    case 'Override':
                        $this->call('xtenant:edit', ['tenant_name' => $tenantParams['subdomain']]);
                        //$this->callSilent('xtenant:edit', ['tenant_name' => $tenant['subdomain']]);
                        $this->line(Artisan::output());
                        break;
                        
                    case 'Destroy':
                        $this->call('xtenant:destroy', ['tenant_name' => $tenantParams['subdomain']]);
                        //$this->callSilent('xtenant:destroy', ['tenant_name' => $tenant['subdomain']]);
                        $this->line(Artisan::output());
                        // Now let's create it
                        $this->createTenant($tenantParams);
                        break;
                    
                    default:
                        $this->info(' > ' . $tenantParams['subdomain'] . ' url: http://' . $tenantParams['subdomain'] . '.[your_domain]');
                        break;
                }
            } else if (isset($tenantParams['subdomain']) && isset($tenantParams['name']) && isset($tenantParams['description'])) {
                // Let's create this tenant
                $this->createTenant($tenantParams);
            }

        } else {
            // Show error message
            $this->error('ERROR: xTenant not set up yet. You should run `artisan xtenant:setup` first');
        }   
    }

    private function askForParams()
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

    private function createTenant($tenantParams)
    {
        $database = $this->createDatabase($tenantParams['subdomain']);
        
        if ($database) {
            $newTenant = Tenant::create([
                'subdomain' => $tenantParams['subdomain'],
                'database' => $database,
                'name' => trim($tenantParams['name']),
                'description' => trim($tenantParams['description']),
            ]);

            if ($newTenant) {
                // Ask to run migrations
                //$this->runMigrations($newTenant->database);
                $this->call('xtenant:migrate', ['tenant_name' => $newTenant->subdomain]);
                
                // Ask to run seeds
                //$this->runSeeds();
                $this->call('xtenant:seed', ['tenant_name' => $newTenant->subdomain]);

                // Ask to create directory for this tenant in storage/app/public
                //$this->createDirectory($newTenant->subdomain);
                $this->call('xtenant:directory', ['tenant_name' => $newTenant->subdomain]);

                // Create symbolic link
                $this->createSymlink($newTenant->subdomain);
                
                // Show success message and url info
                $this->info(' > ' . $newTenant->subdomain . ' created successfully!');
                $this->info(' > ' . $newTenant->subdomain . ' url: http://' . $newTenant->subdomain . '.[your_domain]');
                
            }

        }

        if (!$database || !$newTenant) {
            $this->error('ERROR: Could not create this tenant. Please check your database connection.');
        }
    }

    private function createDatabase($subdomain)
    {
        $databaseName = $subdomain . '.db';
        try {
            // Try to create database for MySql/PostgreSQL connection
            // Tried binding but didn't work
            //  $sqlQuery = 'CREATE DATABASE IF NOT EXISTS :db_name DEFAULT CHARACTER SET utf8mb4';
            //  \DB::statement($sqlQuery, ['db_name' => $databaseName]);
            // So did it this way
            $sqlQuery = 'CREATE DATABASE IF NOT EXISTS `' . $databaseName . '` DEFAULT CHARACTER SET utf8mb4';
            if (\DB::statement($sqlQuery)) {
                
                return $databaseName;
            }
        } catch (\PDOException $e) {
            // Otherwise it's SQLite connection
            if ($e->getCode() == 'HY000') {

                $databaseName = database_path($databaseName);

                if (touch($databaseName)) {

                    return $databaseName;
                }
            }

            return false;
        }
    }

    private function createSymlink($subdomain)
    {
        $link = public_path($subdomain);
        $target = storage_path('app/' . $subdomain);
        @symlink($target, $link);
    }

}