<?php

namespace EgalFramework\CommandLine\Tests;

use EgalFramework\CommandLine\Commands\MakeMetadata;
use EgalFramework\CommandLine\Commands\MakeModel;
use EgalFramework\CommandLine\Exceptions\ModelManagerException;
use EgalFramework\CommandLine\ModelManager;
use EgalFramework\CommandLine\Tests\Stubs\RedisFactory;
use EgalFramework\CommandLine\Tests\Stubs\RequestCache;
use EgalFramework\Common\Session;
use Exception;

class ModelManagerTest extends CommandTestCase
{

    protected function setUp(): void
    {
        parent::setUp();
        if (file_exists($this->tmpDir . '/app/models.json')) {
            unlink($this->tmpDir . '/app/models.json');
        }
    }

    /**
     * @throws ModelManagerException
     */
    public function testModelManager()
    {
        $modelManager = new ModelManager;

        $this->assertFalse($modelManager->isRegistered('Test'));
        $this->assertFalse($modelManager->hasMetadata('Test'));

        $modelManager->register('Test', '\\Test\\ModelNamespace', '\\Test\\Metadata\\Namespace');
        $modelManager = new ModelManager;
        $this->assertTrue($modelManager->isRegistered('Test'));
        $this->assertTrue($modelManager->hasMetadata('Test'));

        $this->assertEquals(['Test'], $modelManager->getModels());

        $this->assertEquals('\\Test\\ModelNamespace\\Test', $modelManager->getModelPath('Test'));
        $this->assertEquals('\\Test\\Metadata\\Namespace\\Test', $modelManager->getMetadataPath('Test'));

        $modelManager->unregister('Test');
        $this->assertFalse($modelManager->isRegistered('Test'));
        $this->assertFalse($modelManager->hasMetadata('Test'));

        $modelManager = new ModelManager;
        $this->assertFalse($modelManager->isRegistered('Test'));
        $this->assertFalse($modelManager->hasMetadata('Test'));
    }

    public function testGetModelPath()
    {
        $modelManager = new ModelManager;
        $this->expectException(ModelManagerException::class);
        $modelManager->getModelPath('QQQ');
    }

    public function testGetMetadataPath()
    {
        $modelManager = new ModelManager;
        $this->expectException(ModelManagerException::class);
        $modelManager->getMetadataPath('QQQ');
    }

    public function testCorruptedJSON()
    {
        file_put_contents(Session::getRegistry()->get('AppPath') . '/models.json', '{"bad123');
        $this->expectException(ModelManagerException::class);
        new ModelManager;
    }

    /**
     * @throws Exception
     */
    public function testGetModelFiles()
    {
        /** @var MakeMetadata $command */
        $command = $this->getCommand(MakeMetadata::class);
        $this->input->setArgument('modelName', 'Test/TestModel');
        $command->handle();

        /** @var MakeModel $command */
        $command = $this->getCommand(MakeModel::class);
        $this->loadMetadata('Test/TestModel');
        $command->handle();
        $this->assertEquals(['Test/TestModel'], Session::getModelManager()->getModelFiles());

        /** @var MakeMetadata $command */
        $command = $this->getCommand(MakeMetadata::class);
        $this->input->setArgument('modelName', 'TestModel');
        $command->handle();

        /** @var MakeModel $command */
        $command = $this->getCommand(MakeModel::class);
        $this->loadMetadata('TestModel');
        $command->handle();
        $this->assertEquals(['TestModel'], Session::getModelManager()->getModelFiles());
    }

    public function testFlushCache()
    {
        $modelManager = new ModelManager;
        Session::setRequestCache(new RequestCache(new RedisFactory));
        $modelManager->flushCache('Test', 1);

        // @TODO implement
        $this->assertTrue(true);
    }

}
