<?php

namespace Egal\Interface\Metadata\Components\Table;

use Egal\Interface\Metadata\Components\Table\Computed\ComputedInterface;
use Egal\Interface\Metadata\Configuration;
use Egal\Interface\Metadata\Metadata;

class TableField extends Configuration
{
    // TODO CompositeField

    protected string $label;

    protected string $path;

    protected FieldType $type;

    protected bool $sortable = false;

    protected bool $searchable = false;

    protected bool $filterable = false;

    protected ?ComputedInterface $computed = null;

    protected ?Metadata $editWidget = null;

    public static function make(string $label, string $path, FieldType $type): self
    {
        $tableField = new self();
        $tableField->label = $label;
        $tableField->type = $type;
        $tableField->path = $path;

        return $tableField;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getType(): string
    {
        return $this->type->value;
    }

    public function getSortable(): bool
    {
        return $this->sortable;
    }

    public function getSearchable(): bool
    {
        return $this->searchable;
    }

    public function getFilterable(): bool
    {
        return $this->filterable;
    }

    public function getComputed(): array
    {
        return isset($this->computed) ? $this->computed->toArray() : [];
    }

    public function getEditWidget(): array
    {
        return isset($this->editWidget) ? $this->editWidget->toArray() : [];
    }

    public function setSortable(bool $sortable): self
    {
        $this->sortable = $sortable;

        return $this;
    }

    public function setSearchable(bool $searchable): self
    {
        $this->searchable = $searchable;

        return $this;
    }

    public function setFilterable(bool $filterable): self
    {
        $this->filterable = $filterable;

        return $this;
    }

    public function setComputed(?ComputedInterface $computed): self
    {
        $this->computed = $computed;

        return $this;
    }

    public function setEditWidget(?Metadata $widget): self
    {
        $this->editWidget = $widget;

        return $this;
    }
}
