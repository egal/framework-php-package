<?php

declare(strict_types=1);

namespace Egal\Model\Traits;

use Egal\Model\Enums\VariableType;
use Egal\Model\Facades\ModelMetadataManager;
use Egal\Model\Metadata\FieldMetadata;
use Egal\Model\Metadata\ModelMetadata;
use Egal\Model\Model;

/**
 * @package Egal\Model
 */
trait UsesModelMetadata
{

    private ModelMetadata $modelMetadata;

    private array $validationRules = [];

    private readonly string $keyName;

    public function initializeUsesModelMetadata(): void
    {
        $this->modelMetadata = ModelMetadataManager::getModelMetadata(static::class);
        $this->keyType = $this->modelMetadata->getKey()->getType()->value;
        $this->keyName = $this->modelMetadata->getKey()->getName();

        $this->setKeyProperties();
        $this->setValidationRules();
        $this->setDefaultAttributes();
        $this->mergeCasts($this->modelMetadata->getCasts());

        $this->mergeGuarded($this->modelMetadata->getGuardedFieldsNames());
        $this->makeHidden($this->modelMetadata->getHiddenFieldsNames());
    }

    private function setKeyProperties(): void
    {
        switch ($this->keyType) {
            case VariableType::UUID->value:
                $this->mergeCasts(['id' => 'string']);
            case VariableType::INTEGER->value:
                $this->incrementing = true;
                return;
            default:
                $this->incrementing = false;
        }
    }

    private function setDefaultAttributes(): void
    {
        static::creating(static function (Model $model): void {
            foreach ($model->getModelMetadata()->getFields() as $field) {
                if ($field->getDefault() === null) {
                    continue;
                }

                if (!$model->getAttribute($field->getName())) {
                    $model->setAttribute($field->getName(), $field->getDefault());
                }
            }
        });
    }

    public abstract static function constructMetadata(): ModelMetadata;

    private function setValidationRules(): void
    {
        $this->setValidationRule($this->modelMetadata->getKey());

        foreach ($this->modelMetadata->getFields() as $field) {
            $this->setValidationRule($field);
        }

        foreach ($this->modelMetadata->getFakeFields() as $field) {
            $this->setValidationRule($field);
        }
    }

    private function setValidationRule(FieldMetadata $field): void
    {
        $this->validationRules[$field->getName()] = $field->getValidationRules();
    }

    public final function getModelMetadata(): ModelMetadata
    {
        return ModelMetadataManager::getModelMetadata(static::class);
    }

    public function getValidationRules(): array
    {
        return $this->validationRules;
    }

    public function getKeyType(): string
    {
        return $this->keyType;
    }

    public function getIncrementing(): bool
    {
        return $this->incrementing;
    }

}
