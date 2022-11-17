<?php

declare(strict_types=1);

namespace Egal\Core\Exceptions;

use Exception;

class ImpossibilityDeterminingStatusOfResponseException extends Exception
{

    /**
     * @var string
     */
    protected $code = 500;

    /**
     * @var int
     */
    protected $message = 'Impossibility determining status of response!';

}
