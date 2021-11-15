<?php

declare(strict_types=1);

namespace Egal\Core\Communication;

use Egal\Core\Exceptions\ReplyMessageNotBelongToRequestException;
use Egal\Core\Exceptions\ResponseException;
use Egal\Core\Exceptions\UnsupportedReplyMessageTypeException;
use Egal\Core\Messages\ActionErrorMessage;
use Egal\Core\Messages\ActionMessage;
use Egal\Core\Messages\ActionResultMessage;
use Egal\Core\Messages\Message;
use Egal\Core\Messages\StartProcessingMessage;

class Response
{

    private ActionMessage $actionMessage;

    private ?StartProcessingMessage $startProcessingMessage = null;

    private ?ActionResultMessage $actionResultMessage = null;

    private ?ActionErrorMessage $actionErrorMessage = null;

    private int $statusCode = 500;

    private ?string $internalCode = null;

    private ?string $errorMessage = null;

    public function getActionMessage(): ActionMessage
    {
        return $this->actionMessage;
    }

    public function setActionMessage(ActionMessage $actionMessage): void
    {
        $this->actionMessage = $actionMessage;
    }

    public function getStartProcessingMessage(): ?StartProcessingMessage
    {
        return $this->startProcessingMessage;
    }

    public function setStartProcessingMessage(?StartProcessingMessage $startProcessingMessage): void
    {
        $this->startProcessingMessage = $startProcessingMessage;
    }

    public function getActionResultMessage(): ?ActionResultMessage
    {
        return $this->actionResultMessage;
    }

    public function setActionResultMessage(?ActionResultMessage $actionResultMessage): void
    {
        $this->actionResultMessage = $actionResultMessage;
    }

    public function getStatusCode(): int
    {
        if ($this->getActionErrorMessage()) {
            $statusCode = $this->getActionErrorMessage()->getCode();

            return $statusCode !== 0
                ? $statusCode
                : 500;
        }

        return 200;
    }

    public function getErrorMessage(): ?string
    {
        return $this->getActionErrorMessage()->getMessage();
    }

    public function getActionErrorMessage(): ?ActionErrorMessage
    {
        return $this->actionErrorMessage;
    }

    public function setActionErrorMessage(?ActionErrorMessage $actionErrorMessage): void
    {
        $this->actionErrorMessage = $actionErrorMessage;
    }

    public function isActionErrorMessageExists(): bool
    {
        return isset($this->actionErrorMessage);
    }

    public function throwActionErrorMessageIfExists(): void
    {
        if ($this->isActionErrorMessageExists()) {
            throw new ResponseException(
                $this->getActionErrorMessage()->getMessage(),
                $this->getActionErrorMessage()->getCode()
            );
        }
    }

    public function collectReplyMessage(Message $replyMessage): void
    {
        $checkAffiliation = function ($message): void {
            /** @var \Egal\Core\Messages\ActionResultMessage|\Egal\Core\Messages\ActionErrorMessage|\Egal\Core\Messages\StartProcessingMessage $message */
            if ($message->getActionMessage()->getUuid() !== $this->getActionMessage()->getUuid()) {
                throw new ReplyMessageNotBelongToRequestException();
            }
        };

        if ($replyMessage instanceof ActionResultMessage) {
            $checkAffiliation($replyMessage);
            $this->setActionResultMessage($replyMessage);
        } elseif ($replyMessage instanceof ActionErrorMessage) {
            $checkAffiliation($replyMessage);
            $this->setActionErrorMessage($replyMessage);
        } elseif ($replyMessage instanceof StartProcessingMessage) {
            $checkAffiliation($replyMessage);
            $this->setStartProcessingMessage($replyMessage);
        } else {
            throw new UnsupportedReplyMessageTypeException();
        }
    }

    public function isReplyMessagesCollected(): bool
    {
        return isset($this->actionErrorMessage) || isset($this->actionResultMessage);
    }

}
