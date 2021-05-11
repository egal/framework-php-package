<?php

namespace Egal\Exception;

use Illuminate\Support\MessageBag;

class ValidateException extends Exception
{

    protected const BASE_MESSAGE_LINE = 'Не пройдена валидация!';
    protected const DEFAULT_CODE = 405;

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
