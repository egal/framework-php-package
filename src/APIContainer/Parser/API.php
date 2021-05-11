<?php

namespace EgalFramework\APIContainer\Parser;

use EgalFramework\APIContainer\Models\Model;
use EgalFramework\Common\Interfaces\APIContainer\ModelInterface;
use EgalFramework\Common\Interfaces\APIContainer\ParserInterface;
use ReflectionClass;
use ReflectionException;

/**
 * Class API
 * @package EgalFramework\APIContainer\Parser
 */
class API implements ParserInterface
{

    /**
     * @param string $modelName
     * @param bool $fullAccess
     * @return ModelInterface
     * @throws ReflectionException
     */
    public function extract(string $modelName, bool $fullAccess): ModelInterface
    {
        $api = new Model;
        $reflectionClass = new ReflectionClass($modelName);
        if ($reflectionClass->getParentClass()) {
            $api = self::extract($reflectionClass->getParentClass()->getName(), $fullAccess);
        }
        $api->name = $reflectionClass->getShortName();
        /** @TODO optimize: this have no reasons to scan non-magic methods twice */
        $classDescription = new ClassDescription($reflectionClass, $api, $fullAccess);
        $classDescription->extract();
        return $api;
    }

}
