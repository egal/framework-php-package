<?php

namespace Egal\Core\Messages;

trait HasActionMessage
{

    private ActionMessage $actionMessage;

    /**
     * @return ActionMessage
     */
    public function getActionMessage(): ActionMessage
    {
        return $this->actionMessage;
    }

    /**
     * @param ActionMessage $actionMessage
     */
    public function setActionMessage(ActionMessage $actionMessage): void
    {
        $this->actionMessage = $actionMessage;
    }

    public function toArray(): array
    {
        /** @noinspection PhpMultipleClassDeclarationsInspection */
        $result = parent::toArray();
        if (isset($this->actionMessage))  {
            $result['action_message'] = $this->actionMessage->toArray();
        }
        return $result;
    }


}
