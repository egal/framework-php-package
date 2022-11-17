<?php

declare(strict_types=1);

namespace Egal\Model\Exceptions;

use Exception;

class RelationNotFoundException extends Exception
{

    /**
     * @var string
     */
    protected $message = 'Relation not found!';

    /**
     * @var int
     */
    protected $code = 403;

    public static function make(string $relation): self
    {
        $exception = new static();
        $exception->message = 'Relation ' . $relation . ' not found!';

        return $exception;
    }

}
