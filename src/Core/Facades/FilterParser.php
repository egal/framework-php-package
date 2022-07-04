<?php

namespace Egal\Core\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Egal\Core\Rest\Filter\Parser
 */
class FilterParser extends Facade
{

    protected static function getFacadeAccessor()
    {
        return 'egal.filter.parser';
    }

}
