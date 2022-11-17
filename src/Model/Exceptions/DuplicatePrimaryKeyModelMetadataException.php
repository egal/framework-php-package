<?php

declare(strict_types=1);

namespace Egal\Model\Exceptions;

use Exception;

class DuplicatePrimaryKeyModelMetadataException extends Exception
{

    /**
     * @var string
     */
    protected $message = 'Duplicate primary key in model metadata!';

    /**
     * @var int
     */
    protected $code = 500;

}
