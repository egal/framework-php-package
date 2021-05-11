<?php

namespace EgalFramework\CommandLine\Tests\Stubs;

use EgalFramework\Common\Interfaces\FieldInterface;

class Field implements FieldInterface
{

    private string $type;

    private string $name;

    private bool $readonly;

    public function __construct(string $type, string $name)
    {
        $this->type = $type;
        $this->name = $name;
        $this->readonly = $name == '#';
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getReadonly(): bool
    {
        return $this->readonly;
    }

    public function setInChangeForm(): FieldInterface
    {
        return $this;
    }

    public function setInCreateForm(): FieldInterface
    {
        return $this;
    }

    public function setRequired()
    {
        return $this;
    }

    public function setReadonly(bool $readonly): FieldInterface
    {
        // TODO: Implement setReadonly() method.
    }

    public function getReadonlyOnCreate(): bool
    {
        // TODO: Implement getReadonlyOnCreate() method.
    }

    public function getReadonlyOnChange(): bool
    {
        // TODO: Implement getReadonlyOnChange() method.
    }

    public function getRelation(): ?string
    {
        // TODO: Implement getRelation() method.
    }

    public function getDefaultSortOrder(): string
    {
        // TODO: Implement getDefaultSortOrder() method.
    }
}
