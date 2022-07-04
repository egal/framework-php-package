<?php

namespace Egal\Interface\Metadata\Widgets\Input;

use Egal\Interface\Metadata\Configuration;

class InputInterfaceConfiguration extends Configuration
{
    protected InputType $type = InputType::Text;

    protected string $placeholder = '';

    protected string $error = '';

    protected bool $showSuccess = false;

    protected bool $showFilled = true;

    protected ?array $modelValue = null;

    protected bool $disabled = false;

    protected array $validators = [];

    protected string $helperText = '';

    protected string $iconLeft = '';

    protected string $iconRight = '';

    protected InputSize $size = InputSize::Md;

    protected bool $showError = true;

    protected bool $required = false;

    protected bool $showArrows = true;

    protected ?int $min = null;

    protected ?int $max = null;

    protected ?int $inputMaxLength = null;

    protected bool $clearable = true;

    protected string $postfix = '';

    protected string $labelDisabledColor = '';

    protected string $valueDisabledColor = '';

    protected string $helperDisabledColor = '';

    protected bool $showSuccessIcon = false;

    public static function make(): self
    {
        return new self();
    }

    public function getType(): string
    {
        return $this->type->value;
    }

    public function setType(InputType $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getPlaceholder(): string
    {
        return $this->placeholder;
    }

    public function setPlaceholder(string $placeholder): self
    {
        $this->placeholder = $placeholder;

        return $this;
    }

    public function getError(): string
    {
        return $this->error;
    }

    public function setError(string $error): self
    {
        $this->error = $error;

        return $this;
    }

    public function getShowSuccess(): bool
    {
        return $this->showSuccess;
    }

    public function setShowSuccess(bool $showSuccess): self
    {
        $this->showSuccess = $showSuccess;

        return $this;
    }

    public function getShowFilled(): bool
    {
        return $this->showFilled;
    }

    public function setShowFilled(bool $showFilled): self
    {
        $this->showFilled = $showFilled;

        return $this;
    }

    public function getModelValue(): ?array
    {
        return $this->modelValue;
    }

    public function setModelValue(?array $modelValue): self
    {
        $this->modelValue = $modelValue;

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

    public function getValidators(): array
    {
        return $this->validators;
    }

    public function setValidators(array $validators): self
    {
        $this->validators = $validators;

        return $this;
    }

    public function getHelperText(): string
    {
        return $this->helperText;
    }

    public function setHelperText(string $helperText): self
    {
        $this->helperText = $helperText;

        return $this;
    }

    public function getIconLeft(): string
    {
        return $this->iconLeft;
    }

    public function setIconLeft(string $iconLeft): self
    {
        $this->iconLeft = $iconLeft;

        return $this;
    }

    public function getIconRight(): string
    {
        return $this->iconRight;
    }

    public function setIconRight(string $iconRight): self
    {
        $this->iconRight = $iconRight;

        return $this;
    }

    public function getSize(): string
    {
        return $this->size->value;
    }

    public function setSize(InputSize $size): self
    {
        $this->size = $size;

        return $this;
    }

    public function getShowError(): bool
    {
        return $this->showError;
    }

    public function setShowError(bool $showError): self
    {
        $this->showError = $showError;

        return $this;
    }

    public function getRequired(): bool
    {
        return $this->required;
    }

    public function setRequired(bool $required): self
    {
        $this->required = $required;

        return $this;
    }

    public function getShowArrows(): bool
    {
        return $this->showArrows;
    }

    public function setShowArrows(bool $showArrows): self
    {
        $this->showArrows = $showArrows;

        return $this;
    }

    public function getMin(): ?int
    {
        return $this->min;
    }

    public function setMin(?int $min): self
    {
        $this->min = $min;

        return $this;
    }

    public function getMax(): ?int
    {
        return $this->max;
    }

    public function setMax(?int $max): self
    {
        $this->max = $max;

        return $this;
    }

    public function getInputMaxLength(): ?int
    {
        return $this->inputMaxLength;
    }

    public function setInputMaxLength(?int $inputMaxLength): self
    {
        $this->inputMaxLength = $inputMaxLength;

        return $this;
    }

    public function getClearable(): bool
    {
        return $this->clearable;
    }

    public function setClearable(bool $clearable): self
    {
        $this->clearable = $clearable;

        return $this;
    }

    public function getPostfix(): string
    {
        return $this->postfix;
    }

    public function setPostfix(string $postfix): self
    {
        $this->postfix = $postfix;

        return $this;
    }

    public function getLabelDisabledColor(): string
    {
        return $this->labelDisabledColor;
    }

    public function setLabelDisabledColor(string $labelDisabledColor): self
    {
        $this->labelDisabledColor = $labelDisabledColor;

        return $this;
    }

    public function getValueDisabledColor(): string
    {
        return $this->valueDisabledColor;
    }

    public function setValueDisabledColor(string $valueDisabledColor): self
    {
        $this->valueDisabledColor = $valueDisabledColor;

        return $this;
    }

    public function getHelperDisabledColor(): string
    {
        return $this->helperDisabledColor;
    }

    public function setHelperDisabledColor(string $helperDisabledColor): self
    {
        $this->helperDisabledColor = $helperDisabledColor;

        return $this;
    }

    public function getShowSuccessIcon(): bool
    {
        return $this->showSuccessIcon;
    }

    public function setShowSuccessIcon(bool $showSuccessIcon): self
    {
        $this->showSuccessIcon = $showSuccessIcon;

        return $this;
    }
}
