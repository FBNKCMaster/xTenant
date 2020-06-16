<?php

namespace Tests\Feature;

//use Tests\TestCase;
//use Orchestra\Testbench\TestCase;
//use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestHelper as TestCase;

use FBNKCMaster\xTenant\Models\Tenant;

class BackupTenantTest extends TestCase
{
    public function test_can_backup_tenants_database_with_artisan_backupdb_console_command()
    {
        $subdomain = 'demo';
        $this->assertTrue(true);

        // First run setup
        $this->setupXTenant();
        
        // create demo tenant
        $this->createTenant($subdomain);

        // => Assert tenant was created
        $tenant = Tenant::where('subdomain', $subdomain)->first();
        $this->assertNotNull($tenant);
        
        // Get to tenant's database
        $database = $this->getTenantDatabase($subdomain);
        $this->assertNotNull($database);
        
        // Then backup its database
        //  1 - First case: enter subdomain directly as a parameter
        //      and cancel when asked to confirm backup
        $this->artisan('xtenant:backupdb ' . $subdomain)
                ->expectsQuestion('Are you sure you want to backup [' . $subdomain . ']\'s database?', 'No')
                ->expectsOutput(' > Operation canceled.')
                ->assertExitCode(0);
        //  2 - Second case: don't specify subdomain
        //      give output backup file as param
        //      and confirm backup
        $outputFile = $database . '_' . date('YmdHis') . '_Backup';
        $this->artisan('xtenant:backupdb --output=' . $outputFile)
                ->expectsQuestion('Enter tenant\'s subdomain', $subdomain)
                ->expectsQuestion('Are you sure you want to backup [' . $subdomain . ']\'s database?', 'Yes')
                ->expectsOutput(' > ' . $subdomain . '\'s database backed up successfully!')
                ->assertExitCode(0);

        // Assert database backup output exist
        $this->assertTrue(file_exists($outputFile));
    }

    public function test_can_backup_tenants_directory_with_artisan_backupdir_console_command()
    {
        $subdomain = 'demo';
        $this->assertTrue(true);

        // First run setup
        $this->setupXTenant();
        
        // create demo tenant
        $this->createTenant($subdomain);
        
        // Then backup its directory
        //  1 - First case: enter subdomain directly as a parameter
        //      and cancel when asked to confirm backup
        $this->artisan('xtenant:backupdir ' . $subdomain)
                ->expectsQuestion('Are you sure you want to backup [' . $subdomain . ']\'s directory?', 'No')
                ->expectsOutput(' > Operation canceled.')
                ->assertExitCode(0);
        //  2 - Second case: don't specify subdomain
        //      and confirm backup
        $backupPath = storage_path('app/' . $subdomain . '_' . date('YmdHis') . '_Backup');
        $this->artisan('xtenant:backupdir --path=' . $backupPath)
                ->expectsQuestion('Enter tenant\'s subdomain', $subdomain)
                ->expectsQuestion('Are you sure you want to backup [' . $subdomain . ']\'s directory?', 'Yes')
                ->expectsOutput('A backup of [' . $subdomain . ']\'s directory was created here: ' . $backupPath)
                //->expectsOutput('An error occurred. Could not make a backup of [' . $subdomain . ']\'s directory.')
                ->assertExitCode(0);

        // => Assert directory was backed up
        // assert original directory exist
        $this->assertTrue(is_dir(storage_path('app/' . $subdomain)));
        // assert backup directory exist
        $this->assertTrue(is_dir($backupPath));
    }
}
