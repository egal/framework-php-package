<?php

namespace EgalFramework\Metadata\Tests\Samples;

use EgalFramework\Common\Interfaces\APIContainer\MethodInterface;
use EgalFramework\Common\Interfaces\APIContainer\ModelInterface;

class ModelClass implements ModelInterface
{

    public function getMethods(): array
    {
        return [];
    }

    public function getMethod(string $name): ?MethodInterface
    {
        // TODO: Implement getMethod() method.
    }

    public function setMethod(string $name, MethodInterface $method)
    {
        // TODO: Implement setMethod() method.
    }

    public function removeMethod(string $name)
    {
        // TODO: Implement removeMethod() method.
    }

    public function toString(): string
    {
        // TODO: Implement toString() method.
    }

    public function keySortMethods()
    {
        // TODO: Implement keySortMethods() method.
    }

}
