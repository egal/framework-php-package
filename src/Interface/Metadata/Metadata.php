<?php

namespace Egal\Interface\Metadata;

abstract class Metadata
{
    protected string $type;

    protected string $label;

    protected ConfigurationInterface $interfaceConfig;

    protected ?ConfigurationInterface $dataConfig;

    public static function make(string $label, ConfigurationInterface $interfaceConfig, ?ConfigurationInterface $dataConfig = null): self
    {
        $metadata = new static();
        $metadata->label = $label;
        $metadata->interfaceConfig = $interfaceConfig;
        $metadata->dataConfig = $dataConfig;
        $metadata->type = class_basename(static::class);

        return $metadata;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function getInterfaceConfig(): ConfigurationInterface
    {
        return $this->interfaceConfig;
    }

    public function getDataConfig(): ConfigurationInterface
    {
        return $this->dataConfig;
    }

    public function toArray(): array
    {
        return [
            'type' => $this->type,
            'label' => $this->label,
            'content' => isset($this->interfaceConfig) ? $this->interfaceConfig->toArray() : null,
            'data' => isset($this->dataConfig) ? $this->dataConfig->toArray() : null
        ];
    }
}
