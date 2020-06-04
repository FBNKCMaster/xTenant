<?php

namespace FBNKCMaster\xTenant\Console\Commands;

//use Artisan;
//use Illuminate\Console\Command;

use FBNKCMaster\xTenant\Models\Tenant;

class BackupDatabase
{
    /**
     * Run database backup.
     * 
     * @param   string  $subdomain
     * @param   string  $databaseType
     *
     * @return  boolean
     */
    public static function run($subdomain, $databaseType)
    {
        $database = (Tenant::where('subdomain', $subdomain)->first() ?? null)->database ?? null;

        if ($database) {
            if ($databaseType == 'mysql') {
                return self::mysqlBackup($database);
            } elseif ($databaseType == 'sqlite') {
                return self::sqliteBackup($database);
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    private function mysqlBackup()
    {
        $command = sprintf('mysqldump' . $this->options . ' --user=%s --password=%s --host=%s --port=%s %s > %s',
			escapeshellarg($this->user),
			escapeshellarg($this->pswd),
			escapeshellarg($this->host),
			escapeshellarg($this->port),
			escapeshellarg($this->database),
			escapeshellarg($this->path.$this->fileName)
		);
        //return exec($command);
        return $this->exeCmd($command) === true ? ['result' => 'ok', 'file' => $this->path.$this->fileName] : ['result' => 'error', 'message' => $this->exeCmd($command)];
    }

    private function exeCmd($command)
	{
		$process = new Process($command);
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
    
    private static function sqliteBackup($database)
    {
        return true;
    }
}