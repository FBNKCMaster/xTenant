<?php

namespace Tests\Feature;

//use Tests\TestCase;
//use Orchestra\Testbench\TestCase;
//use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestHelper as TestCase;

use FBNKCMaster\xTenant\Models\XTenantParam;

class SetupMultiTenancyTest extends TestCase
{

    public function test_can_setup_xtenant_database_and_tables_with_artisan_setup_console_command()
    {
        $superAdminSubdomain = 'superadmin';
        $this->artisan('xtenant:setup')
                ->expectsQuestion('Enter SuperAdmin subdomain', $superAdminSubdomain)
                ->expectsQuestion('Allow "www"?', 'Yes')
                ->expectsOutput(' > Admin url: http://' . $superAdminSubdomain . '.[your_domain]')
                ->assertExitCode(0);
        
        $this->assertTrue(\Schema::hasTable('migrations'));
        $this->assertTrue(\Schema::hasTable('tenants'));
        $this->assertTrue(\Schema::hasTable('x_tenant_params'));

        $param = XTenantParam::getParams();

        $this->assertEquals($superAdminSubdomain, $param->super_admin_subdomain);
        $this->assertTrue($param->allow_www);
    }

    public function test_if_already_setup_ask_overwite_existing_params()
    {
        $superAdminSubdomain = 'superadmin';
        $allowWww = 'Yes';
        $this->artisan('xtenant:setup')
                ->expectsQuestion('Enter SuperAdmin subdomain', $superAdminSubdomain)
                ->expectsQuestion('Allow "www"?', $allowWww)
                ->expectsOutput(' > Admin url: http://' . $superAdminSubdomain . '.[your_domain]')
                ->assertExitCode(0);

        $this->artisan('xtenant:setup')
                ->expectsQuestion('Override existing setup? [ superadmin\'s subdomain: ' . $superAdminSubdomain . ' ]', 'No')
                ->expectsOutput(' > Admin url: http://' . $superAdminSubdomain . '.[your_domain]')
                ->assertExitCode(0);

        $new_superAdminSubdomain = 'super_new_admin';
        $allowWww = 'No';
        $this->artisan('xtenant:setup')
                ->expectsQuestion('Override existing setup? [ superadmin\'s subdomain: ' . $superAdminSubdomain . ' ]', 'Yes')
                ->expectsQuestion('Enter new SuperAdmin subdomain', $new_superAdminSubdomain)
                ->expectsQuestion('Allow "www"?', $allowWww)
                ->expectsOutput(' > New admin url: http://' . $new_superAdminSubdomain . '.[your_domain]')
                ->assertExitCode(0);
    }
}
