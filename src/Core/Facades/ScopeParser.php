<?php

namespace Egal\Core\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Egal\Core\Rest\Scope\Parser
 */
class ScopeParser extends Facade
{

    protected static function getFacadeAccessor()
    {
        return 'egal.scope.parser';
    }

}
