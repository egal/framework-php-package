<?php

namespace Egal\Model\Metadata;

use Egal\Auth\Accesses\StatusAccess;
use Egal\Model\Exceptions\ModelActionMetadataException;
use Illuminate\Support\Str;
use phpDocumentor\Reflection\DocBlock\Tags\Generic as RefGenericTag;
use ReflectionParameter;

/**
 * @package Egal\Model
 */
class ModelActionMetadata
{

    public const METHOD_NAME_PREFIX = 'action';
    private const ROLES_ACCESS_TAG_NAME = 'roles-access';
    private const SERVICES_ACCESS_TAG_NAME = 'services-access';
    private const STATUSES_ACCESS_TAG_NAME = 'statuses-access';
    private const PERMISSIONS_ACCESS_TAG_NAME = 'permissions-access';
    private const AND_TAG_SEPARATOR = ',';
    private const OR_TAG_SEPARATOR = '|';

    /**
     * @var string
     */
    protected string $actionName;

    /**
     * @var string[]
     */
    protected array $statusesAccess = [];

    /**
     * @var string[]
     */
    protected array $servicesAccess = [];

    /**
     * @var string[]
     */
    protected array $rolesAccess = [];

    /**
     * @var string[]
     */
    protected array $permissionsAccess = [];

    /**
     * @var ReflectionParameter[]
     */
    private array $parameters;

    /**
     * @return array
     * @throws \ReflectionException
     */
    public function toArray(): array
    {
        $result = [];
        $result['action_name'] = $this->actionName;
        $result['statuses_access'] = $this->statusesAccess;
        $result['services_access'] = $this->servicesAccess;
        $result['roles_access'] = $this->rolesAccess;
        $result['permissions_access'] = $this->permissionsAccess;

        foreach ($this->parameters as $key => $parameter) {
            $result['parameters'][$key]['name'] = Str::snake($parameter->getName());
            $result['parameters'][$key]['allows_null'] = $parameter->allowsNull() || $parameter->getType()->allowsNull();
            if ($parameter->isDefaultValueAvailable()) {
                $result['parameters'][$key]['default_value'] = $parameter->getDefaultValue();
            }
        }
        return $result;
    }

    /**
     * Получение корректного названия метода у модели.
     *
     * @param string $actionName
     * @return string
     *
     * TODO: Убрать ответственность с других классов от определения метода по названию
     */
    public static function getCurrentActionName(string $actionName): string
    {
        if (str_contains($actionName, ModelActionMetadata::METHOD_NAME_PREFIX)) {
            return $actionName;
        } else {
            return ModelActionMetadata::METHOD_NAME_PREFIX . ucwords($actionName);
        }
    }

    /**
     * @return string
     */
    public function getActionName(): string
    {
        return $this->actionName;
    }

    private const MODEL_ACTION_PREFIX = 'action';

    /**
     * @return string
     */
    public function getActionMethodName(): string
    {
        return self::MODEL_ACTION_PREFIX . ucwords($this->actionName);
    }

    /**
     * @return string[]
     */
    public function getStatusesAccess(): array
    {
        return $this->statusesAccess;
    }

    /**
     * @return string[]
     */
    public function getServicesAccess(): array
    {
        return $this->servicesAccess;
    }

    /**
     * @return array[]
     */
    public function getRolesAccess(): array
    {
        return $this->rolesAccess;
    }

    /**
     * @return array[]
     */
    public function getPermissionsAccess(): array
    {
        return $this->permissionsAccess;
    }

    /**
     * @return ReflectionParameter[]
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    /**
     * @param RefGenericTag $tag
     * @throws ModelActionMetadataException
     */
    public function supplementFromTag(RefGenericTag $tag)
    {
        switch ($tag->getName()) {
            case self::STATUSES_ACCESS_TAG_NAME:
            case self::SERVICES_ACCESS_TAG_NAME:
                if (str_contains($tag->getDescription(), self::AND_TAG_SEPARATOR)) {
                    throw new ModelActionMetadataException(
                        'Services and Statuses accesses don\'t supported AND operator!'
                    );
                }
                $this->{Str::camel($tag->getName())} = explode(self::OR_TAG_SEPARATOR, $tag->getDescription());
                break;
            case self::ROLES_ACCESS_TAG_NAME:
            case self::PERMISSIONS_ACCESS_TAG_NAME:
                foreach (explode(self::OR_TAG_SEPARATOR, $tag->getDescription()) as $rawOrValue) {
                    $this->{Str::camel($tag->getName())}[] = explode(self::AND_TAG_SEPARATOR, $rawOrValue);
                    if (in_array(StatusAccess::GUEST, $this->{Str::camel($tag->getName())})) {
                        throw new ModelActionMetadataException(
                            $tag->getName() . ' don\'t supports with ' . StatusAccess::GUEST . ' auth status!'
                        );
                    }
                }
                $this->statusesAccess[] = StatusAccess::LOGGED;
                $this->statusesAccess = array_unique($this->statusesAccess);
                break;
        }
    }

    /**
     * @param string $actionName
     */
    public function setActionName(string $actionName): void
    {
        $this->actionName = $actionName;
    }

    /**
     * @param ReflectionParameter[] $parameters
     */
    public function setParameters(array $parameters): void
    {
        $this->parameters = $parameters;
    }

}
