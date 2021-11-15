<?php

declare(strict_types=1);

namespace Egal\Core\Messages;

interface HasActionMessageInterface
{

    public function getActionMessage(): ActionMessage;

    public function setActionMessage(ActionMessage $actionMessage): void;

}
