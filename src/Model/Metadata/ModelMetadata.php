<?php

declare(strict_types=1);

namespace Egal\Model\Metadata;

use Egal\Model\Exceptions\ActionNotFoundException;
use Egal\Model\Exceptions\DuplicatePrimaryKeyModelMetadataException;
use Egal\Model\Exceptions\FieldNotFoundException;
use Egal\Model\Exceptions\IncorrectCaseOfPropertyVariableNameException;
use Egal\Model\Exceptions\ModelMetadataException;
use Egal\Model\Exceptions\RelationNotFoundException;
use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\DocBlockFactory;
use ReflectionClass;

/**
 * TODO: Получать из $this->validationRules с выборкой именно fields
 */
class ModelMetadata
{

    protected string $modelClass;

    protected string $modelShortName;

    /**
     * @var string[]
     * @deprecated from v2.0.0, use {@see ModelMetadata::$fields}.
     */
    protected array $databaseFields = [];

    /**
     * @var string[]
     */
    protected array $fields = [];

    /**
     * @var mixed[]
     */
    protected array $fieldsWithTypes = [];

    /**
     * @var string[]
     */
    protected array $fakeFields = [];

    /**
     * @var string[]
     */
    protected array $relations = [];

    /**
     * @var string[]
     */
    protected array $validationRules = [];

    /**
     * @var \Egal\Model\Metadata\ModelActionMetadata[]
     */
    protected array $actionsMetadata = [];

    private ?string $primaryKey = null;

    /**
     * @throws \ReflectionException
     * @throws \Exception
     */
    public function __construct(string $modelClass)
    {
        $this->modelClass = $modelClass;
        $modelReflectionClass = new ReflectionClass($this->modelClass);
        $this->modelShortName = $modelReflectionClass->getShortName();
        $docComment = $modelReflectionClass->getDocComment();

        if (!$docComment) {
            return;
        }

        $docBlock = DocBlockFactory::createInstance()->create($docComment);
        $this->scanProperties($docBlock);
        $this->scanActionsFromClassDocBlock($modelReflectionClass, $docBlock);
    }

    /**
     * TODO: Remove 'database_fields' from v2.0.0.
     * TODO: Remove 'primary_keys' from v2.0.0.
     *
     * @return mixed[]
     * @throws \ReflectionException
     */
    public function toArray(): array
    {
        $result = [
            'model_class' => $this->modelClass,
            'model_short_name' => $this->modelShortName,
            'database_fields' => $this->fields,
            'fields' => $this->fields,
            'fields_with_types' => $this->fieldsWithTypes,
            'fake_fields' => $this->fakeFields,
            'relations' => $this->relations,
            'validation_rules' => $this->validationRules,
            'primary_keys' => $this->getPrimaryKeys(),
            'actions_metadata' => [],
        ];

        foreach ($this->actionsMetadata as $key => $actionMetadata) {
            $result['actions_metadata'][$key] = $actionMetadata->toArray();
        }

        return $result;
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
     * @depricated from v2.0.0
     */
    public function getPrimaryKeys(): array
    {
        return [$this->primaryKey];
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

    public function getModelShortName(): string
    {
        return $this->modelShortName;
    }

    public function getModelClass(): string
    {
        return $this->modelClass;
    }

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
     * @param mixed[] $validationRules
     * @return $this
     */
    public function setValidationRules(array $validationRules): self
    {
        $this->validationRules = $validationRules;

        return $this;
    }

    /**
     * @return $this
     */
    public function addValidationRules(string $propertyName, string ...$propertyValidationRules): ModelMetadata
    {
        $this->validationRules[$propertyName] = isset($this->validationRules[$propertyName])
            ? array_merge($this->validationRules[$propertyName], $propertyValidationRules)
            : $propertyValidationRules;

        return $this;
    }

    /**
     * @throws \Egal\Model\Exceptions\ActionNotFoundException
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

    public function relationExist(string $relation): bool
    {
        return in_array($relation, $this->relations);
    }

    /**
     * @throws \Egal\Model\Exceptions\RelationNotFoundException
     */
    public function relationExistOrFail(string $relation): bool
    {
        if (!$this->relationExist($relation)) {
            throw new RelationNotFoundException();
        }

        return true;
    }

    public function getPrimaryKey(): ?string
    {
        return $this->primaryKey;
    }

    protected function scanActions(): void
    {
        // TODO: Implement functionality!
    }

    /**
     * Разбирает все property в phpDoc и отбирает field, relation и правила валидации
     *
     * @throws \Egal\Model\Exceptions\DuplicatePrimaryKeyModelMetadataException
     * @throws \Egal\Model\Exceptions\IncorrectCaseOfPropertyVariableNameException
     */
    protected function scanProperties(DocBlock $docBlock): void
    {
        /** @var \phpDocumentor\Reflection\DocBlock\Tags\Property $property */
        foreach ($docBlock->getTagsByName('property') as $property) {
            $propertyTags = $property->getDescription()->getTags();

            if ($property->getVariableName() !== snake_case($property->getVariableName())) {
                throw IncorrectCaseOfPropertyVariableNameException::make(
                    $this->modelClass,
                    $property->getVariableName()
                );
            }

            /** @var \phpDocumentor\Reflection\DocBlock\Tags\Generic $propertyTag */
            foreach ($propertyTags as $propertyTag) {
                $bodyTemplate = $propertyTag->getDescription()
                    ? $propertyTag->getDescription()->getBodyTemplate()
                    : '';
                $tagName = $propertyTag->getName();

                switch ($tagName) {
                    case 'validation-rules':
                        $this->validationRules[$property->getVariableName()] = explode('|', $bodyTemplate);
                        break;
                    case 'primary-key':
                        if (isset($this->primaryKey)) {
                            throw new DuplicatePrimaryKeyModelMetadataException();
                        }

                        if ($property->getVariableName()) {
                            $this->primaryKey = (string) $property->getVariableName();
                        }

                        break;
                    case 'property-type':
                        if ($bodyTemplate === 'field') {
                            $this->fields[] = $property->getVariableName();
                            $this->fieldsWithTypes[$property->getVariableName()] = $property->getType();
                        } elseif ($bodyTemplate === 'relation') {
                            $this->relations[] = $property->getVariableName();
                        }

                        break;
                    default:
                        break;
                }
            }
        }
    }

    /**
     * @throws \Egal\Model\Exceptions\ModelActionMetadataException|\Egal\Model\Exceptions\ModelMetadataException
     */
    protected function scanActionsFromClassDocBlock(ReflectionClass $modelReflectionClass, DocBlock $docBlock): void
    {
        /** @var \phpDocumentor\Reflection\DocBlock\Tags\Generic $tag */
        foreach ($docBlock->getTagsByName('action') as $tag) {
            $actionName = $tag->getDescription()->getBodyTemplate();
            $actionName = str_replace(
                [' ', '%', '$', '1', '2', '3', '4', '5', '6', '7', '8', '9', '10'],
                '#',
                $actionName
            );
            $actionNameExtra = stristr($actionName, '#');
            $actionName = str_replace($actionNameExtra, '', $actionName);
            $actionCurrentName = ModelActionMetadata::getCurrentActionName($actionName);

            if (!$modelReflectionClass->hasMethod($actionCurrentName)) {
                continue;
            }

            $reflectionMethod = $modelReflectionClass->getMethod($actionCurrentName);

            if (!$reflectionMethod->isStatic()) {
                throw new ModelMetadataException('All actions methods of the model must be static!');
            }

            $modelActionMetadata = new ModelActionMetadata();
            $modelActionMetadata->setActionName($actionName);
            $modelActionMetadata->setParameters($reflectionMethod->getParameters());
            /** @var \phpDocumentor\Reflection\DocBlock\Tags\Generic $actionTag */
            foreach ($tag->getDescription()->getTags() as $actionTag) {
                $modelActionMetadata->supplementFromTag($actionTag);
            }
            $this->addActionMetadata($modelActionMetadata);
        }
    }

    protected function addActionMetadata(ModelActionMetadata $modelActionMetadata): void
    {
        if (isset($this->actionsMetadata[$modelActionMetadata->getActionName()])) {
            return;
        }

        $this->actionsMetadata[$modelActionMetadata->getActionName()] = $modelActionMetadata;
    }

}