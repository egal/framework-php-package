<?php

declare(strict_types=1);

namespace Egal\Model\Exceptions;

use Exception;

class ModelActionMetadataException extends Exception
{

    /**
     * @var string
     */
    protected $message = 'Model action metadata exception!';

    /**
     * @var int
     */
    protected $code = 500;

}
