<?php

namespace EgalFramework\Model\Deprecated;

use EgalFramework\Common\Interfaces\ExceptionInterface;
use Exception;
use Throwable;

/**
 * @deprecated
 */
class ValidateException extends Exception implements ExceptionInterface
{

    /** @var array */
    private array $errors;

    /**
     * ValidateException constructor.
     * @param string $message
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct($message = '', $code = 0, Throwable $previous = null)
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
