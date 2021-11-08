<?php

declare(strict_types=1);

namespace Egal\Model\Metadata;

use Egal\Auth\Accesses\StatusAccess;
use Egal\Model\Exceptions\ModelActionMetadataException;
use Egal\Model\Exceptions\ModelMetadataTagContainsSpaceException;
use Illuminate\Support\Str;
use phpDocumentor\Reflection\DocBlock\Tags\Generic as RefGenericTag;

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

    private const MODEL_ACTION_PREFIX = 'action';

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
     * @var \ReflectionParameter[]
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
            $result['parameters'][$key]['allows_null'] = $parameter->allowsNull()
                || $parameter->getType()->allowsNull();

            if (!$parameter->isDefaultValueAvailable()) {
                continue;
            }

            $result['parameters'][$key]['default_value'] = $parameter->getDefaultValue();
        }

        return $result;
    }

    public function getActionName(): string
    {
        return $this->actionName;
    }

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
     * @return \ReflectionParameter[]
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    public function supplementFromTag(RefGenericTag $tag): void
    {
        $tagDescription = $tag->getDescription()->render();
        $tagName = $tag->getName();

        switch ($tagName) {
            case self::STATUSES_ACCESS_TAG_NAME:
            case self::SERVICES_ACCESS_TAG_NAME:
                if (str_contains($tagDescription, self::AND_TAG_SEPARATOR)) {
                    throw new ModelActionMetadataException(
                        'Services and Statuses accesses don\'t supported AND operator!'
                    );
                }

                // Check string not contain spaces.
                $pattern = '/[^\s]+/';

                if (!preg_match($pattern, (string) $tagDescription, $matches) || $matches[0] !== $tagDescription) {
                    throw ModelMetadataTagContainsSpaceException::make($tagName);
                }

                $this->{Str::camel($tagName)} = explode(self::OR_TAG_SEPARATOR, $tagDescription);
                break;
            case self::ROLES_ACCESS_TAG_NAME:
            case self::PERMISSIONS_ACCESS_TAG_NAME:
                // Check string not contain spaces.
                $pattern = '/[^\s]+/';

                if (!preg_match($pattern, (string) $tagDescription, $matches) || $matches[0] !== $tagDescription) {
                    throw ModelMetadataTagContainsSpaceException::make($tagName);
                }

                foreach (explode(self::OR_TAG_SEPARATOR, $tagDescription) as $rawOrValue) {
                    $this->{Str::camel($tagName)}[] = explode(self::AND_TAG_SEPARATOR, $rawOrValue);

                    if (in_array(StatusAccess::GUEST, $this->{Str::camel($tagName)})) {
                        throw new ModelActionMetadataException(
                            $tagName . ' don\'t supports with ' . StatusAccess::GUEST . ' auth status!'
                        );
                    }
                }
                $this->statusesAccess[] = StatusAccess::LOGGED;
                $this->statusesAccess = array_unique($this->statusesAccess);
                break;
            default:
                break;
        }
    }

    public function setActionName(string $actionName): void
    {
        $this->actionName = $actionName;
    }

    /**
     * @param \ReflectionParameter[] $parameters
     */
    public function setParameters(array $parameters): void
    {
        $this->parameters = $parameters;
    }

    /**
     * Получение корректного названия метода у модели.
     *
     * @return string
     *
     * TODO: Убрать ответственность с других классов от определения метода по названию
     */
    public static function getCurrentActionName(string $actionName): string
    {
        return str_contains($actionName, self::METHOD_NAME_PREFIX)
            ? $actionName
            : self::METHOD_NAME_PREFIX . ucwords($actionName);
    }

}
