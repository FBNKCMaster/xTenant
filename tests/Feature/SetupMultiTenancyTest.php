<?php

namespace Tests\Feature;

//use Tests\TestCase;
//use Orchestra\Testbench\TestCase;
//use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestHelper as TestCase;

use FBNKCMaster\xTenant\Models\XTenantSetting;

class SetupMultiTenancyTest extends TestCase
{

    public function test_can_setup_xtenant_database_and_tables_with_artisan_setup_console_command()
    {
        $this->superAdminSubdomain = 'superadmin';
        $email = 'superadmin@xtenant.test';
        $password = 'secretlongenough';
        $this->artisan('xtenant:setup')
                ->expectsQuestion('Enter SuperAdmin subdomain', $this->superAdminSubdomain)
                ->expectsQuestion('Enter SuperAdmin email (this will be your login)', $email)
                ->expectsQuestion('Enter SuperAdmin password (must be at least 8 characters long)', $password)
                ->expectsQuestion('Confirm password', $password)
                ->expectsQuestion('Allow "www"?', 'Yes')
                ->expectsOutput(' > Admin url: http://' . $this->superAdminSubdomain . '.[your_domain]')
                ->assertExitCode(0);
        
        $this->assertTrue(\Schema::hasTable('migrations'));
        $this->assertTrue(\Schema::hasTable('tenants'));
        $this->assertTrue(\Schema::hasTable('x_tenant_settings'));

        $setting = XTenantSetting::getSettings();

        $this->assertEquals($this->superAdminSubdomain, $setting->super_admin_subdomain);
        $this->assertTrue($setting->allow_www);
    }

    public function test_when_setup_xtenant_if_super_admin_subdomain_is_empty_show_error()
    {
        $this->superAdminSubdomain = ' ';

        $this->artisan('xtenant:setup')
                ->expectsQuestion('Enter SuperAdmin subdomain', $this->superAdminSubdomain)
                ->expectsOutput('Sorry, the SuperAdmin subdomain cannot be empty. Repeat again.')
                ->assertExitCode(0);

        $this->superAdminSubdomain = '';

        $this->artisan('xtenant:setup')
                ->expectsQuestion('Enter SuperAdmin subdomain', $this->superAdminSubdomain)
                ->expectsOutput('Sorry, the SuperAdmin subdomain cannot be empty. Repeat again.')
                ->assertExitCode(0);
    }

    public function test_when_setup_xtenant_if_invalid_email_format_show_error()
    {
        $this->superAdminSubdomain = 'superadmin';
        $email = '@this_is_an.invalid.email';
        $password = 'secret';

        $this->artisan('xtenant:setup')
                ->expectsQuestion('Enter SuperAdmin subdomain', $this->superAdminSubdomain)
                ->expectsQuestion('Enter SuperAdmin email (this will be your login)', $email)
                ->expectsOutput('Sorry, this is an invalid email format. Repeat again.')
                ->assertExitCode(0);
    }

    public function test_when_setup_xtenant_if_password_is_too_short_show_error()
    {
        $this->superAdminSubdomain = 'superadmin';
        $email = 'superadmin@xtenant.test';
        $password = 'secret';

        $this->artisan('xtenant:setup')
                ->expectsQuestion('Enter SuperAdmin subdomain', $this->superAdminSubdomain)
                ->expectsQuestion('Enter SuperAdmin email (this will be your login)', $email)
                ->expectsQuestion('Enter SuperAdmin password (must be at least 8 characters long)', $password)
                ->expectsOutput('Sorry, the password is too short. It must be at least 8 characters.')
                ->assertExitCode(0);
    }

    public function test_when_setup_xtenant_if_passwords_do_not_match_show_error()
    {
        $this->superAdminSubdomain = 'superadmin';
        $email = 'superadmin@xtenant.test';
        $password = 'secretlongenough';
        $password_confirm = 'hguonegnolterces';

        $this->artisan('xtenant:setup')
                ->expectsQuestion('Enter SuperAdmin subdomain', $this->superAdminSubdomain)
                ->expectsQuestion('Enter SuperAdmin email (this will be your login)', $email)
                ->expectsQuestion('Enter SuperAdmin password (must be at least 8 characters long)', $password)
                ->expectsQuestion('Confirm password', $password_confirm)
                ->expectsOutput('Sorry, the passwords you entered do not match. Repeat again.')
                ->assertExitCode(0);
    }

    public function test_if_already_setup_ask_overwite_existing_settings()
    {
        $this->superAdminSubdomain = 'superadmin';
        $email = 'superadmin@xtenant.test';
        $password = 'secretlongenough';
        $allowWww = 'Yes';
        $this->artisan('xtenant:setup')
                ->expectsQuestion('Enter SuperAdmin subdomain', $this->superAdminSubdomain)
                ->expectsQuestion('Enter SuperAdmin email (this will be your login)', $email)
                ->expectsQuestion('Enter SuperAdmin password (must be at least 8 characters long)', $password)
                ->expectsQuestion('Confirm password', $password)
                ->expectsQuestion('Allow "www"?', $allowWww)
                ->expectsOutput(' > Admin url: http://' . $this->superAdminSubdomain . '.[your_domain]')
                ->assertExitCode(0);

        $this->artisan('xtenant:setup')
                ->expectsQuestion('Override existing setup? [ superadmin\'s subdomain: ' . $this->superAdminSubdomain . ' ]', 'No')
                ->expectsOutput(' > Admin url: http://' . $this->superAdminSubdomain . '.[your_domain]')
                ->assertExitCode(0);

        $this->newSuperAdminSubdomain = 'super_new_admin';
        $allowWww = 'No';
        $this->artisan('xtenant:setup')
                ->expectsQuestion('Override existing setup? [ superadmin\'s subdomain: ' . $this->superAdminSubdomain . ' ]', 'Yes')
                ->expectsQuestion('Enter new SuperAdmin subdomain', $this->newSuperAdminSubdomain)
                ->expectsQuestion('Enter SuperAdmin email (this will be your login)', $email)
                ->expectsQuestion('Enter SuperAdmin password (must be at least 8 characters long)', $password)
                ->expectsQuestion('Confirm password', $password)
                ->expectsQuestion('Allow "www"?', $allowWww)
                ->expectsOutput(' > New admin url: http://' . $this->newSuperAdminSubdomain . '.[your_domain]')
                ->assertExitCode(0);
    }
}
