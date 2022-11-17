<?php

declare(strict_types=1);

namespace Egal\Core\Messages;

use Egal\Core\Exceptions\ActionMessageException;
use Egal\Core\Exceptions\InitializeMessageFromArrayException;
use Egal\Core\Exceptions\UndefinedTypeOfMessageException;

class ActionMessage extends Message
{

    protected string $type = MessageType::ACTION;
    
    protected string $serviceName;
    
    protected string $modelName;
    
    protected string $actionName;

    /**
     * @var mixed[]
     */
    protected array $parameters;

    protected ?string $token;

    public function __construct(
        string $serviceName,
        string $modelName,
        string $actionName,
        array $parameters = [],
        ?string $token = null
    ) {
        parent::__construct();
        
        $this->serviceName = $serviceName;
        $this->modelName = $modelName;
        $this->actionName = $actionName;
        $this->parameters = $parameters;
        $this->token = $token;
    }

    /**
     * @param array $array
     * @throws \Egal\Core\Exceptions\InitializeMessageFromArrayException
     * @throws \Egal\Core\Exceptions\UndefinedTypeOfMessageException
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
            $array['token'] ?? null,
        );

        $actionMessage->uuid = $array['uuid'];

        return $actionMessage;
    }

    /**
     * @param mixed $value
     * @throws \Egal\Core\Exceptions\ActionMessageException
     */
    public function addParameter(string $key, $value): void
    {
        if (isset($this->parameters[$key])) {
            throw new ActionMessageException(sprintf('Duplicate %s parameter!', $key));
        }

        $this->parameters[$key] = $value;
    }

    /**
     * @param mixed[] $array
     * @throws \Exception
     */
    public function addParameters(array $array): void
    {
        foreach ($array as $key => $value) {
            $this->addParameter($key, $value);
        }
    }

    public function isTokenExist(): bool
    {
        return isset($this->token);
    }

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function setToken(?string $token): void
    {
        $this->token = $token;
    }

    public function getModelName(): string
    {
        return $this->modelName;
    }

    public function setModelName(string $modelName): void
    {
        $this->modelName = $modelName;
    }

    public function getActionName(): string
    {
        return $this->actionName;
    }

    public function setActionName(string $actionName): void
    {
        $this->actionName = $actionName;
    }

    /**
     * @return mixed[]
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    /**
     * @param mixed[] $parameters
     */
    public function setParameters(array $parameters): void
    {
        $this->parameters = $parameters;
    }

    public function getServiceName(): string
    {
        return $this->serviceName;
    }

}
