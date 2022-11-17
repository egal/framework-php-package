<?php

declare(strict_types=1);

namespace Egal\Model\Exceptions;

use Exception;

class ModelMetadataException extends Exception
{

    /**
     * @var string
     */
    protected $message = 'Model metadata exception!';

    /**
     * @var int
     */
    protected $code = 500;

}
