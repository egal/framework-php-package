<?php


namespace EgalFramework\Model\Exceptions;

use Exception as BaseException;
use Throwable;

abstract class Exception extends BaseException
{

    protected ?string $baseMessageLine = null;
    protected ?int $defaultCode = null;

    public function __construct(string $additionalMessageLine = null, $code = null, Throwable $previous = null)
    {
        // Формирование кода ошибки
        // Если код ошибки не передан - выставить код ошибки = defaultCode
        // Если код ошибки не передан и не выставлен defaultCode - выставить код ошибки = 500
        if (is_null($code) && !is_null($this->defaultCode)) {
            $code = $this->defaultCode;
        } elseif (is_null($code) && is_null($this->defaultCode)) {
            $code = 500;
        }

        // Формирование сообщения ошибки
        // Если выставлен baseMessageLine выставить его в начало сообщения
        // Если передана дополнительная строка сообщения - добавить её в конец сообщения
        $message = '';
        if (!is_null($this->baseMessageLine) && $this->baseMessageLine !== '') {
            $message .= $this->baseMessageLine;
        }
        if (!is_null($additionalMessageLine) && $additionalMessageLine !== '') {
            $message .= ' ';
            $message .= $additionalMessageLine;
        }

        parent::__construct($message, $code, $previous);
    }

}