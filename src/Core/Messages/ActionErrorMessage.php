<?php

namespace Egal\Core\Messages;

use Egal\Core\Exceptions\InitializeMessageFromArrayException;
use Egal\Core\Exceptions\UndefinedTypeOfMessageException;
use Exception;

class ActionErrorMessage extends Message
{

    use HasActionMessage;

    protected string $type = MessageType::ACTION_ERROR;
    public string $code;
    public string $message;

    public function __construct(string $message = '', string $code = '')
    {
        parent::__construct();
        $this->code = $code;
        $this->message = $message;
    }

    /**
     * @param array $array
     * @return ActionErrorMessage
     * @throws Exception
     */
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

        if (isset($array['action_message'])) {
            $result->actionMessage = ActionMessage::fromArray($array['action_message']);
        }

        return $result;
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * @param string $message
     */
    public function setMessage(string $message): void
    {
        $this->message = $message;
    }

    /**
     * @return string
     */
    public function getCode(): string
    {
        return $this->code;
    }

    /**
     * @param string $code
     */
    public function setCode(string $code): void
    {
        $this->code = $code;
    }

}
