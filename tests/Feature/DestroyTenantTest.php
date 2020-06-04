<?php

namespace Tests\Feature;

//use Tests\TestCase;
//use Orchestra\Testbench\TestCase;
//use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestHelper as TestCase;

use FBNKCMaster\xTenant\Models\Tenant;

class DestroyTenantTest extends TestCase
{

    public function test_can_destroy_tenant_with_artisan_new_console_command()
    {
        $subdomain = 'demo';
        $this->assertTrue(true);

        // First run setup
        $this->setupXTenant();
        
        // create demo tenant
        $this->createTenant($subdomain);
        
        // Then destroy it
        //  1 - First case: enter subdomain directly as a parameter
        //      and cancel when asked to confirm destruction
        $this->artisan('xtenant:destroy ' . $subdomain)
                ->expectsQuestion('Are you sure you want to completely destroy [' . $subdomain . ']?', 'No')
                ->expectsOutput(' > Operation canceled. You can still check  [' . $subdomain . '] at:')
                ->expectsOutput(' > ' . $subdomain . ' url: http://' . $subdomain . '.[your_domain]')
                ->assertExitCode(0);
        //  2 - Second case: don't specify subdomain
        //      and confirm destruction
        $this->artisan('xtenant:destroy')
                ->expectsQuestion('Enter tenant\'s subdomain', $subdomain)
                ->expectsQuestion('Are you sure you want to completely destroy [' . $subdomain . ']?', 'Yes')
                ->expectsQuestion('Delete database?', 'Backup')
                ->expectsQuestion('Delete directory?', 'Backup')
                ->expectsOutput(' > ' . $subdomain . ' destroyed successfully!')
                ->assertExitCode(0);

        // => Assert tenant was completely destroyed
        $tenant = Tenant::where('subdomain', $subdomain)->first();
        $this->assertNull($tenant);
        $this->assertTrue(!is_dir(storage_path('app/' . $subdomain)));
        $this->assertTrue(is_dir(storage_path('app/' . $subdomain . '_BAK')));
        // assert database doesn't exist

        // Connect to tenant's database
        $database = $this->getTenantDatabase($subdomain);
        $this->assertNull($database);
    }
}
