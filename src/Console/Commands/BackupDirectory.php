<?php

namespace FBNKCMaster\xTenant\Console\Commands;

use Artisan;
//use Illuminate\Console\Command;

use FBNKCMaster\xTenant\Models\Tenant;
use FBNKCMaster\xTenant\Helpers\XTenantHelper;

class BackupDirectory extends CommandWeb
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'xtenant:backupdir
                            {tenant_subdomain? : Tenant subdomain}
                            {--path= : The backup directory path}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Backup tenant\'s directory';

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
            $path = $this->option('path');
            if (is_null($subdomain)) {
                $subdomain = $this->ask('Enter tenant\'s subdomain');
            }
            
            $subdomain = strtolower(trim($subdomain));
            
            // Check if it exists
            $tenant = Tenant::where('subdomain', $subdomain)->first();
            if ($tenant) {
                // Ask confirmation
                $choice = $this->choice('Are you sure you want to backup [' . $subdomain . ']\'s directory?', ['Yes', 'No'], 1);

                if ($choice == 'Yes') {
                    $backupPath = XTenantHelper::backupDir($subdomain, false, $path ?? null);
                    if ($backupPath) {
                        $this->info('A backup of [' . $subdomain . ']\'s directory was created here: ' . $backupPath);
                    } else {
                        $this->error('An error occurred. Could not make a backup of [' . $subdomain . ']\'s directory.');
                    }
                } else {
                    $this->info(' > Operation canceled.');
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