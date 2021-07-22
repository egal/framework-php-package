<?php

namespace Egal\Tests;

use ReflectionClass;
use ReflectionException;

class PHPUnitUtil
{

    /**
     * @param object $object
     * @param string $methodName
     * @param mixed ...$parameters
     * @return mixed
     * @throws ReflectionException
     */
    public static function callMethod(object $object, string $methodName, ...$parameters)
    {
        $refClass = new ReflectionClass($object);
        $refMethod = $refClass->getMethod($methodName);
        $refMethod->setAccessible(true);
        return $refMethod->invoke($object, ...$parameters);
    }

    public static function getProperty(object $object, string $propertyName)
    {
        $reflectedClass = new ReflectionClass($object);
        $reflection = $reflectedClass->getProperty($propertyName);
        $reflection->setAccessible(true);
        return $reflection->getValue($object);
    }

    public static function setProperty(object $object, string $propertyName, $propertyValue)
    {
        $refClass = new ReflectionClass($object);
        $refProperty = $refClass->getProperty($propertyName);
        $refProperty->setAccessible(true);
        $refProperty->setValue($object, $propertyValue);
    }

}
