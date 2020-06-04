<?php

namespace FBNKCMaster\xTenant\Console\Commands;

use Artisan;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

use FBNKCMaster\xTenant\Models\Tenant;

class Directory extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'xtenant:directory
                            {tenant_name? : Tenant name}
                            {action? : Action}';

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
        if (\Schema::hasTable('tenants') && \Schema::hasTable('x_tenant_params')) {
            $this->tenantName = $this->argument('tenant_name');
            if (is_null($this->tenantName)) {
                $this->tenantName = $this->ask('Enter tenant\'s subdomain');
            }
            $subdomain = strtolower(trim($this->tenantName));
            // Check if it exists
            if (Tenant::where('subdomain', $subdomain)->first()) {

                // Ask confirmation
                $choice = $this->choice('Are you sure you want to create a directory for [' . $this->tenantName . ']?', ['Yes', 'No'], 1);

                if ($choice == 'Yes') {
                    // 
                    $this->createDirectory($subdomain);
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

    private function createDirectory($subdomain)
    {
        $subdomain = strtolower($subdomain);
        // Check if directory with same name exists
        if (is_dir(storage_path('app/' . $subdomain))) {
            $choice = $this->choice('A directory with same name [' . $this->tenantName . '] exits. Do you want to back it up?', ['Yes', 'No'], 1);
            
            if ($choice == 'Yes') {
                rename(storage_path('app/' . $subdomain), storage_path('app/' . $subdomain . '_BAK'));
                $this->line('You can find the old directory here: ' . storage_path('app/' . $subdomain . '_BAK'));
            } else {
                $this->rrmdir(storage_path('app/' . $subdomain));
                $this->line('Old directory completely removed.');
            }
        }

        // Create tenant's directory in storage/app
        $this->line('Creating directory: ' . base_path('storage/app/' . $subdomain));
        Storage::makeDirectory($subdomain, 0777/* , $recursive = false, $force = false */);
    }

    // https://stackoverflow.com/a/3338133
    private function rrmdir($dir) { 
        if (is_dir($dir)) { 
            $objects = scandir($dir); 
            foreach ($objects as $object) { 
                if ($object != "." && $object != "..") { 
                    if (is_dir($dir."/".$object))
                        rrmdir($dir."/".$object);
                    else
                        unlink($dir."/".$object); 
                } 
            }
            rmdir($dir); 
        } 
    }
}