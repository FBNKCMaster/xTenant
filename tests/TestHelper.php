<?php

namespace Tests;

use Tests\TestCase;
//use Orchestra\Testbench\TestCase;
//use Illuminate\Foundation\Testing\RefreshDatabase;

use FBNKCMaster\xTenant\Models\Tenant;

class TestHelper extends TestCase
{
    // Don't work with sqlite :memory: for some raison
    // so I created resetDatabase() method to reset
    // everything and to mimic RefreshDatabase
    // but you still can use it with mysql
    //use RefreshDatabase;

    private $defaultConnection = null;
    private $defaultDatabase = null;
    public $superAdminSubdomain = null;
    public $newSuperAdminSubdomain = null;
    private $tenantSubdomain = null;

    /**
     * Define environment setup.
     *
     * @setting  \Illuminate\Foundation\Application  $app
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        $this->defaultConnection = \DB::getDefaultConnection();
        if ($this->defaultConnection == 'sqlite') {
            // Setup default database to use sqlite
            $this->defaultDatabase = database_path('database.sqlite');
            $app['config']->set('database.default', 'testbench');
            $app['config']->set('database.connections.testbench', [
                'driver'   => 'sqlite',
                'database' => $this->defaultDatabase,
                'prefix'   => '',
            ]);
    
            $this->refreshSQLiteDatabase();
        } else if ($this->defaultConnection == 'mysql') {
            $this->defaultDatabase = 'laratest';
        }
    }

    /**
     * Get package providers.  At a minimum this is the package being tested, but also
     * would include packages upon which our package depends, e.g. Cartalyst/Sentry
     * In a normal app environment these would be added to the 'providers' array in
     * the config/app.php file.
     *
     * @setting  \Illuminate\Foundation\Application  $app
     *
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [
            'FBNKCMaster\xTenant\Providers\ServiceProvider',
        ];
    }

    protected function tearDown(): void
    {
        $this->resetDatabase();
        $this->resetDirectoriesAndSymbolicLinks();
    }

    private function resetDatabase()
    {
        // Drop tenant database if it was created
        if ($this->defaultConnection == 'sqlite') {
            $this->removeSQLiteDatabaseFile();
        } else if ($this->defaultConnection == 'mysql') {
            try {
                $sql = "DROP DATABASE `$this->tenantDatabase`";
                \DB::statement($sql);
            } catch (\Exception $e) {
                //die($this->tenantDatabase . ' does not exist');
            }
        }

        // And drop tables create on the main database
        try {
            $sql = "DROP TABLE migrations, tenants, x_tenant_settings";
            \DB::statement($sql);
        } catch (\Exception $e) {
            //die('Could not connect to the main database.  Please check your configuration. error:' . $e );
        }

        // Delete any created backup
        $batabaseBackups = glob(database_path($this->tenantSubdomain) . '*');
        foreach($batabaseBackups as $file) {
            if(is_file($file)) {
                @unlink($file); // delete file
            }
        }
    }

    private function resetDirectoriesAndSymbolicLinks()
    {
        if (!is_null($this->superAdminSubdomain) && !empty($this->superAdminSubdomain)) {
            $dirSuperAdminSubdomain = storage_path('app/' . $this->superAdminSubdomain);
            $this->removeDir($dirSuperAdminSubdomain);
            @unlink(public_path($this->superAdminSubdomain));
        }

        if (!is_null($this->newSuperAdminSubdomain) && !empty($this->newSuperAdminSubdomain)) {
            $dirNewSuperAdminSubdomain = storage_path('app/' . $this->newSuperAdminSubdomain);
            $this->removeDir($dirNewSuperAdminSubdomain);
            @unlink(public_path($this->newSuperAdminSubdomain));
        }
        
        if (!is_null($this->tenantSubdomain) && !empty($this->tenantSubdomain)) {
            $dirTenantSubdomain = storage_path('app/' . $this->tenantSubdomain);
            $this->removeDir($dirTenantSubdomain);
            @unlink(public_path($this->tenantSubdomain));

            $directoryBackups = glob(storage_path('app/' . $this->tenantSubdomain . '_*_Backup'));
            foreach($directoryBackups as $dir) {
                if(is_dir($dir)) {
                    $this->removeDir($dir);
                }
            }
        }
    }

    private function refreshSQLiteDatabase()
    {
        // For some reason RefreshDatabase doesn't work well with sqlite in some tests
        // so as a workaround let's do it this way
        $this->removeSQLiteDatabaseFile();
        touch($this->defaultDatabase);
    }

    private function removeSQLiteDatabaseFile()
    {
        if (is_file($this->defaultDatabase)) {
            @unlink($this->defaultDatabase);
        }
    }

    private function removeDir($dir)
    {
        if (is_dir($dir)) { 
            $objects = scandir($dir); 
            foreach ($objects as $object) { 
                if ($object != '.' && $object != '..') { 
                    if (is_dir($dir . '/' . $object)) {
                        $this->removeDir($dir . '/' . $object);
                    } else {
                        @unlink($dir . '/' . $object); 
                    }
                } 
            }
            @rmdir($dir);
        }
    }

    private function resetBackDefaultConnection()
    {
        $defaultConnection = \DB::getDefaultConnection();
        \DB::purge($defaultConnection);
        config()->set('database.connections.' . $defaultConnection . '.database', $this->defaultDatabase);
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

    public function setupXTenant($superAdminSubdomain = 'superadmin', $email = 'superadmin@xtenant.test', $password = 'secretlongenough')
    {
        $this->artisan('xtenant:setup')
                ->expectsQuestion('Enter SuperAdmin subdomain', $superAdminSubdomain)
                ->expectsQuestion('Enter SuperAdmin email (this will be your login)', $email)
                ->expectsQuestion('Enter SuperAdmin password (must be at least 8 characters long)', $password)
                ->expectsQuestion('Confirm password', $password)
                ->expectsQuestion('Allow "www"?', 'Yes')
                ->expectsOutput(' > Admin url: http://' . $superAdminSubdomain . '.[your_domain]')
                ->assertExitCode(0);

        $this->superAdminSubdomain = $superAdminSubdomain;
    }

    public function createTenant($subdomain = 'demo', $name = 'Demo Tenant', $description = 'Description of the demo tenant')
    {
        $this->tenantSubdomain = $subdomain;
        // Delete any existing directory
        $dir = storage_path('app/' . $this->tenantSubdomain);
        $this->removeDir($dir);

        // Create demo tenant
        $this->artisan('xtenant:new')
                ->expectsQuestion('Enter subdomain', $subdomain)
                ->expectsQuestion('Enter name', $name)
                ->expectsQuestion('Enter description', $description)
                ->expectsQuestion('Do you want to run migrations for [' . $subdomain . ']?', 'Yes')
                ->expectsQuestion('Migration type?', 'default')
                ->expectsQuestion('Do you want to run seeds for [' . $subdomain . ']?', 'Yes')
                ->expectsQuestion('Seeds type?', 'Default')
                ->expectsQuestion('Do you want to create a directory for [' . $subdomain . ']?', 'Yes')
                ->expectsOutput(' > ' . $subdomain . ' created successfully!')
                ->expectsOutput(' > ' . $subdomain . ' url: http://' . $subdomain . '.[your_domain]')
                ->assertExitCode(0);
        $tenant = Tenant::where('subdomain', $subdomain)->first();
        $this->assertEquals($subdomain, $tenant->subdomain);
        $this->assertTrue(is_dir(storage_path('app/' . $tenant->subdomain)));

        // Connect to tenant's database
        $this->tenantDatabase = $this->getTenantDatabase($subdomain);
        $this->connectToTenantDatabase($this->tenantDatabase);
        $this->assertTrue(\Schema::hasTable('migrations'));

        // Assert symbolic link for this tenant was created
        $link = public_path($subdomain);
        $this->assertTrue(is_link($link));

        $this->resetBackDefaultConnection();
    }
    
}