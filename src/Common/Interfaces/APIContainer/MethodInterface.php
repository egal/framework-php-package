<?php

namespace EgalFramework\Common\Interfaces\APIContainer;

/**
 * Interface MethodInterface
 * @package EgalFramework\Common\Interfaces\APIContainer
 *
 * @property ArgumentInterface[] $arguments
 */
interface MethodInterface
{

    public function toString(): string;

    public function getRoles(): array;

}
