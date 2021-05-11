<?php

namespace EgalFramework\CommandLine\Tests\Stubs;

use EgalFramework\Common\Interfaces\ModelInterface;

/**
 * @method mixed find(int $id)
 */
class Model implements ModelInterface
{

    public int $id;

    public function __call($name, $arguments)
    {
        // TODO: Implement @method mixed find(int $id)
    }

    public function newModelQuery()
    {
        return new ModelQuery;
    }

}
