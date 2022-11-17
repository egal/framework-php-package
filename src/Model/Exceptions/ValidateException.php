<?php

declare(strict_types=1);

namespace Egal\Model\Exceptions;

use Exception;
use Illuminate\Support\MessageBag;

class ValidateException extends Exception
{

    /**
     * @var string
     */
    protected $message = 'Validation failed!';

    /**
     * @var int
     */
    protected $code = 405;

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
