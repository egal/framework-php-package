<?php

declare(strict_types=1);

namespace Egal\Core\Messages;

class MessageType
{

    public const ACTION = 'action';
    public const ACTION_RESULT = 'action_result';
    public const ACTION_ERROR = 'action_error';
    public const START_PROCESSING = 'start_processing';
    public const EVENT = 'event';

}
