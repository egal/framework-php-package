<?php

declare(strict_types=1);

namespace Egal\Model\Exceptions;

use Exception;

class RelationNotFoundException extends Exception
{

    protected $message = 'Relation not found!';

    protected $code = 403;

    public static function make(string $relation): self
    {
        $exception = new static();
        $exception->message = 'Relation ' . $relation . ' not found!';

        return $exception;
    }

}
