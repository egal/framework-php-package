<?php

declare(strict_types=1);

namespace Egal\Core\Messages;

use Egal\Core\Exceptions\InitializeMessageFromArrayException;
use Egal\Core\Exceptions\UndefinedTypeOfMessageException;

class ActionErrorMessage extends Message
{

    use HasActionMessage;

    public string $internalCode;

    public string $message;


    protected string $type = MessageType::ACTION_ERROR;

    public function __construct(string $message = '', string $code = '')
    {
        parent::__construct();

        $this->internalCode = $code;
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
        $result->internalCode = $array['internal_code'];

        if (isset($array['action_message'])) {
            $result->actionMessage = ActionMessage::fromArray($array['action_message']);
        }

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

    public function getInternalCode(): string
    {
        return $this->internalCode;
    }

    public function setInternalCode(string $internalCode): void
    {
        $this->internalCode = $internalCode;
    }

}
