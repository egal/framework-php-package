<?php

declare(strict_types=1);

namespace Egal\Model\Metadata;

/**
 * @package Egal\Model
 */
class ActionMetadata
{

    // TODO: добавить метаданные доступов
    // TODO: добавить генерацию входных параметров
    // TODO: добавить обработку дефолтных значений параметров
    // TODO: добавить правила валидации для action
    // TODO: добавить примеры запроса-ответа
    public const METHOD_NAME_PREFIX = 'action';

    protected readonly string $name;

    protected function __construct(string $name)
    {
        $this->name = $name;
    }

    public static function make(string $name): self
    {
        return new static($name);
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        $actionMetadata = [];
        $actionMetadata['name'] = $this->name;

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

}
