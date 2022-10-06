<?php

declare(strict_types=1);

namespace Egal\Core\Messages;

use Egal\Core\Exceptions\InitializeMessageFromArrayException;
use Egal\Core\Exceptions\UndefinedTypeOfMessageException;

class ActionResultMessage extends Message implements HasActionMessageInterface
{

    use HasActionMessage;

    /**
     * @var mixed
     */
    protected $data;

    protected string $type = MessageType::ACTION_RESULT;

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
        $result->actionMessage = ActionMessage::fromArray($array[MessageType::ACTION]);

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
