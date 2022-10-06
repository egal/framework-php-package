<?php

declare(strict_types=1);

namespace Egal\Model\Metadata;

use Egal\Model\Enums\RelationType;

class RelationMetadata
{

    protected readonly string $name;

    protected readonly RelationType $type;

    protected function __construct(string $name, RelationType $type)
    {
        $this->name = $name;
        $this->type = $type;
    }

    public static function make(string $name, RelationType $type): self
    {
        return new static($name, $type);
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'type' => $this->type->value,
        ];
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getType(): RelationType
    {
        return $this->type;
    }

}
