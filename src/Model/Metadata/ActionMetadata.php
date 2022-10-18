<?php

declare(strict_types=1);

namespace Egal\Model\Metadata;

use Egal\Model\Exceptions\ActionParameterNotFoundException;

/**
 * @package Egal\Model
 */
class ActionMetadata
{

    // TODO: добавить метаданные доступов
    // TODO: добавить примеры запроса-ответа

    public const METHOD_NAME_PREFIX = 'action';

    protected readonly string $name;

    /**
     * @var ActionParameterMetadata[]
     */
    protected array $parameters = [];

    private ?array $validationRules = null;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public static function make(string $name): self
    {
        return new static($name);
    }

    private function setValidationRules(): void
    {
        $this->validationRules = [];

        foreach ($this->getParameters() as $parameter) {
            $this->setValidationRule($parameter);
        }
    }

    private function setValidationRule(ActionParameterMetadata $parameter): void
    {
        $this->validationRules[$parameter->getName()] = $parameter->getValidationRules();
    }

    public function parameterExist(string $parameterName): bool
    {
        foreach ($this->parameters as $parameter) {
            if ($parameter->getName() === $parameterName) {
                return true;
            }
        }

        return false;
    }

    /**
     * @throws ActionParameterNotFoundException
     */
    public function parameterExistOrFail(string $parameterName): bool
    {
        if (!$this->parameterExist($parameterName)) {
            throw ActionParameterNotFoundException::make($parameterName);
        }

        return true;
    }

    /**
     * @param ActionParameterMetadata[] $parameters
     */
    public function addParameters(array $parameters): self
    {
        $this->parameters = array_merge($this->parameters, $parameters);

        return $this;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        $actionMetadata = [];
        $actionMetadata['name'] = $this->name;
        $actionMetadata['parameters'] = array_map(static fn (ActionParameterMetadata $parameter) => $parameter->toArray(), $this->parameters);

        return $actionMetadata;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getMethodName(): string
    {
        return self::METHOD_NAME_PREFIX . ucwords($this->name);
    }

    public function getParameters(): array
    {
        return $this->parameters;
    }

    public function getValidationRules(): array
    {
        if (!isset($this->validationRules)) {
            $this->setValidationRules();
        }

        return $this->validationRules;
    }

}
