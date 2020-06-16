<?php

namespace FBNKCMaster\xTenant\Console\Commands;

use Artisan;
use Illuminate\Console\Command;

use FBNKCMaster\xTenant\Models\XTenantSetting;
use FBNKCMaster\xTenant\Helpers\XTenantHelper;

class Setup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'xtenant:setup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Setup xTenant';

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
        $this->line('Create `xTenant`\'s tables...');
        //dump(\App::environment());
        //dump(\DB::getDatabaseName());
        $bOverride = false;
        // Check if it's already setup
        if (\Schema::hasTable('tenants') && \Schema::hasTable('x_tenant_settings')) {
            
            $settings = XTenantSetting::getSettings();
            
            if ($settings && $settings->super_admin_subdomain) {
                // Ask overriding existing settings
                $choice = $this->choice('Override existing setup? [ superadmin\'s subdomain: ' . $settings->super_admin_subdomain . ' ]', [
                    'No',
                    'Yes',
                ]);
                
                if ($choice == 'Yes') {
                    // Reset tables
                    $this->createTables(true);
                    // Ask for new settings to store
                    $this->storeSettings(true);
                    
                    $bOverride = true;
                    $settings = null;
                }
            } else {
                // Ask for settings to store
                $this->storeSettings();
            }
        
        } else {
            // Create tables
            $this->createTables();
            // Ask for settings to store
            $this->storeSettings();
        }
        
        // Get stored settings
        $settings = $settings ?? XTenantSetting::getSettings();
        
        //dump(' > Stored Setting > ' . $settings->domain);
        if ($settings && $settings->super_admin_subdomain)
        {
            // Show success message and admin url
            $this->info(' >' . ($bOverride ? ' New admin' : ' Admin') . ' url: http://' . $settings->super_admin_subdomain . '.[your_domain]');
            
        } else {
            // Show error message
            $this->info(' X Something went wrong. Please, check your database connection.');
        }
        
    }

    private function createTables($bReset = false)
    {
        // Define migrations' path
        $migrationsPath = dirname(__FILE__) . '/../../../database/migrations';

        // Run migrations
        Artisan::call('migrate' . ($bReset ? ':fresh' : '') , ['--path' => $migrationsPath, '--realpath' => true, '--env' => \App::environment(), '--force' => true]);
        
        // Show output
        $this->line(Artisan::output());
    }

    private function storeSettings($bOverride = false)
    {
        // Ask for super admin subdomain
        $superAdminSubDomain = $this->ask('Enter' . ($bOverride ? ' new' : '') . ' SuperAdmin subdomain');
        if (empty(trim($superAdminSubDomain))) {
            $this->error('Sorry, the SuperAdmin subdomain cannot be empty. Repeat again.');
            return;
        }
        // Ask for super admin email
        $email = $this->ask('Enter SuperAdmin email (this will be your login)');
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->error('Sorry, this is an invalid email format. Repeat again.');
            return;
        }

        // Ask for super admin password
        $password = $this->secret('Enter SuperAdmin password (must be at least 8 characters long)');
        if (strlen($password) < 8) {
            $this->error('Sorry, the password is too short. It must be at least 8 characters.');
            return;
        }

        // Ask for password confirmation
        $password_confirm = $this->secret('Confirm password');
        if ($password !== $password_confirm) {
            $this->error('Sorry, the passwords you entered do not match. Repeat again.');
            return;
        }

        // Allow "www" even with subdomain ?
        $allowWww = $this->choice('Allow "www"?', ['Yes', 'No'], 1);
        
        // Store 
        $bCreated = XTenantSetting::truncate()->create([
            'super_admin_subdomain' => $superAdminSubDomain,
            'email' => $email,
            'password' => $password, // Don't panic! Encryption is done inside the model ;-)
            'allow_www' => $allowWww == 'Yes',
        ]);

        if ($bCreated) {
            // Create directory
            XTenantHelper::createDirectory($superAdminSubDomain, 'fresh', $this);
            // Create symbolic link
            XTenantHelper::createSymlink($superAdminSubDomain, $this);
        }
        
    }

}