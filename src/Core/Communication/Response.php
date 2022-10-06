<?php

declare(strict_types=1);

namespace Egal\Core\Communication;

use Egal\Core\Exceptions\ImpossibilityDeterminingStatusOfResponseException;
use Egal\Core\Exceptions\NoResultMessageException;
use Egal\Core\Exceptions\ResponseException;
use Egal\Core\Exceptions\UnsupportedReplyMessageTypeException;
use Egal\Core\Messages\ActionErrorMessage;
use Egal\Core\Messages\ActionMessage;
use Egal\Core\Messages\ActionResultMessage;
use Egal\Core\Messages\HasActionMessageInterface;
use Egal\Core\Messages\Message;
use Egal\Core\Messages\StartProcessingMessage;

class Response
{

    private ActionMessage $actionMessage;

    private ?StartProcessingMessage $startProcessingMessage = null;

    private ?ActionResultMessage $actionResultMessage = null;

    private ?ActionErrorMessage $actionErrorMessage = null;

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
        if (!($replyMessage instanceof HasActionMessageInterface)) {
            throw new UnsupportedReplyMessageTypeException();
        }

        if ($replyMessage->getActionMessage()->getUuid() !== $this->getActionMessage()->getUuid()) {
            return;
        }

        if ($replyMessage instanceof ActionResultMessage) {
            $this->setActionResultMessage($replyMessage);
        } elseif ($replyMessage instanceof ActionErrorMessage) {
            $this->setActionErrorMessage($replyMessage);
        } elseif ($replyMessage instanceof StartProcessingMessage) {
            $this->setStartProcessingMessage($replyMessage);
        } else {
            throw new UnsupportedReplyMessageTypeException();
        }
    }

    public function collect(): void
    {
        $switch = [
            $this->getStartProcessingMessage() !== null,
            $this->getActionErrorMessage() !== null,
            $this->getActionResultMessage() !== null,
        ];

        switch ($switch) {
            case [true, false, false]:
                $actionErrorMessage = new ActionErrorMessage();
                $actionErrorMessage->setCode(500);
                $actionErrorMessage->setMessage(
                    'The service responded, but did not process the request within the allotted time!'
                );
                $this->setActionErrorMessage($actionErrorMessage);
                break;
            case [false, false, false]:
                $actionErrorMessage = new ActionErrorMessage();
                $actionErrorMessage->setCode(500);
                $actionErrorMessage->setMessage('Service not responding!');
                $this->setActionErrorMessage($actionErrorMessage);
                break;
            case [true, false, true]:
            case [true, true, false]:
                break;
            case [false, true, true]:
            case [false, true, false]:
            case [false, false, true]:
            case [true, true, true]:
            default:
                throw new ImpossibilityDeterminingStatusOfResponseException();
        }
    }

    public function isReplyMessagesCollected(): bool
    {
        return isset($this->actionErrorMessage) || isset($this->actionResultMessage);
    }

    /**
     * @return mixed
     * @throws \Egal\Core\Exceptions\NoResultMessageException
     */
    public function getResultData()
    {
        $actionResultMessage = $this->getActionResultMessage();

        if ($actionResultMessage === null) {
            $error = $this->getErrorMessage();

            throw new NoResultMessageException($error, $this->getStatusCode());
        }

        return $actionResultMessage->getData();
    }

}
