<?php

declare(strict_types=1);

namespace Egal\Model\Exceptions;

use Exception;

class ModelActionMetadataException extends Exception
{

    protected $message = 'Model action metadata exception!';

    protected $code = 500;

}
