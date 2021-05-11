<?php

namespace Egal\Model\Traits;

use Egal\Model\ModelManager;
use Egal\Model\Metadata\ModelMetadata;
use ReflectionException;

/**
 * @package Egal\Model
 */
trait UsesModelMetadata
{

    /**
     * @return ModelMetadata
     * @throws ReflectionException
     */
    public function getModelMetadata(): ModelMetadata
    {
        return ModelManager::getInstance()->getModelMetadata(static::class);
    }

    /**
     * @return array
     * @throws ReflectionException
     */
    public function getValidationRules(): array
    {
        return $this->getModelMetadata()->getValidationRules();
    }

}
