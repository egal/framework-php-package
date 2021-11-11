<?php

namespace Egal\Core\Messages;

use Egal\Core\Exceptions\InitializeMessageFromArrayException;
use Egal\Core\Exceptions\UndefinedTypeOfMessageException;

class ActionResultMessage extends Message implements HasActionMessageInterface
{

    use HasActionMessage;

    protected string $type = MessageType::ACTION_RESULT;

    /**
     * @var mixed
     */
    public $data;

    public static function fromArray(array $array): ActionResultMessage
    {
        if (!isset($array['type'])) {
            throw new UndefinedTypeOfMessageException();
        }
        if ($array['type'] !== MessageType::ACTION_RESULT) {
            throw new InitializeMessageFromArrayException('Invalid type substitution!');
        }

        $result = new ActionResultMessage();
        $result->uuid = $array['uuid'];
        $result->data = $array['data'];

        if (isset($array['action_message'])) {
            $result->actionMessage = ActionMessage::fromArray($array['action_message']);
        }

        return $result;
    }

    public function getData()
    {
        return $this->data;
    }

    public function setData($data): void
    {
        $this->data = $data;
    }

}
