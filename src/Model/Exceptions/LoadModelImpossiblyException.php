<?php

declare(strict_types=1);

namespace Egal\Model\Exceptions;

use Exception;

/**
 * Class LoadModelImpossiblyException
 */
class LoadModelImpossiblyException extends Exception
{

    protected $message = 'Unable to load model!';

    protected $code = 500;

}
