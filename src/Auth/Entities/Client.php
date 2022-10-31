<?php

declare(strict_types=1);

namespace Egal\Auth\Entities;

use Egal\Auth\Exceptions\NoAccessToActionException;
use Egal\Core\Session\Session;
use Egal\Model\Model;

/**
 * @method bool mayOrFail(string $ability, Model $model)
 * @method bool isUserOrFail()
 * @method bool isGuestOrFail()
 * @method bool isServiceOrFail(string|null $name = null)
 * @method bool hasRoleOrFail(string $role)
 * @method bool hasRolesOrFail(string[] $roles)
 */
abstract class Client
{

    /**
     * @throws NoAccessToActionException
     */
    public function __call(string $name, array $arguments): bool
    {
        $methodName = preg_replace("/^(.*)(OrFail)$/", '$1', $name);
        if (!method_exists($this, $methodName)) {
            trigger_error('Call to undefined method ' . __CLASS__ . '::' . $name . '()', E_USER_ERROR);
        }

        if (!call_user_func_array([$this, $methodName], $arguments)) $this->fail();

        return true;
    }

    public function fail(): never
    {
        throw new NoAccessToActionException();
    }

    public function may(string $ability, Model $model): bool
    {
        return Session::isAuthEnabled()
            ? call_user_func_array([$model->getModelMetadata()->getPolicy(), $ability], [$this, $model])
            : true;
    }

    public function isUser(): bool
    {
        return $this instanceof User;
    }

    public function isGuest(): bool
    {
        return $this instanceof Guest;
    }

    public function isService(string|null $name = null): bool
    {
        return $this instanceof Service;
    }

    public function hasRole(string $role): bool
    {
        return false;
    }

    /**
     * @param string[] $roles
     */
    public function hasRoles(array $roles): bool
    {
        return false;
    }

}
