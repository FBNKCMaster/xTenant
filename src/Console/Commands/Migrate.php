<?php

namespace FBNKCMaster\xTenant\Console\Commands;

use Artisan;
use Illuminate\Console\Command;

use FBNKCMaster\xTenant\Models\Tenant;
//use FBNKCMaster\xTenant\Models\XTenantParam;

class Migrate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'xtenant:migrate
                            {tenant_name? : Tenant name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run migrations for specified tenant';

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
        if (\Schema::hasTable('tenants') && \Schema::hasTable('x_tenant_params')) {
            $this->tenantName = $this->argument('tenant_name');
            if (is_null($this->tenantName)) {
                $this->tenantName = $this->ask('Enter tenant\'s subdomain');
            }
            $subdomain = strtolower(trim($this->tenantName));
            // Check if it exists
            if (Tenant::where('subdomain', $subdomain)->first()) {
                
                // Ask confirmation
                $choice = $this->choice('Are you sure you want to run migrations for [' . $this->tenantName . ']?', ['Yes', 'No'], 1);
                
                if ($choice == 'Yes') {
                    // Save default database
                    $defaultDatabase = $this->getDefaultDatabase();
                    
                    // Get tenant's database
                    $database = $this->getTenantDatabase($subdomain);
                    
                    if ($database) {
                        // Connect to the tenant's database
                        $this->connectToTheNewDatabase($database);
                        
                        // run migrations for this database
                        //$migrationType = $this->choice('Migration type?', ['default', 'refresh', 'fresh'], 0);
                        
                        //$migrationsPath = dirname(__FILE__) . '/../../database/migrations';
                        
                        $this->line('Running migrations: ' . base_path('database/migrations'));
                        
                        $this->call('migrate', ['--path' => base_path('database/migrations'), '--realpath' => true, '--env' => \App::environment(), '--force' => true]);
                        
                        $this->line(Artisan::output());
                        
                        // Then reset back default connction
                        $this->resetBackDefaultConnection($defaultDatabase);
                    } else {
                        $this->error('ERROR: Could not connect to this tenant\'s database. Please check your connection.');
                    }
                    
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

    private function getTenantDatabase($subdomain)
    {
        return (Tenant::where('subdomain', $subdomain)->first() ?? null)->database ?? null;
    }

    private function getDefaultDatabase()
    {
        $defaultConnection = \DB::getDefaultConnection();
        return config()->get('database.connections.' . $defaultConnection . '.database');
    }

    private function connectToTheNewDatabase($database)
    {
        $defaultConnection = \DB::getDefaultConnection();
        \DB::purge($defaultConnection);
        config()->set('database.connections.' . $defaultConnection . '.database', $database);
        \DB::reconnect();
    }

    private function resetBackDefaultConnection($defaultDatabase)
    {
        $defaultConnection = \DB::getDefaultConnection();
        \DB::purge($defaultConnection);
        //config()->set('database.connections.' . $defaultConnection . '.database', env('DB_DATABASE'));
        config()->set('database.connections.' . $defaultConnection . '.database', $defaultDatabase);
        \DB::reconnect();
    }
}