<?php

namespace Egal\Tests;

use Laravel\Lumen\Testing\DatabaseMigrations as BaseDatabaseMigrations;

trait DatabaseMigrations
{

    use BaseDatabaseMigrations {
        runDatabaseMigrations as baseRunDatabaseMigrations;
    }

    public function getMigrationFileName(): string
    {
        return $this->migrationFileName ?? 'migration.php';
    }

    public abstract function getDir(): string;

    public function runDatabaseMigrations(): void
    {
        $path = $this->getDir() . DIRECTORY_SEPARATOR . $this->getMigrationFileName();
        $this->artisan('migrate:fresh', [
            '--realpath' => true,
            '--path' => $path,
        ]);
        $this->beforeApplicationDestroyed(function () use ($path) {
            $this->artisan('migrate:rollback', [
                '--realpath' => true,
                '--path' => $path,
            ]);
        });
    }

}
