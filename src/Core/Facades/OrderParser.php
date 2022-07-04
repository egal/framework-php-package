<?php

namespace Egal\Core\Facades;

use Illuminate\Support\Facades\Facade;

class OrderParser extends Facade
{

    protected static function getFacadeAccessor()
    {
        return 'egal.order.parser';
    }

}
