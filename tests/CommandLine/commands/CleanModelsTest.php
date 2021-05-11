<?php

namespace EgalFramework\CommandLine\Tests\Commands;

use EgalFramework\CommandLine\Commands\CleanModels;
use EgalFramework\CommandLine\Commands\MakeMetadata;
use EgalFramework\CommandLine\Commands\MakeModel;
use EgalFramework\CommandLine\ModelManager;
use EgalFramework\CommandLine\Tests\CommandTestCase;
use EgalFramework\Common\Session;

class CleanModelsTest extends CommandTestCase
{

    public function testClean()
    {
        /** @var MakeMetadata $command */
        $command = $this->getCommand(MakeMetadata::class);
        $this->input->setArgument('modelName', 'Test/TestModel');
        $command->handle();

        /** @var MakeModel $command */
        $command = $this->getCommand(MakeModel::class);
        $this->loadMetadata('Test/TestModel');
        $command->handle();

        $data = json_decode(file_get_contents(Session::getRegistry()->get('AppPath') . '/models.json'), true);
        $data['models']['FakeModel'] = '\\EgalFramework\\FakeModel';
        $data['metadata']['FakeMetadata'] = '\\EgalFramework\\FakeMetadata';
        file_put_contents(Session::getRegistry()->get('AppPath') . '/models.json', json_encode($data));
        $this->loadModel('Test/TestModel');

        Session::setModelManager(new ModelManager);

        /** @var CleanModels $command */
        $command = $this->getCommand(CleanModels::class);
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
