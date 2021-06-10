<?php

namespace Egal\Core\ActionCaller;

use Egal\Auth\Accesses\StatusAccess;
use Egal\Core\Exceptions\ActionCallException;
use Egal\Core\Exceptions\NoAccessActionCallException;
use Egal\Core\Session\Session;
use Egal\Model\Metadata\ModelActionMetadata;
use Egal\Model\Metadata\ModelMetadata;
use Egal\Model\ModelManager;
use Illuminate\Support\Str;

/**
 * Class ActionCaller
 * @package Egal\Core\ActionCaller
 */
class ActionCaller
{

    protected array $actionParameters = [];
    private ModelMetadata $modelMetadata;
    private ModelActionMetadata $modelActionMetadata;

    /**
     * ActionCaller constructor.
     * @param string $modelName
     * @param string $actionName
     * @param array $actionParameters
     * @throws \Exception
     */
    public function __construct(string $modelName, string $actionName, array $actionParameters = [])
    {
        $this->modelMetadata = ModelManager::getModelMetadata($modelName);
        $this->modelActionMetadata = $this->modelMetadata->getAction($actionName);
        $this->actionParameters = $actionParameters;
    }

    /**
     * @return mixed
     * @throws \ReflectionException
     * @throws \Exception
     * @throws \Egal\Core\Exceptions\ActionCallException
     */
    public function call()
    {
        if (Session::isAuthEnabled() && !$this->isAccessedForCall()) {
            throw new NoAccessActionCallException();
        }

        return call_user_func_array(
            [
                $this->modelMetadata->getModelClass(),
                $this->modelActionMetadata->getActionMethodName()
            ],
            $this->getValidActionParameters()
        );
    }

    /**
     * @return bool
     * @throws \Exception
     */
    private function isAccessedForCall(): bool
    {
        $authStatus = Session::getAuthStatus();
        // For user and service we check if it guest
        if ($authStatus === StatusAccess::GUEST) {
            return in_array($authStatus, $this->modelActionMetadata->getStatusesAccess());
        }

        return $this->isServiceAccess() || $this->isUserAccess();
    }

    /**
     * @return bool
     * @throws \Egal\Core\Exceptions\CurrentSessionException
     */
    private function isServiceAccess(): bool
    {
        if (!Session::isServiceServiceTokenExists()) {
            return false;
        }
        $serviceName = Session::getServiceServiceToken()->getServiceName();
        return in_array($serviceName, $this->modelActionMetadata->getServicesAccess());
    }

    /**
     * @return bool
     * @throws \Exception
     */
    private function isUserAccess(): bool
    {
        if (!Session::isUserServiceTokenExists()) {
            return false;
        }

        return in_array(Session::getAuthStatus(), $this->modelActionMetadata->getStatusesAccess())
            && $this->userHasAccessWithCurrentRoles()
            && $this->userHasAccessWithCurrentPermissions();
    }

    /**
     * @return bool
     * @throws \Exception
     * TODO: Переименовать
     */
    private function userHasAccessWithCurrentRoles(): bool
    {
        if (count($this->modelActionMetadata->getRolesAccess()) === 0) {
            return true;
        }
        foreach ($this->modelActionMetadata->getRolesAccess() as $rolesAccess) {
            if (
                count(array_intersect(Session::getUserServiceToken()->getRoles(), $rolesAccess))
                === count($rolesAccess)
            ) {
                return true;
            }
        }
        return false;
    }

    /**
     * @return bool
     * @throws \Exception
     * TODO: Переименовать
     */
    private function userHasAccessWithCurrentPermissions(): bool
    {
        if (count($this->modelActionMetadata->getPermissionsAccess()) === 0) {
            return true;
        }
        foreach ($this->modelActionMetadata->getPermissionsAccess() as $permissionsAccess) {
            if (
                count(array_intersect(Session::getUserServiceToken()->getPermissions(), $permissionsAccess))
                === count($permissionsAccess)
            ) {
                return true;
            }
        }
        return false;
    }

    /**
     * @return array
     * @throws ActionCallException
     * @throws \ReflectionException
     */
    private function getValidActionParameters(): array
    {
        $newActionParameters = [];
        foreach ($this->modelActionMetadata->getParameters() as $reflectionParameter) {
            $actionParameterKey = Str::snake($reflectionParameter->getName());
            $newActionParameterKey = $reflectionParameter->getPosition();

            if (!array_key_exists($actionParameterKey, $this->actionParameters)) {
                if ($reflectionParameter->isDefaultValueAvailable()) {
                    $newActionParameters[$newActionParameterKey] = $reflectionParameter->getDefaultValue();
                } elseif ($reflectionParameter->allowsNull()) {
                    $newActionParameters[$newActionParameterKey] = null;
                } else {
                    throw new ActionCallException(
                        "Parameter value $actionParameterKey necessarily!"
                        . ' There is not null and no default value!'
                    );
                }
            } else {
                $newActionParameters[$newActionParameterKey] = $this->actionParameters[$actionParameterKey];
            }
        }
        return $newActionParameters;
    }

}
