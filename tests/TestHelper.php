<?php

namespace Tests;

use Tests\TestCase;
//use Orchestra\Testbench\TestCase;
//use Illuminate\Foundation\Testing\RefreshDatabase;

use FBNKCMaster\xTenant\Models\Tenant;

class TestHelper extends TestCase
{
    
    //use RefreshDatabase;

    private const DEFAULT_DATABASE = 'database.sqlite';

    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        // Setup default database to use sqlite
        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver'   => 'sqlite',
            'database' => self::DEFAULT_DATABASE,
            'prefix'   => '',
        ]);

        $this->refreshDatabase();
    }

    private function refreshDatabase()
    {
        // For some reason RefreshDatabase doesn't work well with sqlite in some tests
        // so as a workaround let's do it this way
        if (is_file(self::DEFAULT_DATABASE)) {
            @unlink(self::DEFAULT_DATABASE);
        }
        touch(self::DEFAULT_DATABASE);
    }

    /**
     * Get package providers.  At a minimum this is the package being tested, but also
     * would include packages upon which our package depends, e.g. Cartalyst/Sentry
     * In a normal app environment these would be added to the 'providers' array in
     * the config/app.php file.
     *
     * @param  \Illuminate\Foundation\Application  $app
     *
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [
            'FBNKCMaster\xTenant\Providers\ServiceProvider',
        ];
    }

    private function resetBackDefaultConnection()
    {
        $defaultConnection = \DB::getDefaultConnection();
        \DB::purge($defaultConnection);
        config()->set('database.connections.' . $defaultConnection . '.database', self::DEFAULT_DATABASE);
        \DB::reconnect();
    }

    public function getTenantDatabase($subdomain)
    {
        return (Tenant::where('subdomain', $subdomain)->first() ?? null)->database ?? null;
    }
    
    private function connectToTenantDatabase($database)
    {
        $defaultConnection = \DB::getDefaultConnection();
        \DB::purge($defaultConnection);
        config()->set('database.connections.' . $defaultConnection . '.database', $database);
        \DB::reconnect();
    }

    public function setupXTenant($superAdminSubdomain = 'superadmin')
    {
        $this->artisan('xtenant:setup')
                ->expectsQuestion('Enter SuperAdmin subdomain', $superAdminSubdomain)
                ->expectsQuestion('Allow "www"?', 'Yes')
                ->expectsOutput(' > Admin url: http://' . $superAdminSubdomain . '.[your_domain]')
                ->assertExitCode(0);
    }

    public function createTenant($subdomain = 'demo', $name = 'Demo Tenant', $description = 'Description of the demo tenant')
    {
        // Delete any existing directory
        $existingDirectory = storage_path('app/' . $subdomain);
        if (is_dir($existingDirectory)) {
            rmdir($existingDirectory);
        }

        // Create demo tenant
        $this->artisan('xtenant:new')
                ->expectsQuestion('Enter subdomain', $subdomain)
                ->expectsQuestion('Enter name', $name)
                ->expectsQuestion('Enter description', $description)
                ->expectsQuestion('Are you sure you want to run migrations for [' . $subdomain . ']?', 'Yes')
                ->expectsQuestion('Are you sure you want to run seeds for [' . $subdomain . ']?', 'Yes')
                ->expectsQuestion('Seeds type?', 'Default')
                ->expectsQuestion('Are you sure you want to create a directory for [' . $subdomain . ']?', 'Yes')
                ->expectsOutput(' > ' . $subdomain . ' created successfully!')
                ->expectsOutput(' > ' . $subdomain . ' url: http://' . $subdomain . '.[your_domain]')
                ->assertExitCode(0);
        
        $tenant = Tenant::where('subdomain', $subdomain)->first();
        $this->assertEquals($subdomain, $tenant->subdomain);
        $this->assertTrue(is_dir(storage_path('app/' . $tenant->subdomain)));

        // Connect to tenant's database
        $database = $this->getTenantDatabase($subdomain);
        $this->connectToTenantDatabase($database);
        $this->assertTrue(\Schema::hasTable('migrations'));

        // Assert symbolic link for this tenant was created
        $link = public_path($subdomain);
        $this->assertTrue(is_link($link));

        $this->resetBackDefaultConnection();
    }
    
}