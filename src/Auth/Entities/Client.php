<?php

declare(strict_types=1);

namespace Egal\Auth\Entities;

use Egal\Auth\Exceptions\NoAccessToActionException;
use Egal\Model\Facades\ModelMetadataManager;
use Egal\Model\Model;

/**
 * @method bool isUserOrFail()
 * @method bool mayOrFail(string $ability, string|Model $model)
 * @method bool isGuestOrFail()
 * @method bool isServiceOrFail()
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

        return call_user_func_array([$this, $methodName], $arguments) ?: throw new NoAccessToActionException;
    }

    public function may(string $ability, string|Model $model): bool
    {
        $modelClass = is_string($model) ? $model : get_class($model);
        $policy = ModelMetadataManager::getModelMetadata($modelClass)->getPolicy();

        return call_user_func_array([$policy, $ability], [$model]);
    }

    public function isUser(): bool
    {
        return $this instanceof User;
    }

    public function isGuest(): bool
    {
        return $this instanceof Guest;
    }

    public function isService(): bool
    {
        return $this instanceof Service;
    }

}
