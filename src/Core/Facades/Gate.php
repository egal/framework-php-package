<?php

namespace Egal\Core\Facades;

use Egal\Core\Auth\User;
use Illuminate\Support\Facades\Facade;

/**
 * @see \Egal\Core\Auth\Gate
 */
class Gate extends Facade
{

    protected static function getFacadeAccessor()
    {
        return 'egal.gate';
    }

    public static function user(): User
    {
        return self::getUser();
    }

}
