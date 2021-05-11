<?php

namespace Egal\CodeGenerator\Exceptions;

use Exception;

class ReadingStudFileException extends Exception
{

    protected $message = 'Error reading stub file!';
    protected $code = 500;

}
