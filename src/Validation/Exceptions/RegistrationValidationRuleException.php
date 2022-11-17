<?php

declare(strict_types=1);

namespace Egal\Validation\Exceptions;

use Exception;

class RegistrationValidationRuleException extends Exception
{

    /**
     * @var string
     */
    protected $message = 'Registration validation rule exception!';

    /**
     * @var int
     */
    protected $code = 500;

}
