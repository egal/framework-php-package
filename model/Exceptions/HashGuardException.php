<?php

declare(strict_types=1);

namespace Egal\Model\Exceptions;

use Exception;

class HashGuardException extends Exception
{

    protected $message = 'Hash guard Exception!';

    protected $code = 500;

}
