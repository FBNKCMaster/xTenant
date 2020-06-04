<?php

namespace FBNKCMaster\xTenant\Console\Commands;

use Artisan;
use Illuminate\Console\Command;

use FBNKCMaster\xTenant\Models\Tenant;
use FBNKCMaster\xTenant\Models\XTenantParam;

class Destroy extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'xtenant:destroy
                            {tenant_name? : Tenant name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Destroy the tenant';

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
                $choice = $this->choice('Are you sure you want to completely destroy [' . $this->tenantName . ']?', ['Yes', 'No'], 1);

                if ($choice == 'Yes') {
                    $this->removeDatabase($subdomain);
                    $this->removeDirectory($subdomain);
                    $this->removeTenant($subdomain);
                    $this->info(' > ' . $this->tenantName . ' destroyed successfully!');
                } else {
                    $this->info(' > Operation canceled. You can still check  [' . $this->tenantName . '] at:');
                    $this->info(' > ' . $subdomain . ' url: http://' . $subdomain . '.[your_domain]');
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

    private function removeDatabase($subdomain)
    {
        $databaseName = $subdomain . '.db';
        
        $choice = $this->choice('Delete database?', ['Yes', 'No', 'Backup'], 2);

        switch ($choice) {
            case 'Yes':
                try {
                    // https://laravel-tricks.com/tricks/drop-database
                    // Schema::getConnection()->getDoctrineSchemaManager()->dropDatabase("`{$databaseName}`");
                    
                    // try to drop database
                    //if (\DB::statement('DROP DATABASE IF EXISTS :db_name', ['db_name' => $databaseName])) {
                    $sqlQuery = "'DROP DATABASE IF EXISTS '$databaseName'";
                    if (\DB::statement($sqlQuery)) {
                        return;
                    }
        
                } catch (\PDOException $e) {
                    // it's sqlite connection
                    if ($e->getCode() == 'HY000') {
        
                        $databaseName = database_path($databaseName);
        
                        if (@unlink($databaseName)) {
                            return;
                        }
                    }
                }
        
                $this->warn(' ! Could not drop ' . $subdomain . '\'s database.');
                break;

            case 'Backup':
                if (BackupDatabase::run($subdomain, 'sqlite')) {
                    $this->line($subdomain . '\'s database backed up!');
                } else {
                    $this->warn(' ! Could not backup ' . $subdomain . '\'s database.');
                }
                break;
        }
    }

    private function removeDirectory($subdomain)
    {
        $choice = $this->choice('Delete directory?', ['Yes', 'No', 'Backup'], 2);

        switch ($choice) {
            case 'Yes':
                $this->line('Deleting ' . $subdomain . '\'s directory: ' . base_path('storage/app/' . $subdomain));
                $this->rrmdir(storage_path('app/' . $subdomain));

                if (is_dir(storage_path('app/' . $subdomain))) {
                    $this->warn(' ! Could not delete ' . $tenant['subdomain'] . '\'s directory.');
                }
                break;

            case 'Backup':
                $this->line('Creating backup for ' . $subdomain . '\'s directory: ' . base_path('storage/app/' . $subdomain));
                rename(storage_path('app/' . $subdomain), storage_path('app/' . $subdomain . '_BAK'));
                if (!storage_path('app/' . $subdomain . '_BAK')) {
                    $this->warn(' ! Could not backup ' . $tenant['subdomain'] . '\'s directory.');
                }
                break;
        }
    }

    private function removeTenant($subdomain)
    {
        return Tenant::where('subdomain', $subdomain)->delete();
    }

}