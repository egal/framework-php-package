<?php

namespace Egal\Model\Exceptions;

use Exception;

class ExceedingTheLimitCountEntitiesForManipulationException extends Exception
{

    protected $message = 'Exceeding the limit count entities for manipulation!';
    protected $code = 403;

}
