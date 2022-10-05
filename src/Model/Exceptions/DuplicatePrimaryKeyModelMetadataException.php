<?php

declare(strict_types=1);

namespace Egal\Model\Exceptions;

use Exception;

class DuplicatePrimaryKeyModelMetadataException extends Exception
{

    protected $message = 'Duplicate primary key in model metadata!';

    protected $code = 500;

}
