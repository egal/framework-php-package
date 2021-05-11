<?php

namespace EgalFramework\Metadata\Tests\Samples;

use EgalFramework\Common\Interfaces\APIContainer\MethodInterface;
use EgalFramework\Common\Interfaces\APIContainer\ModelInterface;
use EgalFramework\Common\Interfaces\APIContainer\StorageInterface;

class APIStorage implements StorageInterface
{

    public function save(ModelInterface $class)
    {
        // TODO: Implement save() method.
    }

    public function getClass(string $model)
    {
        return new ModelClass;
    }

    public function saveClass(ModelInterface $class)
    {
        // TODO: Implement saveClass() method.
    }

    public function getMethod(string $model, string $method)
    {
        // TODO: Implement getMethod() method.
    }

    public function saveMethod(ModelInterface $class, MethodInterface $method)
    {
        // TODO: Implement saveMethod() method.
    }

    public function removeMethod(string $model, string $method)
    {
        // TODO: Implement removeMethod() method.
    }

    public function removeClass(string $model)
    {
        // TODO: Implement removeClass() method.
    }

    public function removeAll()
    {
        // TODO: Implement removeAll() method.
    }

}
