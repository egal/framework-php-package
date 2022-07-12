<?php

namespace Egal\Core\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Egal\Core\Auth\Manager
 */
class AuthManager extends Facade
{

    protected static function getFacadeAccessor()
    {
        return 'egal.auth.manager';
    }

}
