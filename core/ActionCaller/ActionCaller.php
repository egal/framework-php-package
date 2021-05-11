<?php

namespace Egal\Core\ActionCaller;

use Egal\Auth\Accesses\StatusAccess;
use Egal\Core\Session\Session;
use Egal\Exception\ActionCallException;
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
            throw new ActionCallException('No access!');
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

        $authStatus = Session::getAuthStatus();
        $statusCheck = in_array($authStatus, $actionMetadata->getStatusesAccess());
        $rolesAccess = $actionMetadata->getRolesAccess();
        $permissionsAccess = $actionMetadata->getPermissionsAccess();

        return (
                $statusCheck
                && $authStatus === StatusAccess::GUEST
            )
            || (
                $statusCheck
                && (
                    $rolesAccess === []
                    || count(array_intersect(Session::getUserServiceToken()->getRoles(), $rolesAccess)) > 0
                )
                && (
                    $permissionsAccess === []
                    || count(array_intersect(Session::getUserServiceToken()->getPermissions(), $permissionsAccess)) > 0
                )
            );
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
