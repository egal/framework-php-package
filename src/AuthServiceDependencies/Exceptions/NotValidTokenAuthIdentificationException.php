<?php

declare(strict_types=1);

namespace Egal\AuthServiceDependencies\Exceptions;

use Exception;

class NotValidTokenAuthIdentificationException extends Exception
{

    protected $code = 400;

    protected $message = 'Not valid token auth identification!';

}
