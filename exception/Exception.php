<?php

namespace Egal\Exception;

use Exception as BaseException;
use Throwable;

abstract class Exception extends BaseException
{

    /**
     * Должно содержать ключевое слово "ошибка"
     */
    protected const MESSAGE_PREFIX_LINE = null;

    /**
     * Должно содержать сообщение сути ошибки
     */
    protected const BASE_MESSAGE_LINE = null;
    protected const DEFAULT_CODE = 500;

    /**
     * @param string|null $additionalMessageLine
     * @param int|null $code
     * @param Throwable|null $previous
     */
    public function __construct(?string $additionalMessageLine = null, ?int $code = null, ?Throwable $previous = null)
    {
        // Формирование кода ошибки
        // Если код ошибки не передан - выставить код ошибки = defaultCode
        // Если код ошибки не передан и не выставлен defaultCode - выставить код ошибки = 500
        $code = empty($code) ? static::DEFAULT_CODE : $code;

        // Формирование сообщения ошибки
        // Если выставлен baseMessageLine выставить его в начало сообщения
        // Если передана дополнительная строка сообщения - добавить её в конец сообщения
        $message = '';
        empty(static::MESSAGE_PREFIX_LINE) ?: $message .= ' ' . static::MESSAGE_PREFIX_LINE;
        empty(static::BASE_MESSAGE_LINE) ?: $message .= ' ' . static::BASE_MESSAGE_LINE;
        empty($additionalMessageLine) ?: $message .= ' ' . $additionalMessageLine;
        $message = trim(str_replace('  ', ' ', $message));

        parent::__construct($message, $code, $previous);
    }

}
