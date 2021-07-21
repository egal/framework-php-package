<?php

namespace Egal\Core\Exceptions;

use Exception;

class ImpossibilityDeterminingStatusOfResponseException extends Exception
{

    protected $code = 500;
    protected $message = 'Impossibility determining status of response!';

}
