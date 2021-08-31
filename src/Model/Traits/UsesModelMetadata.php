<?php

declare(strict_types=1);

namespace Egal\Model\Traits;

use Egal\Model\Metadata\ModelMetadata;
use Egal\Model\ModelManager;

/**
 * @package Egal\Model
 */
trait UsesModelMetadata
{

    /**
     * @throws \ReflectionException
     */
    public function getModelMetadata(): ModelMetadata
    {
        return ModelManager::getModelMetadata(static::class);
    }

    /**
     * @return array[]
     * @throws \ReflectionException
     */
    protected function getValidationRules(): array
    {
        return $this->getModelMetadata()->getValidationRules();
    }

    /**
     * @param string[] $validationRules
     * @return $this
     * @throws \ReflectionException
     */
    protected function setValidationRules(array $validationRules): self
    {
        $this->getModelMetadata()->setValidationRules($validationRules);

        return $this;
    }

    /**
     * @return $this
     * @throws \ReflectionException
     */
    protected function addValidationRules(string $propertyName, string ...$validationRules): self
    {
        $this->getModelMetadata()->addValidationRules($propertyName, ...$validationRules);

        return $this;
    }

}
