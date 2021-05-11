<?php

namespace EgalFramework\CommandLine\Tests\Commands;

use EgalFramework\CommandLine\Commands\MakeMetadata;
use EgalFramework\CommandLine\Commands\MakeModel;
use EgalFramework\CommandLine\Commands\Rehash;
use EgalFramework\CommandLine\Tests\CommandTestCase;
use EgalFramework\CommandLine\Tests\Stubs\App;
use EgalFramework\Common\Session;
use Exception;
use Illuminate\Support\Facades\Schema;

class RehashTest extends CommandTestCase
{

    /**
     * @throws Exception
     */
    public function testRehash()
    {
        /** @var MakeMetadata $metadataCommand */
        $metadataCommand = $this->getCommand(MakeMetadata::class);
        $this->input->setArgument('modelName', 'Test/TestModel');
        $metadataCommand->handle();

        /** @var MakeModel $modelCommand */
        $modelCommand = $this->getCommand(MakeModel::class);
        $this->input->setArgument('modelName', 'Test/TestModel');
        $modelCommand->handle();

        $this->loadModel('Test/TestModel');

        Session::getRegistry()->set('id', 1);

        /** @var Rehash $command */
        Schema::setFacadeApplication(new App);
        $command = $this->getCommand(Rehash::class);
        $command->handle();
        $this->assertTrue(true);

        $this->input->setArgument('modelName', null);
        $command->handle();
        $this->assertTrue(true);
    }

}
