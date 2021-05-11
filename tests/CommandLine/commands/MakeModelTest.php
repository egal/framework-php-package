<?php

namespace EgalFramework\CommandLine\Tests\Commands;

use EgalFramework\CommandLine\Commands\MakeMetadata;
use EgalFramework\CommandLine\Commands\MakeModel;
use EgalFramework\CommandLine\Tests\CommandTestCase;
use EgalFramework\Common\Session;
use Exception;

class MakeModelTest extends CommandTestCase
{

    /**
     * @throws Exception
     */
    public function testModel()
    {
        /** @var MakeMetadata $metadataCommand */
        $metadataCommand = $this->getCommand(MakeMetadata::class);
        $this->input->setArgument('modelName', 'Test/TestModel1');
        $metadataCommand->handle();
        $path = Session::getRegistry()->get('AppPath') . '/Metadata/Test/TestModel1.php';
        file_put_contents($path, str_replace(
            '\'hash\' => (new Field(FieldType::STRING, \'hash\'))',
            '\'json_field\' => new Field(FieldType::JSON, \'json_field\'),' . PHP_EOL
            . '\'hash\' => (new Field(FieldType::STRING, \'hash\'))',
            file_get_contents($path)
        ));
        $this->loadMetadata('Test/TestModel1');

        /** @var MakeModel $command */
        $command = $this->getCommand(MakeModel::class);
        $command->handle();
        $this->assertEquals(
            $this->getAsset('Model1.php'),
            file_get_contents(Session::getRegistry()->get('AppPath') . '/PublicModels/Test/TestModel1.php')
        );

        $this->expectExceptionMessage('Model is already exist');
        $command->handle();
    }

}
