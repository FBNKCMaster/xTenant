<?php

namespace Tests\Feature;

//use Tests\TestCase;
//use Orchestra\Testbench\TestCase;
//use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestHelper as TestCase;

use FBNKCMaster\xTenant\Models\Tenant;

class CreateTenantTest extends TestCase
{

    public function test_can_create_new_tenant_with_artisan_new_console_command()
    {
        // First run setup
        $this->setupXTenant();
        
        // create demo tenant
        $this->createTenant();
    }

    public function test_if_tenant_already_exits_ask_to_edit_or_delete()
    {
        // First run setup
        $this->setupXTenant();
        
        // create demo tenant
        $this->createTenant();

        // try to create another tenant with same subdomain
        // choose: [ cancel => Do nothing and exit ]
        $subdomain = 'demo';
        $name = 'Another Demo Tenant To Cancel';
        $description = 'Description of the other demo tenant to cancel';
        $this->artisan('xtenant:new')
                ->expectsQuestion('Enter subdomain', $subdomain)
                ->expectsQuestion('Do you want to override/destory it?', 'Cancel')
                ->expectsOutput('This tenant [' . $subdomain . '] already exists.')
                ->expectsOutput(' > ' . $subdomain . ' url: http://' . $subdomain . '.[your_domain]')
                ->assertExitCode(0);
        
        // => Assert nothing was changed
        $tenant = Tenant::where('subdomain', $subdomain)->first();
        $this->assertEquals($subdomain, $tenant->subdomain);
        $this->assertTrue(is_dir(storage_path('app/' . $tenant->subdomain)));
 
        // try to create another tenant with same subdomain
        // choose: [ edit => Override existing one ]
        $subdomain = 'demo';
        $name = 'Another Demo Tenant To Override';
        $description = 'Description of the other demo tenant to override';
        $this->artisan('xtenant:new')
                ->expectsQuestion('Enter subdomain', $subdomain)
                ->expectsOutput('This tenant [' . $subdomain . '] already exists.')
                ->expectsQuestion('Do you want to override/destory it?', 'Override')
                ->expectsQuestion('Are you sure you want to edit [' . $subdomain . ']?', 'Yes')
                ->expectsQuestion('Enter name', $name)
                ->expectsQuestion('Enter description', $description)
                ->expectsQuestion('Are you sure you want to run migrations for [' . $subdomain . ']?', 'Yes')
                ->expectsQuestion('Are you sure you want to run seeds for [' . $subdomain . ']?', 'Yes')
                ->expectsQuestion('Seeds type?', 'Default')
                ->expectsQuestion('Are you sure you want to create a directory for [' . $subdomain . ']?', 'Yes')
                ->expectsQuestion('A directory with same name [' . $subdomain . '] exits. Do you want to back it up?', 'Yes')
                ->expectsOutput(' > ' . $subdomain . ' override successfully!')
                ->expectsOutput(' > ' . $subdomain . ' url: http://' . $subdomain . '.[your_domain]')
                ->assertExitCode(0);

        // => Assert properties for this tenant were updated with new values
        $tenant = Tenant::where('subdomain', $subdomain)->first();
        $this->assertEquals($subdomain, $tenant->subdomain);
        $this->assertEquals($name, $tenant->name);
        $this->assertEquals($description, $tenant->description);


        // try to create another tenant with same subdomain
        // choose: [ destory => Delete everything and exit ]
        $subdomain = 'demo';
        $name = 'Another Demo Tenant To Destroy';
        $description = 'Description of the other demo tenant to destroy';
        $this->artisan('xtenant:new')
                ->expectsQuestion('Enter subdomain', $subdomain)
                ->expectsOutput('This tenant [' . $subdomain . '] already exists.')
                ->expectsQuestion('Do you want to override/destory it?', 'Destroy')
                ->expectsQuestion('Are you sure you want to completely destroy [' . $subdomain . ']?', 'Yes')
                ->expectsQuestion('Delete database?', 'Backup')
                ->expectsQuestion('Delete directory?', 'Backup')
                ->expectsOutput(' > ' . $subdomain . ' destroyed successfully!')
                ->expectsQuestion('Are you sure you want to run migrations for [' . $subdomain . ']?', 'Yes')
                ->expectsQuestion('Are you sure you want to run seeds for [' . $subdomain . ']?', 'Yes')
                ->expectsQuestion('Seeds type?', 'Default')
                ->expectsQuestion('Are you sure you want to create a directory for [' . $subdomain . ']?', 'Yes')
                ->expectsOutput(' > ' . $subdomain . ' created successfully!')
                ->expectsOutput(' > ' . $subdomain . ' url: http://' . $subdomain . '.[your_domain]')
                ->assertExitCode(0);

        // => Assert tenant was created
        $tenant = Tenant::where('subdomain', $subdomain)->first();
        $this->assertEquals($subdomain, $tenant->subdomain);
        $this->assertTrue(is_dir(storage_path('app/' . $tenant->subdomain)));
    }
}
