<?php

declare(strict_types=1);

namespace Egal\AuthServiceDependencies\Exceptions;

use Exception;

class IncorrectAppServicesEnvironmentVariablePatternException extends Exception
{

    /**
     * @var string
     */
    protected $message = 'Incorrect app services environment variable pattern!';

    /**
     * @var int
     */
    protected $code = 500;

    public static function make(string $string): self
    {
        $result = new static();
        $result->message .= ' [' . $string . ']';

        return $result;
    }

}
