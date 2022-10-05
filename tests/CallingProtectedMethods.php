<?php

namespace Egal\Tests;

use ReflectionClass;
use ReflectionException;
use ReflectionMethod;

/**
 * Trait CallingProtectedMethods
 * @package Egal\Tests
 */
trait CallingProtectedMethods
{

    /**
     * @param string $class
     * @param string|object $name
     * @return ReflectionMethod
     * @throws ReflectionException
     */
    private function getGuardedMethod($class, string $name): ReflectionMethod
    {
        $class = is_object($class) ? get_class($class) : $class;
        $class = new ReflectionClass($class);
        $method = $class->getMethod($name);
        $method->setAccessible(true);
        return $method;
    }

    /**
     * @param object $object
     * @param string $methodName
     * @param mixed ...$parameters
     * @return mixed
     * @throws ReflectionException
     */
    protected function callGuardedMethod(object $object, string $methodName, ...$parameters)
    {
        return $this->getGuardedMethod($object, $methodName)->invoke($object, ...$parameters);
    }

}
