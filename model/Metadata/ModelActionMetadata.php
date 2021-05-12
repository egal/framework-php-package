<?php

namespace Egal\Model\Metadata;

use Egal\Auth\Accesses\StatusAccess;
use Illuminate\Support\Str;
use ReflectionParameter;

/**
 * @package Egal\Model
 */
class ModelActionMetadata
{

    public const PREFIX = 'action';

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
     * @param string $actionName
     * @param ReflectionParameter[] $parameters
     * @param string[] $statusesAccess
     * @param string[] $rolesAccess
     * @param string[] $permissionsAccess
     * @param array $servicesAccess
     */
    public function __construct(
        string $actionName,
        array $parameters,
        array $statusesAccess = [],
        array $rolesAccess = [],
        array $permissionsAccess = [],
        array $servicesAccess = []
    )
    {
        $this->actionName = ModelActionMetadata::getCurrentActionName($actionName);
        $this->parameters = $parameters;
        $this->rolesAccess = $rolesAccess;
        $this->permissionsAccess = $permissionsAccess;
        $this->servicesAccess = $servicesAccess;

        if ($statusesAccess === [] && $this->rolesAccess === [] && $this->permissionsAccess === []) {
            $this->statusesAccess = [];
        } elseif ($statusesAccess === [] && ($this->rolesAccess !== [] || $this->permissionsAccess !== [])) {
            $this->statusesAccess = [StatusAccess::LOGGED];
        } else {
            $this->statusesAccess = $statusesAccess;
        }
    }

    /**
     * Получение корректного названия метода у модели.
     *
     * @param string $actionName
     * @return string
     */
    public static function getCurrentActionName(string $actionName): string
    {
        if (str_contains($actionName, ModelActionMetadata::PREFIX)) {
            return $actionName;
        } else {
            return ModelActionMetadata::PREFIX . ucwords($actionName);
        }
    }

    /**
     * @return string
     */
    public function getActionName(): string
    {
        return $this->actionName;
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
     * @return string[]
     */
    public function getRolesAccess(): array
    {
        return $this->rolesAccess;
    }

    /**
     * @return string[]
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

}
