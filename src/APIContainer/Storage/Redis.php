<?php

namespace EgalFramework\APIContainer\Storage;

use EgalFramework\APIContainer\Models\Model;
use EgalFramework\APIContainer\Models\Method;
use EgalFramework\Common\Interfaces\APIContainer\MethodInterface;
use EgalFramework\Common\Interfaces\APIContainer\StorageInterface;
use EgalFramework\Common\Interfaces\APIContainer\ModelInterface;
use Illuminate\Redis\RedisManager;
use Redis as RedisClient;

class Redis implements StorageInterface
{

    /** @var RedisManager|RedisClient */
    private $redisManager;

    /** @var string */
    private $path;

    /**
     * API constructor.
     * @param RedisManager $redisManager
     * @param string $path
     */
    public function __construct(RedisManager $redisManager, string $path)
    {
        $this->redisManager = $redisManager;
        $this->path = rtrim($path, ':');
    }

    /**
     * @param ModelInterface $class
     */
    public function save(ModelInterface $class)
    {
        $this->saveClass($class);
        foreach ($class->getMethods() as $method) {
            $this->saveMethod($class, $method);
        }
    }

    /**
     * Get class description
     * @param string $model
     * @return ModelInterface|NULL
     */
    public function getClass(string $model): ?ModelInterface
    {
        $data = $this->redisManager->get($this->path . ':' . $model . ':description');
        if (!$data) {
            return NULL;
        }
        $obj = new Model();
        $obj->fromString($data);
        $methods = $this->redisManager->keys($this->path . ':' . $model . ':methods:*');
        foreach ($methods as $method) {
            $method = explode(':', $method);
            $obj->setMethod($method[count($method) - 1], $this->getMethod($model, $method[count($method) - 1]));
        }
        return $obj;
    }

    /**
     * Save class description
     * @param ModelInterface $class
     */
    public function saveClass(ModelInterface $class)
    {
        $this->redisManager->set($this->path . ':' . $class->name . ':description', $class->toString());
    }

    /**
     * Get method
     * @param string $model
     * @param string $method
     * @return MethodInterface|NULL
     */
    public function getMethod(string $model, string $method): ?MethodInterface
    {
        $data = $this->redisManager->get($this->path . ':' . $model . ':methods:' . $method);
        if ($data) {
            $method = new Method;
            $method->fromString($data);
            return $method;
        }
        return NULL;
    }

    /**
     * Save method
     * @param ModelInterface $class
     * @param MethodInterface $method
     */
    public function saveMethod(ModelInterface $class, MethodInterface $method)
    {
        $this->redisManager->set($this->path . ':' . $class->name . ':methods:' . $method->name, $method->toString());
    }

    /**
     * Remove method
     * @param string $model
     * @param string $method
     * @codeCoverageIgnore
     */
    public function removeMethod(string $model, string $method)
    {
        $this->redisManager->del([$this->path . ':' . $model . ':methods:' . $method]);
    }

    /**
     * Remove class at all
     * @param string $model
     * @codeCoverageIgnore
     */
    public function removeClass(string $model)
    {
        $this->redisManager->del([$this->path . ':' . $model . ':description']);
        if ($keys = $this->redisManager->keys($this->path . ':' . $model . '*')) {
            $this->redisManager->del($keys);
        }
    }

    /**
     * Remove all API data
     * @codeCoverageIgnore
     */
    public function removeAll()
    {
        if ($keys = $this->redisManager->keys($this->path . ':*')) {
            $this->redisManager->del($keys);
        }
    }

}
