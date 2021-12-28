<?php

declare(strict_types=1);

namespace Egal\Core\Exceptions;

use Exception;

class NoResultMessageException extends Exception
{

    protected $message = 'No result message!';

}
