<?php

namespace EgalFramework\CommandLine\Tests\Commands;

use EgalFramework\CommandLine\Commands\MakeMetadata;
use EgalFramework\CommandLine\Commands\MakeModel;
use EgalFramework\CommandLine\Commands\RegisterModel;
use EgalFramework\CommandLine\Tests\CommandTestCase;
use EgalFramework\Common\Session;

class RegisterModelTest extends CommandTestCase
{

    public function testModelRegister()
    {
        /** @var RegisterModel $command */
        $command = $this->getCommand(RegisterModel::class);
        $this->input->setArgument('modelName', 'TestModel1');
        $command->handle();
        $data = json_decode(file_get_contents(Session::getRegistry()->get('AppPath') . '/models.json'), true);
        $this->assertEquals(
            [
                'models' => [
                    'TestModel1' => '\\App\\PublicModels\\TestModel1',
                ],
                'metadata' => [
                    'TestModel1' => '\\App\\Metadata\\TestModel1',
                ],
            ],
            $data
        );
    }

    public function testRegAll()
    {
        /** @var MakeMetadata $command */
        $command = $this->getCommand(MakeMetadata::class);
        $this->input->setArgument('modelName', 'Test/TestModel');
        $command->handle();

        /** @var MakeModel $command */
        $command = $this->getCommand(MakeModel::class);
        $this->loadMetadata('Test/TestModel');
        $command->handle();

        $this->loadModel('Test/TestModel');

        /** @var RegisterModel $command */
        $command = $this->getCommand(RegisterModel::class);
        $this->input->setArgument('modelName', null);
        $command->handle();
        $data = json_decode(file_get_contents(Session::getRegistry()->get('AppPath') . '/models.json'), true);
        $this->assertEquals(
            [
                'models' => [
                    'Test/TestModel' => '\\App\\PublicModels\\Test\\TestModel',
                ],
                'metadata' => [
                    'Test/TestModel' => '\\App\\Metadata\\Test\\TestModel',
                ],
            ],
            $data
        );
    }

}
