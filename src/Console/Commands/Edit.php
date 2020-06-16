<?php

namespace FBNKCMaster\xTenant\Console\Commands;

use Artisan;
use Illuminate\Console\Command;

use FBNKCMaster\xTenant\Models\Tenant;

class Edit extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'xtenant:edit
                            {tenant_subdomain? : Tenant name}';

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
        if (\Schema::hasTable('tenants') && \Schema::hasTable('x_tenant_settings')) {
            $subdomain = $this->argument('tenant_subdomain');
            if (is_null($subdomain)) {
                $subdomain = $this->ask('Enter tenant\'s subdomain');
            }
            $subdomain = strtolower(trim($subdomain));
            // Check if it exists
            if (Tenant::where('subdomain', $subdomain)->first()) {

                // Ask confirmation
                $choice = $this->choice('Are you sure you want to edit [' . $subdomain . ']?', ['Yes', 'No'], 1);

                if ($choice == 'Yes') {
                    if ($this->updateSettings($subdomain)) {
                        
                        // Ask to run migrations
                        $this->call('xtenant:migrate', ['tenant_subdomain' => $subdomain]);
                        
                        // Ask to run seeds
                        $this->call('xtenant:seed', ['tenant_subdomain' => $subdomain]);
                        
                        // Ask to create directory for this tenant in storage/app/public
                        // or erase existing content in it
                        $this->call('xtenant:directory', ['tenant_subdomain' => $subdomain]);
                        
                        // Show success message
                        $this->info(' > ' . $subdomain. ' override successfully!');
                        $this->info(' > ' . $subdomain . ' url: http://' . $subdomain . '.[your_domain]');

                    } else {
                        $this->error('ERROR: Could not edit [' . $subdomain . ']. Please check your connection.');
                    }

                } else {
                    $this->info(' > Operation canceled. Nothing has changed. You can still check  [' . $subdomain . '] at:');
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

    private function updateSettings($subdomain)
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