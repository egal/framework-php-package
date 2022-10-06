<?php

declare(strict_types=1);

namespace Egal\Core\Exceptions;

use Exception;

class MessageProcessingException extends Exception
{

    protected $message = 'Message processing exception!';

    protected $code = 500;

}
