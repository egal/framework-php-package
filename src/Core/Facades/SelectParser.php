<?php

namespace Egal\Core\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Egal\Core\Rest\Select\Parser
 */
class SelectParser extends Facade
{

    protected static function getFacadeAccessor()
    {
        return 'egal.select.parser';
    }

}
