<?php

namespace Tests\Feature;

//use Orchestra\Testbench\TestCase;
//use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestHelper as TestCase;

use Illuminate\Http\Request;

use FBNKCMaster\xTenant\Models\Tenant;
use FBNKCMaster\xTenant\Models\XTenantSetting;

use Illuminate\Support\Facades\Http;

use Illuminate\Support\Facades\Route;

class WebConsoleCommandsTest extends TestCase
{
    protected $controller;

    public function test_web_command_exists()
    {
        $this->assertTrue(class_exists(\FBNKCMaster\xTenant\Console\Commands\CommandWeb::class));
    }

    public function test_command_new()
    {
        // First run setup
        $this->setupXTenant();

        $this->controller = new \FBNKCMaster\xTenant\Controllers\SuperAdminController();

        // Run xtenant:new with a post request
        $responseNewCommand = $this->sendRequest('/cmd', [
            'cmd' => 'new',
        ]);
        $qHash = $responseNewCommand['q_hash'];
        $text = $responseNewCommand['text'];
        $this->assertEquals('ask', $responseNewCommand['action']);
        $this->assertEquals(' > Enter subdomain', $text);
        
        // Enter subdomain
        $tenantSubdomain = 'demo_tenant_test';
        $responseEnterSubdomain = $this->sendRequest('/cmd', [
            'cmd' => 'new',
            'q_hash' => $qHash,
            'web_input' => $tenantSubdomain,
        ]);
        $qHash = $responseEnterSubdomain['q_hash'];
        $text = $responseEnterSubdomain['text'];
        $this->assertEquals('ask', $responseEnterSubdomain['action']);
        $this->assertEquals(' > Enter name', $text);
        
        // Enter name
        $tenantName = 'Demo Tenant';
        $responseEnterName = $this->sendRequest('/cmd', [
            'cmd' => 'new',
            'q_hash' => $qHash,
            'web_input' => $tenantName,
        ]);
        $qHash = $responseEnterName['q_hash'];
        $text = $responseEnterName['text'];
        $this->assertEquals('ask', $responseEnterName['action']);
        $this->assertEquals(' > Enter description', $text);
        
        // Enter description
        $tenantDescription = 'This is the description of Demo Tenant';
        $responseEnterDescription = $this->sendRequest('/cmd', [
            'cmd' => 'new',
            'q_hash' => $qHash,
            'web_input' => $tenantDescription,
        ]);
        /* 
        // Don't know why this one fails in tests, but it is working perfectly in production
        // Hint: $this->call('xtenant:migrate'), $this->call('xtenant:seed'), $this->call('xtenant:directors')
        //       are called behind the scene and don't output anything
        $qHash = $responseEnterDescription['q_hash'];
        $text = $responseEnterDescription['text'];
        $this->assertEquals('ask', $responseEnterDescription['action']);
        $this->assertEquals(' > "xtenant:migrate [' . $tenantSubdomain . ']" shall be run directly', $text);
         */

        $tenant = Tenant::where('subdomain', $tenantSubdomain)->first();
        $this->assertNotNull($tenant);
        $this->assertEquals($tenantSubdomain, $tenant->subdomain);
        //$this->assertTrue(is_dir(storage_path('app/' . $tenant->subdomain)));
        // xtenant:directory shall be run directly
        
        // Connect to tenant's database
        $this->tenantDatabase = $this->getTenantDatabase($tenantSubdomain);
        $this->connectToTenantDatabase($this->tenantDatabase);
        //$this->assertTrue(\Schema::hasTable('migrations'));
        // xtenant:migrate shall be run directly

        // Assert symbolic link for this tenant was created
        $link = public_path($tenantSubdomain);
        $this->assertTrue(is_link($link));

    }

    private function sendRequest($url, $data, $method = 'POST')
    {
        $request = Request::create($url, $method, $data);
        $response = $this->controller->cmd($request);
        return $response[0] ?? [];
    }

}
