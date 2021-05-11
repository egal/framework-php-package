<?php

namespace EgalFramework\CommandLine\Tests\Commands;

use EgalFramework\CommandLine\Commands\MakeMetadata;
use EgalFramework\CommandLine\Commands\MakeMigration;
use EgalFramework\CommandLine\Tests\CommandTestCase;
use EgalFramework\Common\Session;
use Exception;

class MakeMigrationTest extends CommandTestCase
{

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        parent::setUp();
        /** @var MakeMetadata $command */
        $command = $this->getCommand(MakeMetadata::class);
        $this->input->setArgument('modelName', 'Test/TestModel');
        $command->handle();
    }

    /**
     * @throws Exception
     */
    public function testMigration()
    {
        /** @var MakeMigration $command */
        $command = $this->getCommand(MakeMigration::class);
        $this->input->setArgument('modelName', 'Test/TestModel');
        $command->handle();
        $this->assertEquals($this->getAsset('migration.php'), $this->getMigrationFile());
    }

    public function getMigrationFile(): string
    {
        $data = '';
        $dir = opendir(Session::getRegistry()->get('DBPath') . 'migrations');
        while ($file = readdir($dir)) {
            if (in_array($file, ['.', '..'])) {
                continue;
            }

            $data = file_get_contents(Session::getRegistry()->get('DBPath') . 'migrations/' . $file);
        }
        closedir($dir);
        return $data;
    }

    public function testNoMetadata()
    {
        /** @var MakeMigration $command */
        $command = $this->getCommand(MakeMigration::class);
        $this->input->setArgument('modelName', 'TestModel1');
        $this->expectException(Exception::class);
        $command->handle();
    }

}
