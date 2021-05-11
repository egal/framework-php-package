<?php

namespace EgalFramework\APIContainer\Tests;

use EgalFramework\APIContainer\Models\Method;
use EgalFramework\APIContainer\Models\Model;
use EgalFramework\APIContainer\Storage\Redis;
use EgalFramework\Common\Interfaces\APIContainer\MethodInterface;
use EgalFramework\Common\Interfaces\APIContainer\ModelInterface;
use EgalFramework\Common\Interfaces\APIContainer\StorageInterface;
use Illuminate\Redis\RedisManager;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use ReflectionMethod;

class StorageTest extends TestCase
{

    /** @var StorageInterface */
    private $storage;

    /** @var ModelInterface */
    private ModelInterface $sampleModel;

    /** @var MethodInterface */
    private MethodInterface $sampleMethod;

    /**
     * @throws ReflectionException
     */
    protected function setUp(): void
    {
        parent::setUp();
        $builder = $this->getMockBuilder(RedisManager::class);
        $redisManager = $builder
            ->setMethods(['set', 'get', 'keys'])
            ->disableOriginalConstructor()
            ->getMock();
        $redisManager
            ->method('set')
            ->willReturn(true);
        $this->sampleModel = new Model;
        $this->sampleModel->name = 'model';
        $this->sampleMethod = new Method;
        $this->sampleMethod->name = 'methodName';
        $this->sampleModel->setMethod($this->sampleMethod->name, $this->sampleMethod);
        $callback = new ReflectionMethod($this, 'getFakeRedisData');
        $redisManager
            ->method('get')
            ->will($this->returnCallback($callback->getClosure($this)));
        $callback = new ReflectionMethod($this, 'getFakeKeysData');
        $redisManager
            ->method('keys')
            ->will($this->returnCallback($callback->getClosure($this)));
        $this->storage = new Redis($redisManager, 'test_path');
    }

    /**
     * @param string $path
     * @return string|null
     * @noinspection PhpUnusedPrivateMethodInspection
     */
    private function getFakeRedisData(string $path)
    {
        if (preg_match('/NoClass/', $path)) {
            return null;
        } elseif (preg_match('/methods/', $path)) {
            return $this->sampleMethod->toString();
        } else {
            return $this->sampleModel->toString();
        }
    }

    /**
     * @param string $path
     * @return array
     * @noinspection PhpUnusedPrivateMethodInspection
     */
    private function getFakeKeysData(string $path)
    {
        return [$this->sampleMethod->toString()];
    }

    public function testSave()
    {
        $this->storage->save($this->sampleModel);
        $this->assertEquals($this->sampleModel->toString(), $this->storage->getClass('model')->toString());
    }

    public function testSaveMethod()
    {
        $this->storage->saveMethod($this->sampleModel, $this->sampleMethod);
        $this->assertEquals(
            $this->sampleMethod->toString(),
            $this->storage->getMethod($this->sampleModel->name, $this->sampleMethod->name)->toString()
        );
    }

    public function testLoad()
    {
        $this->assertNull($this->storage->getClass('NoClass'));
        $this->assertNull($this->storage->getMethod('NoClass', 'NoMethod'));
    }

}
