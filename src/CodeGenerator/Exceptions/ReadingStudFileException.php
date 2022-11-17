<?php

declare(strict_types=1);

namespace Egal\CodeGenerator\Exceptions;

use Exception;

class ReadingStudFileException extends Exception
{

    /**
     * @var string
     */
    protected $message = 'Error reading stub file!';

    /**
     * @var int
     */
    protected $code = 500;

}
