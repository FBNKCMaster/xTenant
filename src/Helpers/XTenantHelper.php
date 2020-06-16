<?php

namespace FBNKCMaster\xTenant\Helpers;

use Symfony\Component\Process\Process;

class XTenantHelper
{

  	public static function createDatabase($subdomain)
    {
        $databaseName = $subdomain . '.db';
        $dbConnectionType = self::getDatabaseConnectionType();
        
        switch($dbConnectionType) {
            case 'SQLiteConnection':
                return self::createSQLiteDatabase($databaseName);
                break;
            
            case 'MySqlConnection':
                return self::createMySqlDatabase($databaseName);
                break;
            
            case 'PostgresConnection':
                return self::createPostgresDatabase($databaseName);
                break;
            
            case 'SqlServerConnection':
                return self::createSqlServerDatabase($databaseName);
                break;
            
            default:
                return null;
                break;
        }
    }
	
	public static function runMigrations($subdomain, $database, $migrationType = 'default', $thisConsoleInstance = null, $messageBag = null)
    {
        // Save default database
        $defaultDatabase = self::getDefaultDatabase();
        
        if ($database) {
            // Connect to the tenant's database
            self::connectToTheNewDatabase($database);
            
            // run migrations for this database
            if ($migrationType == 'ask' && $thisConsoleInstance) {
                $migrationType = $thisConsoleInstance->choice('Migration type?', ['default', 'refresh', 'fresh'], 0);
                $thisConsoleInstance->line('Running migrations: ' . base_path('database/migrations'));
            }
            
            $migrateCommand = 'migrate' . ($migrationType != 'default' ? ':' . $migrationType : '');

            \Artisan::call($migrateCommand, ['--path' => base_path('database/migrations'), '--realpath' => true, '--env' => \App::environment(), '--force' => true]);
            
            $thisConsoleInstance && $thisConsoleInstance->line(\Artisan::output());
            
            // Then reset back default connction
            self::resetBackDefaultConnection($defaultDatabase);
        } else {
            $error = 'ERROR: Could not connect to this tenant\'s database. Please check your connection.';
            if (is_null($thisConsoleInstance)) {
                $messageBag->add('migrations', $error);
                return $messageBag;
            } else {
                $thisConsoleInstance->error($error);
            }
        }
    }
	
	public static function runSeeds($subdomain, $database, $seedType = 'default', $thisConsoleInstance = null, $messageBag = null)
    {
        // Save default database
        $defaultDatabase = self::getDefaultDatabase();

        if ($database) {
            // Connect to the tenant's database
            self::connectToTheNewDatabase($database);

            if ($seedType == 'ask' && $thisConsoleInstance) {
                // Ask for type of seed
                $seedType = strtolower($thisConsoleInstance->choice('Seeds type?', ['Default', 'Custom', 'Fresh'], 0));
                //dump('-------- ' . $seedType);
                //dd('Running `' .$seedType. '` seeds within: ' . base_path('database/seeds/'));
                $thisConsoleInstance->line('Running `' .$seedType. '` seeds within: ' . base_path('database/seeds/'));
        
                try {
                    if ($seedType == 'custom') {
                        $seeders = glob(base_path('database/seeds/*.php'));
                        $seeders = str_replace([base_path('database/seeds/'), '.php'], ['', ''], $seeders);
                        $seeder = $thisConsoleInstance->anticipate('What seeder do you want to run?', $seeders);
                        \Artisan::call('db:seed', ['--class' => $seeder, '--env' => \App::environment(), '--force' => true]);
                    } else {
                        if ($seedType == 'fresh') {
                            \Artisan::call('migrate:fresh', ['--env' => \App::environment(), '--seed' => true, '--force' => true]);
                        } else {
                            \Artisan::call('db:seed', ['--env' => \App::environment(), '--force' => true]);
                        }
                    }
            
                    $thisConsoleInstance->line(\Artisan::output());
                } catch (\Throwable $th) {
                    $thisConsoleInstance->warn('No seeds found in: ' . base_path('database/seeds/'));
                }
                
            } else {
                if ($seedType == 'fresh') {
                    \Artisan::call('migrate:fresh', ['--env' => \App::environment(), '--seed' => true, '--force' => true]);
                } else {
                    \Artisan::call('db:seed', ['--env' => \App::environment(), '--force' => true]);
                }
            }

            // Then reset back default connction
            self::resetBackDefaultConnection($defaultDatabase);
        } else {
            $error = 'ERROR: Could not connect to this tenant\'s database. Please check your connection.';
            if (is_null($thisConsoleInstance)) {
                $messageBag->add('migrations', $error);
                return $messageBag;
            } else {
                $thisConsoleInstance->error($error);
            }
        }
    }
	
	public static function createDirectory($subdomain, $action = 'default', $thisConsoleInstance = null, $messageBag = null)
    {
        $subdomain = strtolower($subdomain);
        // Check if directory with same name exists
        if (is_dir(storage_path('app/' . $subdomain))) {
            if ($action == 'ask' && $thisConsoleInstance) {
                $choice = $thisConsoleInstance->choice('A directory with the same name [' . $subdomain . '] exists. Do you want to back it up?', ['Yes', 'No'], 1);
                
                if ($choice == 'Yes') {
                    $backupPath = self::backupDir($subdomain, true);
                    if ($backupPath) {
                        $thisConsoleInstance->line('You can find the old directory here: ' . $backupPath);
                    } else {
                        $thisConsoleInstance->warn('An error occurred. We could not backup this directory: ' . storage_path('app/' . $subdomain));
                    }
                    
                } else {
                    if (!self::removeDir($subdomain)) {
                        $thisConsoleInstance->warn('An error occurred. We could not remove this directory: ' . storage_path('app/' . $subdomain));
                    } else {
                        $thisConsoleInstance->line('Old directory completely removed.');
                    }
                }
            } else {
                if ($action == 'backup') {
                    $backupPath = self::backupDir($subdomain, true);
                    if ($backupPath) {
                        $error = 'A directory with the same name [' . $subdomain . '] was found and been backed up here: ' . $backupPath;
                    } else {
                        $error = 'A directory with the same name [' . $subdomain . '] was found but COULD NOT back it up.';
                    }
                    
                } else if ($action == 'fresh') {
                    if (!self::removeDir($subdomain)) {
                        $error = 'An error occurred. We could not remove this directory: ' . storage_path('app/' . $subdomain);
                    }
                }

                if (isset($error)) {
                    $messageBag->add('directory', $error);
                }
            }
            
        }
        
        // Create tenant's directory in storage/app
        $thisConsoleInstance && $thisConsoleInstance->line('Creating directory: ' . base_path('storage/app/' . $subdomain));
        \Storage::makeDirectory($subdomain, 0777/* , $recursive = false, $force = false */);
        return $messageBag;
    }

    public static function destroyTenant($tenant, $action = 'backup', $thisConsoleInstance = null, $messageBag = null) {
        $messageBag = self::removeDatabase($tenant->subdomain, $action, $thisConsoleInstance, $messageBag);
        $messageBag = self::removeDirectory($tenant->subdomain, $action, $thisConsoleInstance, $messageBag);
        if (!$tenant->delete()) {
            $error = 'An error occurred. Could not delete this tenant.';
            if ($messageBag) {
                $messageBag->add('tenant', $error);
            } else if ($thisConsoleInstance) {
                $thisConsoleInstance->error($error);
            }
        }
        return $messageBag;
    }

    public static function createSymlink($subdomain, $thisConsoleInstance = null, $messageBag = null)
    {
        $link = public_path($subdomain);
        $target = storage_path('app/' . $subdomain);
        $bCreated = @symlink($target, $link);

        if (!$bCreated) {
            $error = 'Could not create symbolic link for the directory of this tenant.';
            if (is_null($thisConsoleInstance)) {
                $messageBag->add('directory', $error);
            } else {
                $thisConsoleInstance->warn($error);
            }
        }

        return $messageBag;
    }

    private static function getDefaultDatabase()
    {
        $defaultConnection = \DB::getDefaultConnection();
        return config()->get('database.connections.' . $defaultConnection . '.database');
    }

    public static function getDatabaseConnectionType()
    {
        return str_replace('Illuminate\Database\\', '', get_class(\DB::connection()));
    }

    private static function connectToTheNewDatabase($database)
    {
        $defaultConnection = \DB::getDefaultConnection();
        \DB::purge($defaultConnection);
        config()->set('database.connections.' . $defaultConnection . '.database', $database);
        \DB::reconnect();
    }

    private static function resetBackDefaultConnection($defaultDatabase)
    {
        $defaultConnection = \DB::getDefaultConnection();
        \DB::purge($defaultConnection);
        //config()->set('database.connections.' . $defaultConnection . '.database', env('DB_DATABASE'));
        config()->set('database.connections.' . $defaultConnection . '.database', $defaultDatabase);
        \DB::reconnect();
    }

    private static function removeDatabase($subdomain, $action = 'backup', $thisConsoleInstance, $messageBag = null)
    {
        $databaseName = $subdomain . '.db';

        if ($action == 'ask' && $thisConsoleInstance) {
            $choice = $thisConsoleInstance->choice('Delete database?', ['Yes', 'No', 'Backup'], 2);
    
            switch ($choice) {
                case 'Yes':
                    if (!self::dropDatabase($databaseName)) {
                        $thisConsoleInstance->warn(' ! Could not drop ' . $subdomain . '\'s database.');
                    } else {
                        return true;
                    }
                    break;
    
                case 'Backup':
                    if (self::backupDatabase($databaseName)) {
                        $thisConsoleInstance->line($subdomain . '\'s database backed up!');
                    } else {
                        $thisConsoleInstance->warn(' ! Could not backup ' . $subdomain . '\'s database.');
                    }
                    break;
            }
        } else {
            if ($action == 'backup') {
                if (self::backupDatabase($databaseName)) {
                    $error = 'A backup of [' . $subdomain . ']\'s database was saved.';
                } else {
                    $error = 'Could not make a backup of [' . $subdomain . ']\'s database.';
                }
                
            } else {
                if (!self::dropDatabase($databaseName)) {
                    $error = 'An error occurred. We could not delete [' . $subdomain . ']\'s database.';
                }
            }

            $messageBag->add('database', $error);
        }
        
        return $messageBag;
    }

    private static function removeDirectory($subdomain, $action = 'backup', $thisConsoleInstance, $messageBag = null)
    {
        if ($action == 'ask' && $thisConsoleInstance) {
            $choice = $thisConsoleInstance->choice('Delete directory?', ['Yes', 'No', 'Backup'], 2);
    
            switch ($choice) {
                case 'Yes':
                    $thisConsoleInstance->line('Deleting ' . $subdomain . '\'s directory: ' . base_path('storage/app/' . $subdomain));
                    if (!self::removeDir($subdomain)) {
                        $thisConsoleInstance->warn(' ! Could not delete ' . $tenant['subdomain'] . '\'s directory.');
                    }
                    break;
    
                case 'Backup':
                    $thisConsoleInstance->line('Creating backup for ' . $subdomain . '\'s directory: ' . base_path('storage/app/' . $subdomain));
                    if (!self::backupDir($subdomain, true)) {
                        $thisConsoleInstance->warn(' ! Could not backup ' . $tenant['subdomain'] . '\'s directory.');
                    }
                    break;
            }
        } else {
            if ($action == 'backup') {
                $backupPath = self::backupDir($subdomain, true);
                if ($backupPath) {
                    $error = 'A backup of [' . $subdomain . ']\'s directory was created here: ' . $backupPath;
                } else {
                    $error = 'An error occurred. Could not make a backup of [' . $subdomain . ']\'s directory.';
                }
                
            } else {
                if (!self::removeDir($subdomain)) {
                    $error = 'An error occurred. We could not delete [' . $subdomain . ']\'s directory.';
                }
            }

            $messageBag->add('directory', $error);
        }
        
        return $messageBag;
    }

    private static function dropDatabase($database)
    {
        $dbConnectionType = self::getDatabaseConnectionType();
        
        switch($dbConnectionType) {
            case 'SQLiteConnection':
                $database = database_path($database);
                return is_file($database) && @unlink($database);
                break;
            
            case 'MySqlConnection': case 'PostgresConnection': case 'SqlServerConnection':
                try {
                    //if (\DB::statement('DROP DATABASE IF EXISTS :db_name', ['db_name' => $database])) {
                    $sqlQuery = "'DROP DATABASE IF EXISTS '$database'";
                    return \DB::statement($sqlQuery);
                } catch (\PDOException $e) {
                    return false;
                }
                break;
            
            default:
                return null;
                break;
        }
    }

    public static function backupDatabase($database, $outputFile = null)
    {
        $dbConnectionType = self::getDatabaseConnectionType();
        $outputFile = $outputFile ?? database_path($database . '_' . date('YmdHis') . '_Backup');
        $connectionType = \DB::getDefaultConnection();
        $connection = config('database.connections.' . $connectionType);
        switch($dbConnectionType) {
            case 'SQLiteConnection':
                return self::backupSQLiteDatabase($database, $outputFile);
                break;
            
            case 'MySqlConnection':
                return self::backupMySqlDatabase($database, $outputFile, $connection);
                break;
            
            case 'PostgresConnection':
                return self::backupPostgresDatabase($database, $outputFile, $connection);
                break;
            
            case 'SqlServerConnection':
                return self::backupSqlServerDatabase($database, $outputFile, $connection);
                break;
            
            default:
                return false;
                break;
        }
    }

    private static function createSQLiteDatabase($databaseName)
    {
        $databaseName = database_path($databaseName);
        return @touch($databaseName) ? $databaseName : null;
    }

    private static function backupSQLiteDatabase($database, $outputFile, $bRemove = false)
    {
        if ($bRemove) {
            $b = @rename(database_path($database), $outputFile);
        } else {
            $b = @copy(database_path($database), $outputFile);
        }
        return $b;
    }

    private static function createMySqlDatabase($databaseName)
    {
        // Tried binding but didn't work
        //  $sqlQuery = 'CREATE DATABASE IF NOT EXISTS :db_name DEFAULT CHARACTER SET utf8mb4';
        //  \DB::statement($sqlQuery, ['db_name' => $databaseName]);
        // So did it this way
        $sqlQuery = 'CREATE DATABASE IF NOT EXISTS `' . $databaseName . '` DEFAULT CHARACTER SET utf8mb4';
        return \DB::statement($sqlQuery) ? $databaseName : null;
    }

    private static function backupMySqlDatabase($database, $outputFile, $connection)
    {
        $command = [
            '/usr/local/mysql/bin/mysqldump',
            '--user=' . $connection['username'],
            '--password=' . $connection['password'],
            '--host=' . $connection['host'],
            '--port=' . $connection['port'],
            // https://dev.mysql.com/doc/refman/8.0/en/mysqldump.html
            '--complete-insert=true', // Include column names
            '--single-transaction=true', // Issue a BEGIN SQL statement before dumping data from the server.
            '--quick=true', // Enforce dumping tables row by row. This provides added safety for systems with little RAM and/or large databases where storing tables in memory could become problematic.
            '--lock-tables=false', // Do not lock tables for the backup session.
            // For dumping data only uncomment this
            //'--insert-ignore=true',
            //'--no-create-info=true',
            //'--no-create-db=true',
            $database,
            '--result-file=' . $outputFile
        ];
        
        $output = self::exeCmd($command);
        return $output;
    }

    private static function createPostgresDatabase($databaseName)
    {
        return null;
    }

    private static function backupPostgresDatabase($database, $outputFile, $connection)
    {
        // No yet implemented
        return false;
    }

    private static function createSqlServerDatabase($databaseName)
    {
        return null;
    }

    private static function backupSqlServerDatabase($database, $outputFile, $connection)
    {
        // No yet implemented
        return false;
    }

    private static function exeCmd($command)
	{
        $process = new Process($command);
        //$process->setWorkingDirectory(null);
		$process->setTimeout(999999999);
		$process->run();
		if ($process->isSuccessful())
		{
			return true;
		}
		else
		{
			return $process->getErrorOutput();
		}
    }

    public static function removeDir($dir)
	{
        if (!is_null($dir) && !empty($dir)) {
            $dir = storage_path('app/' . $dir);
            self::rrmdir($dir);
            return !is_dir($dir);
        } else {
            return true;
        }
    }

    public static function backupDir($dir, $bRemove = false, $path = null) {
        $path = $path ?? storage_path('app/' . $dir . '_' . date('YmdHis') . '_Backup');
        if ($bRemove) {
            $b = rename(storage_path('app/' . $dir), $path);
        } else {
            //$b = copy(storage_path('app/' . $dir), $path);
            $b = self::rcopy(storage_path('app/' . $dir), $path);
        }
        return $b ? $path : false;
    }

    // https://stackoverflow.com/a/3338133
    private static function rrmdir($dir) { 
        if (is_dir($dir)) { 
            $objects = scandir($dir); 
            foreach ($objects as $object) { 
                if ($object != '.' && $object != '..') { 
                    if (is_dir($dir . '/' . $object)) {
                        self::removeDir($dir . '/' . $object);
                    } else {
                        @unlink($dir . '/' . $object); 
                    }
                } 
            }
            @rmdir($dir);
        } 
    }

    private static function rcopy($src, $dst) { 
        $dir = opendir($src); 
        @mkdir($dst);
        while (false !== ($file = readdir($dir))) { 
            if ($file != '.' && $file != '..') { 
                if (is_dir($src . DIRECTORY_SEPARATOR . $file)) { 
                    self::rcopy($src . DIRECTORY_SEPARATOR . $file, $dst . DIRECTORY_SEPARATOR . $file); 
                } else { 
                    copy($src . DIRECTORY_SEPARATOR . $file, $dst . DIRECTORY_SEPARATOR . $file); 
                } 
            } 
        } 
        closedir($dir);
        
        return is_dir($dst);
    }

}