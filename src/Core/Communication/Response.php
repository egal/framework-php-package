<?php

namespace Egal\Core\Communication;

use Egal\Core\Exceptions\ReplyMessageNotBelongToRequestException;
use Egal\Core\Exceptions\ResponseException;
use Egal\Core\Exceptions\UnsupportedReplyMessageTypeException;
use Egal\Core\Messages\ActionErrorMessage;
use Egal\Core\Messages\ActionMessage;
use Egal\Core\Messages\ActionResultMessage;
use Egal\Core\Messages\Message;
use Egal\Core\Messages\MessageType;
use Egal\Core\Messages\StartProcessingMessage;

/**
 * Class Response
 * @package Egal\Core\Communication
 */
class Response
{

    private ActionMessage $actionMessage;
    private ?StartProcessingMessage $startProcessingMessage = null;
    private ?ActionResultMessage $actionResultMessage = null;
    private ?ActionErrorMessage $actionErrorMessage = null;
    private int $statusCode = 500;
    private ?string $errorMessage = null;

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

    /**
     * @return StartProcessingMessage|null
     */
    public function getStartProcessingMessage(): ?StartProcessingMessage
    {
        return $this->startProcessingMessage;
    }

    /**
     * @param StartProcessingMessage|null $startProcessingMessage
     */
    public function setStartProcessingMessage(?StartProcessingMessage $startProcessingMessage): void
    {
        $this->startProcessingMessage = $startProcessingMessage;
    }

    /**
     * @return ActionResultMessage|null
     */
    public function getActionResultMessage(): ?ActionResultMessage
    {
        return $this->actionResultMessage;
    }

    /**
     * @param ActionResultMessage|null $actionResultMessage
     */
    public function setActionResultMessage(?ActionResultMessage $actionResultMessage): void
    {
        $this->actionResultMessage = $actionResultMessage;
    }

    /**
     * @return int
     */
    public function getStatusCode(): int
    {
        return $this->statusCode !== 0 ? $this->statusCode : 500;
    }

    /**
     * @param int $statusCode
     */
    public function setStatusCode(int $statusCode): void
    {
        $this->statusCode = $statusCode;
    }

    /**
     * @return string|null
     */
    public function getErrorMessage(): ?string
    {
        return $this->errorMessage;
    }

    /**
     * @param string|null $errorMessage
     */
    public function setErrorMessage(?string $errorMessage): void
    {
        $this->errorMessage = $errorMessage;
    }

    /**
     * @return ActionErrorMessage|null
     */
    public function getActionErrorMessage(): ?ActionErrorMessage
    {
        return $this->actionErrorMessage;
    }

    /**
     * @param ActionErrorMessage|null $actionErrorMessage
     */
    public function setActionErrorMessage(?ActionErrorMessage $actionErrorMessage): void
    {
        $this->actionErrorMessage = $actionErrorMessage;
    }

    public function isActionErrorMessageExists(): bool
    {
        return isset($this->actionErrorMessage);
    }

    public function throwActionErrorMessageIfExists()
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
        $checkAffiliation = function ($message) {
            /** @var ActionResultMessage|ActionErrorMessage|StartProcessingMessage $message */
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
