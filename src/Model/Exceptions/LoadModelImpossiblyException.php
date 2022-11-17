<?php

declare(strict_types=1);

namespace Egal\Model\Exceptions;

use Exception;

/**
 * Class LoadModelImpossiblyException
 */
class LoadModelImpossiblyException extends Exception
{

    /**
     * @var string
     */
    protected $message = 'Unable to load model!';

    /**
     * @var int
     */
    protected $code = 500;

}
