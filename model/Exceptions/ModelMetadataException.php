<?php

declare(strict_types=1);

namespace Egal\Model\Exceptions;

use Exception;

class ModelMetadataException extends Exception
{

    protected $message = 'Model metadata exception!';

    protected $code = 500;

}
