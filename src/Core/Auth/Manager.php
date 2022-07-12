<?php

namespace Egal\Core\Auth;

class Manager
{
    public static function newUser(): UserModelInterface
    {
        $userModelClass = self::userClass();

        return new $userModelClass();
    }

    public static function userClass(): string
    {
        return config('auth.user_model_class', User::class);
    }
}
