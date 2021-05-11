<?php

namespace EgalFramework\CommandLine\Tests\Commands;

use EgalFramework\CommandLine\Commands\MakeFactory;
use EgalFramework\CommandLine\Commands\MakeMetadata;
use EgalFramework\CommandLine\Tests\CommandTestCase;
use EgalFramework\Common\Session;
use Exception;

class MakeFactoryTest extends CommandTestCase
{

    /**
     * @throws Exception
     */
    public function testMakeFactory(): void
    {
        $modelName = 'Test/TestModel';

        /** @var MakeMetadata $command */
        $command = $this->getCommand(MakeMetadata::class);
        $this->input->setArgument('modelName', $modelName);
        $command->handle();

        /** @var MakeFactory $command */
        $command = $this->getCommand(MakeFactory::class);
        $this->input->setArgument('modelName', $modelName);
        $command->handle();
        $data = file_get_contents(Session::getRegistry()->get('FactoryPath') . '/' . $modelName . '.php');
        $this->assertEquals($this->getAsset('Factory.php'), $data);

        $this->expectException(Exception::class);
        $command->handle();
    }

}
