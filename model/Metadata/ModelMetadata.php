<?php

namespace Egal\Model\Metadata;

use Egal\Auth\Accesses\PermissionAccess;
use Egal\Auth\Accesses\RoleAccess;
use Egal\Auth\Accesses\ServiceAccess;
use Egal\Auth\Accesses\StatusAccess;
use Egal\Model\Exceptions\ModelMetadataException;
use Exception;
use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\DocBlock\Tags\Generic;
use phpDocumentor\Reflection\DocBlock\Tags\Property;
use phpDocumentor\Reflection\DocBlockFactory;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;

/**
 * @package Egal\Model
 */
class ModelMetadata
{

    protected string $modelClass;
    protected string $modelShortName;
    protected array $databaseFields = []; # TODO: Получать из $this->validationRules с выборкой именно fields
    protected array $fieldsWithTypes = [];
    protected array $fakeFields = [];
    protected array $relations = [];
    protected array $validationRules = [];
    private array $primaryKeys = [];

    /**
     * @var ModelActionMetadata[]
     */
    protected array $actionsMetadata = [];

    public function toArray(): array
    {
        $result = [];
        $result['model_class'] = $this->modelClass;
        $result['model_short_name'] = $this->modelShortName;
        $result['database_fields'] = $this->databaseFields;
        $result['fields_with_types'] = $this->fieldsWithTypes;
        $result['fake_fields'] = $this->fakeFields;
        $result['relations'] = $this->relations;
        $result['validation_rules'] = $this->validationRules;
        $result['primary_keys'] = $this->primaryKeys;
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
        $this->scanActionsFromReflectionClass($modelReflectionClass);
        $this->databaseFields = array_keys($this->validationRules);
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
     */
    public function getDatabaseFields(): array
    {
        return $this->databaseFields;
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
                    $this->databaseFields[] = $property->getVariableName();
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
                $statusesAccess = [];
                $servicesAccess = [];
                $rolesAccess = [];
                $permissionsAccess = [];
                /** @var Generic $actionTag */
                foreach ($tag->getDescription()->getTags() as $actionTag) {
                    switch ($actionTag->getName()) {
                        case StatusAccess::TAG:
                            $statusesAccess = explode(',', $actionTag->getDescription());
                            break;
                        case ServiceAccess::TAG:
                            $servicesAccess = explode(',', $actionTag->getDescription());
                            break;
                        case RoleAccess::TAG:
                            $rolesAccess = explode(',', $actionTag->getDescription());
                            break;
                        case PermissionAccess::TAG:
                            $permissionsAccess = explode(',', $actionTag->getDescription());
                            break;
                    }
                }
                $this->addActionMetadata(new ModelActionMetadata(
                    $actionCurrentName,
                    $reflectionMethod->getParameters(),
                    $statusesAccess,
                    $rolesAccess,
                    $permissionsAccess,
                    $servicesAccess
                ));
            }
        }
    }

    protected function addActionMetadata(ModelActionMetadata $modelActionMetadata): void
    {
        if (!isset($this->actionsMetadata[$modelActionMetadata->getActionName()])) {
            $this->actionsMetadata[$modelActionMetadata->getActionName()] = $modelActionMetadata;
        }
    }

    protected function scanActionsFromReflectionClass(ReflectionClass $reflectionClass): void
    {
        foreach ($reflectionClass->getMethods(ReflectionMethod::IS_STATIC) as $reflectionMethod) {
            $actionName = $reflectionMethod->getName();
            if (!str_contains($actionName, ModelActionMetadata::PREFIX)) continue;
            if ($this->hasActionMetadata($actionName)) continue;

            $statusesAccess = [];
            $rolesAccess = [];
            $permissionsAccess = [];
            $servicesAccess = [];

            $docComment = $reflectionMethod->getDocComment();
            if ($docComment) {
                $docBlock = DocBlockFactory::createInstance()->create($docComment);
                /** @var Generic $actionTag */
                foreach ($docBlock->getTags() as $actionTag) {
                    switch ($actionTag->getName()) {
                        case StatusAccess::TAG:
                            $statusesAccess = explode(',', $actionTag->getDescription());
                            break;
                        case ServiceAccess::TAG:
                            $servicesAccess = explode(',', $actionTag->getDescription());
                            break;
                        case RoleAccess::TAG:
                            $rolesAccess = explode(',', $actionTag->getDescription());
                            break;
                        case PermissionAccess::TAG:
                            $permissionsAccess = explode(',', $actionTag->getDescription());
                            break;
                    }
                }
            }

            $this->addActionMetadata(new ModelActionMetadata(
                $actionName,
                $reflectionMethod->getParameters(),
                $statusesAccess,
                $rolesAccess,
                $permissionsAccess,
                $servicesAccess
            ));
        }
    }

    protected function hasActionMetadata(string $actionName): bool
    {
        return isset($this->actionsMetadata[ModelActionMetadata::getCurrentActionName($actionName)]);
    }

    /**
     * @return array
     */
    public function getValidationRules(): array
    {
        return $this->validationRules;
    }

    /**
     * @param string $actionName
     * @return ModelActionMetadata
     * @throws Exception
     */
    public function getAction(string $actionName): ModelActionMetadata
    {
        $actionName = ModelActionMetadata::getCurrentActionName($actionName);
        if ($this->hasActionMetadata($actionName)) {
            return $this->actionsMetadata[$actionName];
        }

        throw new ModelMetadataException(
            $actionName . ' does not exist in the model' . $this->modelClass . '!'
        );
    }

    public function databaseFieldExists(string $string): bool
    {
        return in_array($string, $this->databaseFields);
    }

}
