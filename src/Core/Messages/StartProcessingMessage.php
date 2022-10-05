<?php

declare(strict_types=1);

namespace Egal\Core\Messages;

use Egal\Core\Exceptions\InitializeMessageFromArrayException;
use Egal\Core\Exceptions\UndefinedTypeOfMessageException;

class StartProcessingMessage extends Message implements HasActionMessageInterface
{

    use HasActionMessage;

    protected string $type = MessageType::START_PROCESSING;

    protected float $startedAt;

    public function __construct()
    {
        parent::__construct();

        $this->startedAt = microtime(true);
    }

    public static function fromArray(array $array): StartProcessingMessage
    {
        if (!isset($array['type'])) {
            throw new UndefinedTypeOfMessageException();
        }

        if ($array['type'] !== MessageType::START_PROCESSING) {
            throw new InitializeMessageFromArrayException('Invalid type substitution!');
        }

        $result = new StartProcessingMessage();
        $result->startedAt = $array['started_at'];
        $result->uuid = $array['uuid'];
        $result->actionMessage = ActionMessage::fromArray($array[MessageType::ACTION]);

        return $result;
    }

}
