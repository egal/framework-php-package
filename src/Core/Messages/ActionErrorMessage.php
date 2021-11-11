<?php

namespace Egal\Core\Messages;

use Egal\Core\Exceptions\InitializeMessageFromArrayException;
use Egal\Core\Exceptions\UndefinedTypeOfMessageException;

class ActionErrorMessage extends Message implements HasActionMessageInterface
{

    use HasActionMessage;

    protected string $type = MessageType::ACTION_ERROR;
    public int $code;
    public string $message;

    public function __construct(string $message = '', int $code = 500)
    {
        parent::__construct();
        $this->code = $code;
        $this->message = $message;
    }

    public static function fromArray(array $array): ActionErrorMessage
    {
        if (!isset($array['type'])) {
            throw new UndefinedTypeOfMessageException();
        }

        if ($array['type'] !== MessageType::ACTION_ERROR) {
            throw new InitializeMessageFromArrayException('Invalid type substitution!');
        }

        $result = new ActionErrorMessage();
        $result->uuid = $array['uuid'];
        $result->message = $array['message'];
        $result->code = $array['code'];
        $result->actionMessage = ActionMessage::fromArray($array[MessageType::ACTION]);

        return $result;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function setMessage(string $message): void
    {
        $this->message = $message;
    }

    public function getCode(): int
    {
        return $this->code;
    }

    public function setCode(int $code): void
    {
        $this->code = $code;
    }

}
