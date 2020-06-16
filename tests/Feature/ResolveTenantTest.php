<?php

namespace Tests\Feature;

//use Orchestra\Testbench\TestCase;
//use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestHelper as TestCase;

use Illuminate\Http\Request;

use FBNKCMaster\xTenant\Models\Tenant;
use FBNKCMaster\xTenant\Models\XTenantSetting;

class ResolveTenantTest extends TestCase
{

    public function test_can_find_existing_tenant_for_the_request_with_tenant_as_subdomain()
    {
        $subdomain = 'demo';
        // First run setup
        $this->setupXTenant();
        
        // create demo tenant
        $this->createTenant($subdomain);

        // make the request
        $request = Request::create('http://' . $subdomain . '.your_domain.test');

        // get settings
        $xTenantSettings = XTenantSetting::getSettings();
        
        // check if tenant was registred
        $result = Tenant::findTenant($request, $xTenantSettings->allow_www);
        $tenant = $result['tenant'];

        $this->assertEquals($result['subdomain'], $subdomain);
        $this->assertEquals($tenant->subdomain, $subdomain);
    }

    public function test_will_return_null_if_there_is_no_tenant_for_the_request()
    {
        $subdomain = 'demo';
        // First run setup
        $this->setupXTenant();

        // make the request
        $request = Request::create('http://' . $subdomain . '.your_domain.test');

        // get settings
        $xTenantSettings = XTenantSetting::getSettings();
        
        // check if tenant was registred
        $result = Tenant::findTenant($request, $xTenantSettings->allow_www);

        
        $tenant = $result['tenant'];
        
        $this->assertEquals($result['subdomain'], $subdomain);
        $this->assertNull($tenant);
    }

}
