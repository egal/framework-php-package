<?php

namespace Egal\Core\Exceptions;

use Exception;
use Illuminate\Support\MessageBag;

class ValidateException extends Exception
{

    protected $message = 'Validation failed!';

    protected $code = 400;

    private MessageBag $messageBag;

    public function setMessageBag(MessageBag $messageBag): void
    {
        $this->messageBag = $messageBag;

        foreach ($this->messageBag->getMessages() as $messagePart) {
            foreach ($messagePart as $message) {
                $this->message .= ' ' . $message;
            }
        }
    }

    public function getMessageBag(): MessageBag
    {
        return $this->messageBag;
    }
}
