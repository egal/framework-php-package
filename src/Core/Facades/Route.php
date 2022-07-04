<?php

namespace Egal\Core\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Egal\Core\Http\Route
 */
class Route extends Facade
{

    protected static function getFacadeAccessor()
    {
        return 'egal.route';
    }

}
