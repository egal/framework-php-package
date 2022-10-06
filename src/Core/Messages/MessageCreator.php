<?php

declare(strict_types=1);

namespace Egal\Core\Messages;

use Egal\Core\Exceptions\UnableDetermineMessageTypeException;
use Egal\Core\Exceptions\UnsupportedMessageTypeException;

class MessageCreator
{

    public static function fromArray(array $array): Message
    {
        if (!array_key_exists('type', $array)) {
            throw new UnableDetermineMessageTypeException();
        }

        switch ($array['type']) {
            case MessageType::START_PROCESSING:
                return StartProcessingMessage::fromArray($array);
            case MessageType::ACTION_RESULT:
                return ActionResultMessage::fromArray($array);
            case MessageType::ACTION_ERROR:
                return ActionErrorMessage::fromArray($array);
            case MessageType::EVENT:
                return EventMessage::fromArray($array);
            default:
                throw new UnsupportedMessageTypeException();
        }
    }

    public static function fromJson(string $json): Message
    {
        return static::fromArray(json_decode($json, true));
    }

}
