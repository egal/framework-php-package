<?php

namespace Egal\Model\Exceptions;

use Exception;

/**
 * Class LoadModelImpossiblyException
 * @package Egal\Model\Exceptions
 */
class LoadModelImpossiblyException extends Exception
{

    protected $message = 'LoadModelImpossiblyException';
    protected $code = 500;

}
