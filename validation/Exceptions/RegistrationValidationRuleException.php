<?php

declare(strict_types=1);

namespace Egal\Validation\Exceptions;

use Exception;

class RegistrationValidationRuleException extends Exception
{

    protected $message = 'Registration validation rule exception!';

    protected $code = 500;

}
