<?php

namespace Egal\Model;

use ReflectionClass;

abstract class EnumModel
{

    public static function actionGetItems(): array
    {
        $reflectionClass = new ReflectionClass(get_called_class());
        return $reflectionClass->getConstants();
    }

}
