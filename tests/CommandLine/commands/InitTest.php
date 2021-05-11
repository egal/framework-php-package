<?php

namespace EgalFramework\CommandLine\Tests\Commands;

use EgalFramework\CommandLine\Commands\Init;
use EgalFramework\CommandLine\Tests\CommandTestCase;

class InitTest extends CommandTestCase
{

    public function testInit()
    {
        /** @var Init $command */
        $command = $this->getCommand(Init::class);
        $command->handle();
        $this->assertEquals($this->getAsset('.env'), file_get_contents($this->tmpDir . DIRECTORY_SEPARATOR . '.env'));
    }

}
