<?php

namespace Egal\Core\Auth;

use Carbon\Carbon;
use Egal\Core\Database\Model;
use Egal\Core\Exceptions\NoAccessException;
use Illuminate\Support\Facades\Log;

class Gate
{

    private bool $enabled = true; # TODO: From config and for RPC disabled always.

    private array $policies = [];

    public function __construct()
    {
//        $this->enabled = config('auth.gate_is_enabled');
    }

    /**
     * @param class-string $class
     * @param class-string $policy
     */
    public function registerPolicy(string $class, string $policy): void
    {
        $this->policies[$class] = new $policy();
    }

    /**
     * @param Model|class-string $model
     */
    public function check(?User $user, Ability $ability, Model|string $model): bool
    {
        if (!$this->enabled) {
            return true;
        }

        $policy = $this->policies[is_object($model) ? get_class($model) : $model] ?? null; # TODO: Many policies.

        if ($policy === null || !method_exists($policy, $ability->name)) {
            return false;
        }

        return is_object($model)
            ? $policy->{$ability->name}($user, $model)
            : $policy->{$ability->name}($user);
    }

    /**
     * @param Model|class-string $model
     */
    public function allowed(?User $user, Ability $ability, Model|string $model): bool
    {
        if ($this->check($user, $ability, $model) === false) {
            throw new NoAccessException();
        }

        return true;
    }

}
