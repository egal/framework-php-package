<?php

namespace EgalFramework\Model\Exceptions;

use Exception;
use Throwable;

class ValidationException extends Exception
{

    /** @var array */
    private array $errors;

    /**
     * ValidateException constructor.
     * @param string $message
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct($message = '', $code = 405, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->errors = [];
    }

    /**
     * @param array $errors
     */
    public function setErrors(array $errors): void
    {
        $this->errors = $errors;
    }

    /**
     * @return array
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

}
