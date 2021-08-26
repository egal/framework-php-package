<?php

declare(strict_types=1);

namespace Egal\Core\Exceptions;

use Exception;

/**
 * Class ImpossibilityDeterminingStatusOfResponseException
 */
class ImpossibilityDeterminingStatusOfResponseException extends Exception
{

    /**
     * The error code
     */
    protected $code = 500;

    /**
     * The error message
     */
    protected $message = 'Impossibility determining status of response!';

}
