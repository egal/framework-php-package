<?php

namespace EgalFramework\Metadata\Tests\Samples;

use EgalFramework\Common\Interfaces\APIContainer\MethodInterface;
use EgalFramework\Common\Interfaces\APIContainer\ModelInterface;

class Model implements ModelInterface
{

    public string $name;

    private array $methods;

    public function getMethods(): array
    {
        return isset($this->methods)
            ? $this->methods
            : [];
    }

    public function getMethod(string $name): ?MethodInterface
    {
        return $this->methods[$name];
    }

    public function setMethod(string $name, MethodInterface $method)
    {
        $this->methods[$name] = $method;
    }

    public function removeMethod(string $name)
    {
        unset($this->methods[$name]);
    }

    public function toString(): string
    {
        return '';
    }

    public function keySortMethods()
    {
        ksort($this->methods);
    }

}
