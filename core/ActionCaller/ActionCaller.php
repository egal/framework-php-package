<?php

namespace Egal\Core\ActionCaller;

use Egal\Auth\Accesses\ServiceAccess;
use Egal\Auth\Accesses\StatusAccess;
use Egal\Core\Session\Session;
use Egal\Exception\ActionCallException;
use Egal\Model\Metadata\ModelActionMetadata;
use Egal\Model\ModelManager;
use Exception;
use Illuminate\Support\Str;
use ReflectionException;

class ActionCaller
{

    protected array $actionParameters = [];
    private string $modelClass;
    private string $actionName;

    /**
     * @param string $modelClass
     * @param string $actionName
     * @param array $actionParameters
     * @return mixed
     * @throws ActionCallException
     * @throws ReflectionException
     */
    public static function call(string $modelClass, string $actionName, array $actionParameters = [])
    {
        return (new static($modelClass, $actionName, $actionParameters))->forceCall();
    }

    /**
     * @param string $modelClass
     * @param string $actionName
     * @param array $actionParameters
     */
    public function __construct(string $modelClass, string $actionName, array $actionParameters = [])
    {
        $this->modelClass = $modelClass;
        $this->actionName = $actionName;
        $this->actionParameters = $actionParameters;
    }

    /**
     * @return mixed
     * @throws ReflectionException
     * @throws Exception
     * @throws ActionCallException
     */
    private function forceCall()
    {
        if (Session::isAuthEnabled() && !$this->isAccessedForCall()) {
            throw new ActionCallException('Access denied!');
        }

        return call_user_func_array(
            [
                $this->modelClass,
                $this->actionName
            ],
            $this->getValidActionParameters()
        );
    }

    /**
     * @return bool
     * @throws Exception
     */
    private function isAccessedForCall(): bool
    {
        $modelMetadata = ModelManager::getModelMetadata($this->modelClass); # TODO: Убрать использование ReflectionClass
        $actionMetadata = $modelMetadata->getAction($this->actionName); # TODO: Убрать использование ReflectionMethod

        return $this->isServiceAccess($actionMetadata) || $this->isUserAccess($actionMetadata);
    }

    /**
     * @param ModelActionMetadata $actionMetadata
     * @return bool
     * @throws \Egal\Core\Exceptions\CurrentSessionException
     */
    private function isServiceAccess(ModelActionMetadata $actionMetadata): bool
    {
        if (!Session::isServiceSession()) {
            return false;
        }
        $servicesAccess = $actionMetadata->getServicesAccess();
        if (in_array(ServiceAccess::ALL, $servicesAccess)) {
            return true;
        }
        if (!Session::isServiceServiceTokenExists()) {
            return false;
        }
        $serviceName = Session::getServiceServiceToken()->getServiceName();
        return in_array($serviceName, $servicesAccess);
    }

    /**
     * @param ModelActionMetadata $actionMetadata
     * @return bool
     * @throws Exception
     */
    private function isUserAccess(ModelActionMetadata $actionMetadata): bool
    {
        if (!Session::isUserSerssion()) {
            return false;
        }
        $authStatus = Session::getAuthStatus();
        $statusCheck = in_array($authStatus, $actionMetadata->getStatusesAccess());

        return $statusCheck
            && (
                $authStatus === StatusAccess::GUEST
                || (
                    $this->userHasRoles($actionMetadata)
                    && $this->userHasPermissions($actionMetadata)
                )
            );
    }

    /**
     * @param ModelActionMetadata $actionMetadata
     * @return bool
     * @throws Exception
     */
    private function userHasRoles(ModelActionMetadata $actionMetadata): bool
    {
        $rolesAccess = $actionMetadata->getRolesAccess();

        return $rolesAccess === []
            || count(array_intersect(Session::getUserServiceToken()->getRoles(), $rolesAccess)) > 0;
    }

    /**
     * @param ModelActionMetadata $actionMetadata
     * @return bool
     * @throws Exception
     */
    private function userHasPermissions(ModelActionMetadata $actionMetadata): bool
    {
        $permissionsAccess = $actionMetadata->getPermissionsAccess();

        return $permissionsAccess === []
            || count(array_intersect(Session::getUserServiceToken()->getPermissions(), $permissionsAccess)) > 0;
    }

    /**
     * @return array
     * @throws ActionCallException
     * @throws ReflectionException
     * @throws Exception
     */
    private function getValidActionParameters(): array
    {
        $newActionParameters = [];
        $reflectionParameters = ModelManager::getModelMetadata($this->modelClass)
            ->getAction($this->actionName)
            ->getParameters();

        foreach ($reflectionParameters as $reflectionParameter) {
            $actionParameterKey = Str::snake($reflectionParameter->getName());
            $newActionParameterKey = $reflectionParameter->getPosition();

            if (!array_key_exists($actionParameterKey, $this->actionParameters)) {
                if ($reflectionParameter->isDefaultValueAvailable()) {
                    $newActionParameters[$newActionParameterKey] = $reflectionParameter->getDefaultValue();
                } elseif ($reflectionParameter->allowsNull()) {
                    $newActionParameters[$newActionParameterKey] = null;
                } else {
                    throw new ActionCallException("Значение параметра $actionParameterKey обязательно! Значение по умолчанию или позволение null отсутствует!");
                }
            } else {
                $newActionParameters[$newActionParameterKey] = $this->actionParameters[$actionParameterKey];
            }
        }
        return $newActionParameters;
    }

}
