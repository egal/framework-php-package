<?php

declare(strict_types=1);

namespace Egal\Core\Messages;

use Egal\Core\Exceptions\InitializeMessageFromArrayException;
use Egal\Core\Exceptions\UndefinedTypeOfMessageException;

class ActionErrorMessage extends Message
{

    use HasActionMessage;

    protected int $code;

    protected string $message;

    protected ?string $internalCode;

    protected string $type = MessageType::ACTION_ERROR;

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
        $result->internalCode = $array['internal_code'] ?? null;

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

    public function getCode(): int
    {
        return $this->code;
    }

    public function setCode(int $code): void
    {
        $this->code = $code;
    }

    public function setInternalCode(string $internalCode): void
    {
        $this->internalCode = $internalCode;
    }

    public function getInternalCode(): ?string
    {
        return $this->internalCode;
    }

}
