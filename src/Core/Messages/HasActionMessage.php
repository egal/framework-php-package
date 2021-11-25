<?php

declare(strict_types=1);

namespace Egal\Core\Messages;

trait HasActionMessage
{

    private ActionMessage $actionMessage;

    public function getActionMessage(): ActionMessage
    {
        return $this->actionMessage;
    }

    public function setActionMessage(ActionMessage $actionMessage): void
    {
        $this->actionMessage = $actionMessage;
    }

    public function toArray(): array
    {
        $result = parent::toArray();

        if (isset($this->actionMessage)) {
            $result[MessageType::ACTION] = $this->actionMessage->toArray();
        }

        return $result;
    }

}
