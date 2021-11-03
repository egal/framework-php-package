<?php

namespace Egal\Core\Communication;

use Egal\Core\Exceptions\ResponseException;
use Egal\Core\Messages\ActionErrorMessage;
use Egal\Core\Messages\ActionMessage;
use Egal\Core\Messages\ActionResultMessage;
use Egal\Core\Messages\StartProcessingMessage;

/**
 * Class Response
 * @package Egal\Core\Communication
 */
class Response
{
    const OK_STATUS_CODE = 'OK';
    const INTERNAL_SERVER_ERROR_STATUS_CODE = 'Internal Server Error';

    private ActionMessage $actionMessage;
    private ?StartProcessingMessage $startProcessingMessage = null;
    private ?ActionResultMessage $actionResultMessage = null;
    private ?ActionErrorMessage $actionErrorMessage = null;
    private ?string $statusCode = null;
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
     * @return string
     */
    public function getStatusCode(): ?string
    {
        return $this->statusCode;
    }

    /**
     * @param string|null $statusCode
     */
    public function setStatusCode(?string $statusCode): void
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

    /**
     * @throws ResponseException
     */
    public function throwActionErrorMessageIfExists()
    {
        if ($this->isActionErrorMessageExists()) {
            throw new ResponseException(
                $this->getActionErrorMessage()->getMessage(),
                $this->getActionErrorMessage()->getInternalCode()
            );
        }
    }

}
