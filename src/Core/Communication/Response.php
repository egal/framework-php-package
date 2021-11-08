<?php

declare(strict_types=1);

namespace Egal\Core\Communication;

use Egal\Core\Exceptions\ResponseException;
use Egal\Core\Messages\ActionErrorMessage;
use Egal\Core\Messages\ActionMessage;
use Egal\Core\Messages\ActionResultMessage;
use Egal\Core\Messages\StartProcessingMessage;

/**
 * Class Response
 *
 * @package Egal\Core\Communication
 */
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
        return $this->statusCode !== 0
            ? $this->statusCode
            : 500;
    }

    public function setStatusCode(int $statusCode): void
    {
        $this->statusCode = $statusCode;
    }

    public function getErrorMessage(): ?string
    {
        return $this->errorMessage;
    }

    public function setErrorMessage(?string $errorMessage): void
    {
        $this->errorMessage = $errorMessage;
    }

    public function getInternalCode(): ?string
    {
        return $this->internalCode;
    }

    public function setInternalCode(?string $internalCode): void
    {
        $this->internalCode = $internalCode;
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

    /**
     * @throws \Egal\Core\Exceptions\ResponseException
     */
    public function throwActionErrorMessageIfExists(): void
    {
        if ($this->isActionErrorMessageExists()) {
            throw new ResponseException(
                $this->getActionErrorMessage()->getMessage(),
                $this->getActionErrorMessage()->getCode()
            );
        }
    }

}
