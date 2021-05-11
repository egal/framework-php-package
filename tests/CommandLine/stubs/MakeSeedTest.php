<?php

namespace EgalFramework\CommandLine\Tests\Commands;

use EgalFramework\CommandLine\Commands\MakeSeed;
use EgalFramework\CommandLine\Tests\CommandTestCase;
use EgalFramework\Common\Session;
use Exception;

class MakeSeedTest extends CommandTestCase
{

    /**
     * @throws Exception
     */
    public function testMakeSeed()
    {
        $modelName = 'Test/TestModel';

        /** @var MakeSeed $command */
        $command = $this->getCommand(MakeSeed::class);
        $this->input->setArgument('modelName', $modelName);
        $command->handle();
        $this->assertEquals(
            $this->getAsset('Seed.php'),
            file_get_contents(Session::getRegistry()->get('SeedPath') . $modelName . '.php')
        );

        $this->expectExceptionMessage('Seed is already exist');
        $command->handle();
    }

}
