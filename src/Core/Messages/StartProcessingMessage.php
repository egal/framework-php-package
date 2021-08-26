<?php

namespace Egal\Core\Messages;

use Egal\Core\Exceptions\InitializeMessageFromArrayException;
use Egal\Core\Exceptions\UndefinedTypeOfMessageException;
use Exception;
use Illuminate\Support\Carbon;

class StartProcessingMessage extends Message
{

    use HasActionMessage;

    protected string $type = MessageType::START_PROCESSING;
    protected Carbon $startedAt;

    public function __construct()
    {
        parent::__construct();
        $this->startedAt = Carbon::now('UTC');
    }

    /**
     * @param array $array
     * @return StartProcessingMessage
     * @throws Exception
     */
    public static function fromArray(array $array): StartProcessingMessage
    {
        if (!isset($array['type'])) {
            throw new UndefinedTypeOfMessageException();
        }
        if ($array['type'] !== MessageType::START_PROCESSING) {
            throw new InitializeMessageFromArrayException('Invalid type substitution!');
        }

        $result = new StartProcessingMessage();
        $result->startedAt = Carbon::parse($array['started_at']);
        $result->uuid = $array['uuid'];

        if (isset($array['action_message'])) {
            $result->actionMessage = ActionMessage::fromArray($array['action_message']);
        }

        return $result;
    }

}
