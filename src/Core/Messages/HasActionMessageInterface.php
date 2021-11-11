<?php

namespace Egal\Core\Messages;

interface HasActionMessageInterface
{

    public function getActionMessage(): ActionMessage;

    public function setActionMessage(ActionMessage $actionMessage): void;

}
