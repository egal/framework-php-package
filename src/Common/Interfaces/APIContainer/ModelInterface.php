<?php

namespace EgalFramework\Common\Interfaces\APIContainer;

interface ModelInterface
{

    public function getMethods(): array;

    public function getMethod(string $name): ?MethodInterface;

    public function setMethod(string $name, MethodInterface $method);

    public function removeMethod(string $name);

    public function toString(): string;

    public function keySortMethods();

}
