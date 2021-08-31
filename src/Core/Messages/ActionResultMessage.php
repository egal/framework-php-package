<?php

namespace Egal\Core\Messages;

use Egal\Core\Exceptions\InitializeMessageFromArrayException;
use Egal\Core\Exceptions\UndefinedTypeOfMessageException;
use Exception;

class ActionResultMessage extends Message
{

    use HasActionMessage;

    protected string $type = MessageType::ACTION_RESULT;

    /**
     * @var mixed
     */
    public $data;

    /**
     * @param array $array
     * @return ActionResultMessage
     * @throws Exception
     */
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

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param mixed $data
     */
    public function setData($data): void
    {
        $this->data = $data;
    }


}
