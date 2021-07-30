<?php

namespace Egal\Model\Exceptions;

use Exception;

class FieldNotFoundException extends Exception
{

    protected $message = 'Field not found!';

}
