<?php

namespace EgalFramework\CommandLine\Tests\Commands;

use EgalFramework\CommandLine\Commands\MakeMetadata;
use EgalFramework\CommandLine\Tests\CommandTestCase;
use EgalFramework\Common\Session;
use Exception;

class MakeMetadataTest extends CommandTestCase
{

    /**
     * @throws Exception
     */
    public function testMakeMetadata(): void
    {
        /** @var MakeMetadata $command */
        $command = $this->getCommand(MakeMetadata::class);
        $this->input->setArgument('modelName', 'Test/TestModel');
        $command->handle();
        $this->assertEquals(
            $this->getAsset('Metadata.php'),
            file_get_contents(Session::getRegistry()->get('AppPath') . '/Metadata/Test/TestModel.php')
        );
        $command = $this->getCommand(MakeMetadata::class);
        $this->input->setArgument('modelName', 'Test/TestModel');
        $this->expectExceptionMessage('MetaData is already exists');
        $command->handle();
    }

}
