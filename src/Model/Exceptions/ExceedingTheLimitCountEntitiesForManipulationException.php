<?php

declare(strict_types=1);

namespace Egal\Model\Exceptions;

use Exception;

/**
 * Elimination of exceeding the permissible limit for the simultaneous manipulation of the number of entities.
 */
class ExceedingTheLimitCountEntitiesForManipulationException extends Exception
{

    /**
     * The error message
     */
    protected $message = 'Exceeding the limit count entities for manipulation!';

    /**
     * The error code
     */
    protected $code = 422;

}
