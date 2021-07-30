<?php

namespace Egal\Model\Metadata;

use Egal\Model\Exceptions\ActionNotFoundException;
use Egal\Model\Exceptions\FieldNotFoundException;
use Egal\Model\Exceptions\ModelMetadataException;
use Exception;
use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\DocBlock\Tags\Generic;
use phpDocumentor\Reflection\DocBlock\Tags\Property;
use phpDocumentor\Reflection\DocBlockFactory;
use ReflectionClass;
use ReflectionException;

/**
 * TODO: Получать из $this->validationRules с выборкой именно fields
 */
class ModelMetadata
{

    protected string $modelClass;
    protected string $modelShortName;

    /**
     * @deprecated from v2.0.0, use {@see ModelMetadata::$fields}.
     * @var string[]
     */
    protected array $databaseFields = [];

    /**
     * @var string[]
     */
    protected array $fields = [];
    protected array $fieldsWithTypes = [];
    protected array $fakeFields = [];
    protected array $relations = [];
    protected array $validationRules = [];
    private array $primaryKeys = [];

    /**
     * @var ModelActionMetadata[]
     */
    protected array $actionsMetadata = [];

    /**
     * @return array
     * @throws ReflectionException
     */
    public function toArray(): array
    {
        $result = [
            'model_class' => $this->modelClass,
            'model_short_name' => $this->modelShortName,
            'database_fields' => $this->fields, # TODO: Remove from v2.0.0.
            'fields' => $this->fields,
            'fields_with_types' => $this->fieldsWithTypes,
            'fake_fields' => $this->fakeFields,
            'relations' => $this->relations,
            'validation_rules' => $this->validationRules,
            'primary_keys' => $this->primaryKeys,
            'actions_metadata' => [],
        ];
        foreach ($this->actionsMetadata as $key => $actionMetadata) {
            $result['actions_metadata'][$key] = $actionMetadata->toArray();
        }
        return $result;
    }

    /**
     * ModelMetadata constructor.
     * @param string $modelClass
     * @throws ReflectionException
     * @throws Exception
     */
    public function __construct(string $modelClass)
    {
        $this->modelClass = $modelClass;
        $modelReflectionClass = new ReflectionClass($this->modelClass);
        $this->modelShortName = $modelReflectionClass->getShortName();
        $docComment = $modelReflectionClass->getDocComment();
        if ($docComment) {
            $docBlock = DocBlockFactory::createInstance()->create($docComment);
            $this->scanProperties($docBlock);
            $this->scanActionsFromClassDocBlock($modelReflectionClass, $docBlock);
        }
        $this->fields = array_keys($this->validationRules);
    }

    /**
     * @return array
     */
    public function getRelations(): array
    {
        return $this->relations;
    }

    /**
     * @return array
     */
    public function getPrimaryKeys(): array
    {
        return $this->primaryKeys;
    }

    /**
     * @return array
     */
    public function getFieldsWithTypes(): array
    {
        return $this->fieldsWithTypes;
    }

    /**
     * @return array
     * @deprecated from v2.0.0, use {@see ModelMetadata::getFields()}
     */
    public function getDatabaseFields(): array
    {
        return $this->fields;
    }

    /**
     * @return string
     */
    public function getModelShortName(): string
    {
        return $this->modelShortName;
    }

    /**
     * @return string
     */
    public function getModelClass(): string
    {
        return $this->modelClass;
    }

    /**
     * @param string $modelClass
     */
    public function setModelClass(string $modelClass): void
    {
        $this->modelClass = $modelClass;
    }

    /**
     * @return string[]
     */
    public function getFields(): array
    {
        return $this->fields;
    }

    protected function scanActions(): void
    {
        # TODO
    }

    /**
     * Разбирает все property в phpDoc и отбирает field, relation и правила валидации
     *
     * @param DocBlock $docBlock
     */
    protected function scanProperties(DocBlock $docBlock): void
    {
        /** @var Property $property */
        foreach ($docBlock->getTagsByName('property') as $property) {
            $propertyTags = $property->getDescription()->getTags();
            /** @var Generic $propertyTag */
            foreach ($propertyTags as $propertyTag) {
                $bodyTemplate = $propertyTag->getDescription()
                    ? $propertyTag->getDescription()->getBodyTemplate()
                    : '';
                $tagName = $propertyTag->getName();

                if ($tagName === 'validation-rules') {
                    $this->validationRules[$property->getVariableName()] = explode('|', $bodyTemplate);
                }

                if ($tagName === 'property-type' && $bodyTemplate === 'field') {
                    $this->fields[] = $property->getVariableName();
                    $this->fieldsWithTypes[$property->getVariableName()] = $property->getType();
                }

                if ($tagName === 'property-type' && $bodyTemplate === 'relation') {
                    $this->relations[] = $property->getVariableName();
                }

                if ($tagName === 'primary-key') {
                    $this->primaryKeys[] = $property->getVariableName();
                }
            }
        }
    }

    /**
     * @param ReflectionClass $modelReflectionClass
     * @param DocBlock $docBlock
     * @throws Exception
     */
    protected function scanActionsFromClassDocBlock(ReflectionClass $modelReflectionClass, DocBlock $docBlock): void
    {
        /** @var Generic $tag */
        foreach ($docBlock->getTagsByName('action') as $tag) {
            $actionName = $tag->getDescription()->getBodyTemplate();
            $actionName = str_replace([' ', '%', '$', '1', '2', '3', '4', '5', '6', '7', '8', '9', '10'], '#', $actionName);
            $actionNameExtra = stristr($actionName, '#');
            $actionName = str_replace($actionNameExtra, '', $actionName);
            $actionCurrentName = ModelActionMetadata::getCurrentActionName($actionName);
            if ($modelReflectionClass->hasMethod($actionCurrentName)) {
                $reflectionMethod = $modelReflectionClass->getMethod($actionCurrentName);
                if (!$reflectionMethod->isStatic()) {
                    throw new ModelMetadataException('All actions methods of the model must be static!');
                }

                $modelActionMetadata = new ModelActionMetadata();
                $modelActionMetadata->setActionName($actionName);
                $modelActionMetadata->setParameters($reflectionMethod->getParameters());
                /** @var Generic $actionTag */
                foreach ($tag->getDescription()->getTags() as $actionTag) {
                    $modelActionMetadata->supplementFromTag($actionTag);
                }
                $this->addActionMetadata($modelActionMetadata);
            }
        }
    }

    protected function addActionMetadata(ModelActionMetadata $modelActionMetadata): void
    {
        if (!isset($this->actionsMetadata[$modelActionMetadata->getActionName()])) {
            $this->actionsMetadata[$modelActionMetadata->getActionName()] = $modelActionMetadata;
        }
    }

    /**
     * @return mixed[]
     */
    public function getValidationRules(?string $propertyName = null): array
    {
        if ($propertyName) {
            $this->fieldExistOrFail($propertyName);
            return $this->validationRules[$propertyName] ?? [];
        }

        return $this->validationRules;
    }

    /**
     * @param array $validationRules
     * @return $this
     */
    public function setValidationRules(array $validationRules): self
    {
        $this->validationRules = $validationRules;
        return $this;
    }

    /**
     * @param string $propertyName
     * @param string ...$propertyValidationRules
     * @return $this
     */
    public function addValidationRules(string $propertyName, string ...$propertyValidationRules): ModelMetadata
    {
        if (isset($this->validationRules[$propertyName])) {
            $this->validationRules[$propertyName] = array_merge(
                $this->validationRules[$propertyName],
                $propertyValidationRules
            );
        } else {
            $this->validationRules[$propertyName] = $propertyValidationRules;
        }

        return $this;
    }

    /**
     * @param string $actionName
     * @return ModelActionMetadata
     * @throws Exception
     */
    public function getAction(string $actionName): ModelActionMetadata
    {
        if (isset($this->actionsMetadata[$actionName])) {
            return $this->actionsMetadata[$actionName];
        }

        throw ActionNotFoundException::make($this->modelClass, $actionName);
    }

    /**
     * @deprecated from 2.0.0, use {@see ModelMetadata::fieldExist()}
     */
    public function databaseFieldExists(string $string): bool
    {
        return $this->fieldExist($string);
    }

    public function fieldExist(string $fieldName): bool
    {
        return in_array($fieldName, $this->fields);
    }

    public function fieldExistOrFail(string $fieldName): bool
    {
        if (!$this->fieldExist($fieldName)) {
            throw new FieldNotFoundException();
        }

        return true;
    }

}
