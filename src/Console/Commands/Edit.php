<?php

namespace FBNKCMaster\xTenant\Console\Commands;

use Artisan;
use Illuminate\Console\Command;

use FBNKCMaster\xTenant\Models\Tenant;
use FBNKCMaster\xTenant\Models\XTenantParam;

class Edit extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'xtenant:edit
                            {tenant_name? : Tenant name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Edit tenant\'s infos';

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
                $choice = $this->choice('Are you sure you want to edit [' . $this->tenantName . ']?', ['Yes', 'No'], 1);

                if ($choice == 'Yes') {
                    if ($this->updateParams($subdomain)) {
                        
                        // Ask to run migrations
                        $this->call('xtenant:migrate', ['tenant_name' => $subdomain]);
                        
                        // Ask to run seeds
                        $this->call('xtenant:seed', ['tenant_name' => $subdomain]);
                        
                        // Ask to create directory for this tenant in storage/app/public
                        // or erase existing content in it
                        //$this->createDirectory($subdomain, true);
                        $this->call('xtenant:directory', ['tenant_name' => $subdomain]);
                        
                        // Show success message
                        $this->info(' > ' . $subdomain. ' override successfully!');
                        $this->info(' > ' . $subdomain . ' url: http://' . $subdomain . '.[your_domain]');

                    } else {
                        $this->error('ERROR: Could not edit [' . $this->tenantName . ']. Please check your connection.');
                    }

                } else {
                    $this->info(' > Operation canceled. Nothing has changed. You can still check  [' . $this->tenantName . '] at:');
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

    private function updateParams($subdomain)
    {
        // Ask for name
        $name = $this->ask('Enter name');
        // Ask for description
        $description = $this->ask('Enter description');
        
        return Tenant::where('subdomain', $subdomain)->update([
            'name' => trim($name),
            'description' => trim($description),
        ]);
    }

}