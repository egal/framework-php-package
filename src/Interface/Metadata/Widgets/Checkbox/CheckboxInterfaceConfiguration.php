<?php

namespace Egal\Interface\Metadata\Widgets\Checkbox;

use Egal\Interface\Metadata\Configuration;

class CheckboxInterfaceConfiguration extends Configuration
{
    protected bool $checked = false;

    protected bool $disabled = false;

    protected CheckboxSize $size = CheckboxSize::Md;

    protected bool $checkboxRight = false;

    protected bool $indeterminate = false;

    public static function make(): self
    {
        return new self();
    }

    public function getChecked(): bool
    {
        return $this->checked;
    }

    public function setChecked(bool $checked): self
    {
        $this->checked = $checked;

        return $this;
    }

    public function getDisabled(): bool
    {
        return $this->disabled;
    }

    public function setDisabled(bool $disabled): self
    {
        $this->disabled = $disabled;

        return $this;
    }

    public function getSize(): string
    {
        return $this->size->value;
    }

    public function setSize(CheckboxSize $size): self
    {
        $this->size = $size;

        return $this;
    }

    public function getCheckboxRight(): bool
    {
        return $this->checkboxRight;
    }

    public function setCheckboxRight(bool $checkboxRight): self
    {
        $this->checkboxRight = $checkboxRight;

        return $this;
    }

    public function getIndeterminate(): bool
    {
        return $this->indeterminate;
    }

    public function setIndeterminate(bool $indeterminate): self
    {
        $this->indeterminate = $indeterminate;

        return $this;
    }

}
