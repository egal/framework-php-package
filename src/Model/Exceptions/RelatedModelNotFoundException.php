<?php

declare(strict_types=1);

namespace Egal\Model\Exceptions;

use Exception;

class RelatedModelNotFoundException extends Exception
{

    /**
     * @var string
     */
    protected $message = 'Related model not found!';

    /**
     * @var int
     */
    protected $code = 500;

    /**
     * @return static
     */
    public static function make(string $related): self
    {
        $exception = new static();
        $exception->message = 'Related model' . $related . 'not found!';

        return $exception;
    }

}
