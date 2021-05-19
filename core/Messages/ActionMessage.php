<?php

namespace Egal\Core\Messages;

use Egal\Core\Exceptions\InitializeMessageFromArrayException;
use Egal\Core\Exceptions\UndefinedTypeOfMessageException;
use Egal\Core\Exceptions\ActionMessageException;
use Exception;

class ActionMessage extends Message
{

    protected string $type = MessageType::ACTION;
    protected string $serviceName;
    protected string $modelName;
    protected string $actionName;
    protected array $parameters;

    protected ?string $token;

    public function __construct(
        string $serviceName,
        string $modelName,
        string $actionName,
        array $parameters = [],
        ?string $token = null
    )
    {
        parent::__construct();
        $this->serviceName = $serviceName;
        $this->modelName = $modelName;
        $this->actionName = $actionName;
        $this->parameters = $parameters;
        $this->token = $token;
    }

    /**
     * @param array $array
     * @return ActionMessage
     * @throws InitializeMessageFromArrayException
     * @throws UndefinedTypeOfMessageException
     */
    public static function fromArray(array $array): ActionMessage
    {
        if (!isset($array['type'])) {
            throw new UndefinedTypeOfMessageException();
        }
        if ($array['type'] !== MessageType::ACTION) {
            throw new InitializeMessageFromArrayException('Invalid type substitution!');
        }

        $actionMessage = new ActionMessage(
            $array['service_name'],
            $array['model_name'],
            $array['action_name'],
            $array['parameters'],
            $array['token'] ?? null
        );

        $actionMessage->uuid = $array['uuid'];

        return $actionMessage;
    }

    /**
     * @param $key
     * @param $value
     * @throws Exception
     */
    public function addParameter($key, $value)
    {
        if (isset($this->parameters[$key])) {
            throw new ActionMessageException("Duplicate $key parameter!");
        }
        $this->parameters[$key] = $value;
    }

    /**
     * @param array $array
     * @throws Exception
     */
    public function addParameters(array $array)
    {
        foreach ($array as $key => $value) {
            $this->addParameter($key, $value);
        }
    }

    public function isTokenExist(): bool
    {
        return isset($this->token) && $this->token;
    }

    /**
     * @return string|null
     */
    public function getToken(): ?string
    {
        return $this->token;
    }

    /**
     * @return string
     */
    public function getModelName(): string
    {
        return $this->modelName;
    }

    /**
     * @param string $modelName
     */
    public function setModelName(string $modelName): void
    {
        $this->modelName = $modelName;
    }

    /**
     * @return string
     */
    public function getActionName(): string
    {
        return $this->actionName;
    }

    /**
     * @param string $actionName
     */
    public function setActionName(string $actionName): void
    {
        $this->actionName = $actionName;
    }

    /**
     * @return array
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    /**
     * @param array $parameters
     */
    public function setParameters(array $parameters): void
    {
        $this->parameters = $parameters;
    }

    /**
     * @return string
     */
    public function getServiceName(): string
    {
        return $this->serviceName;
    }

}
