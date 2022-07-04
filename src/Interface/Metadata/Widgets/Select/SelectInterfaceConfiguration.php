<?php

namespace Egal\Interface\Metadata\Widgets\Select;

use Egal\Interface\Metadata\Configuration;

class SelectInterfaceConfiguration extends Configuration
{
    protected string $placeholder = '';

    protected string $helperText = '';

    protected string $error = '';

    protected array $modelValue = [];

    protected array $options = [];

    protected string $shownKey = 'name';

    protected array $validators = [];

    protected SelectSize $size = SelectSize::Md;

    protected bool $showError = true;

    protected bool $clearable = true;

    protected bool $multiple = false;

    protected bool $searchable = false;

    protected bool $searchableInput = false;

    protected string $searchPlaceholder = 'Search';

    protected string $emptyDropdownText = 'no data';

    protected bool $grouped = false;

    protected bool $isLocalOptions = true;

    protected bool $showMoreButtonDisplay = false;

    protected bool $closeDropdownAfterSelection = true;

    protected int $nonLocalOptionsTotalCount = 0;

    protected string $showMoreButtonText = 'Show more...';

    protected array $dropdownStyleConfig = [];

    protected array $inputSearchStyleConfig = [];

    protected bool $showFilled = false;

    protected string $shownBadgeKey = '';

    protected bool $showSuccess = false;

    protected array $inputConfig = [];

    protected SelectDropdownPosition $dropdownPosition = SelectDropdownPosition::Bottom;

    public static function make(): self
    {
        return new self();
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

    public function getHelperText(): string
    {
        return $this->helperText;
    }

    public function setHelperText(string $helperText): self
    {
        $this->helperText = $helperText;

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

    public function getModelValue(): array
    {
        return $this->modelValue;
    }

    public function setModelValue(array $modelValue): self
    {
        $this->modelValue = $modelValue;

        return $this;
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    public function setOptions(array $options): self
    {
        $this->options = $options;

        return $this;
    }

    public function getShownKey(): string
    {
        return $this->shownKey;
    }

    public function setShownKey(string $shownKey): self
    {
        $this->shownKey = $shownKey;

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

    public function getSize(): string
    {
        return $this->size->value;
    }

    public function setSize(SelectSize $size): self
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

    public function getClearable(): bool
    {
        return $this->clearable;
    }

    public function setClearable(bool $clearable): self
    {
        $this->clearable = $clearable;

        return $this;
    }

    public function getMultiple(): bool
    {
        return $this->multiple;
    }

    public function setMultiple(bool $multiple): self
    {
        $this->multiple = $multiple;

        return $this;
    }

    public function getSearchable(): bool
    {
        return $this->searchable;
    }

    public function setSearchable(bool $searchable): self
    {
        $this->searchable = $searchable;

        return $this;
    }

    public function getSearchableInput(): bool
    {
        return $this->searchableInput;
    }

    public function setSearchableInput(bool $searchableInput): self
    {
        $this->searchableInput = $searchableInput;

        return $this;
    }

    public function getSearchPlaceholder(): string
    {
        return $this->searchPlaceholder;
    }

    public function setSearchPlaceholder(string $searchPlaceholder): self
    {
        $this->searchPlaceholder = $searchPlaceholder;

        return $this;
    }

    public function getEmptyDropdownText(): string
    {
        return $this->emptyDropdownText;
    }

    public function setEmptyDropdownText(string $emptyDropdownText): self
    {
        $this->emptyDropdownText = $emptyDropdownText;

        return $this;
    }

    public function getGrouped(): bool
    {
        return $this->grouped;
    }

    public function setGrouped(bool $grouped): self
    {
        $this->grouped = $grouped;

        return $this;
    }

    public function getIsLocalOptions(): bool
    {
        return $this->isLocalOptions;
    }

    public function setIsLocalOptions(bool $isLocalOptions): self
    {
        $this->isLocalOptions = $isLocalOptions;

        return $this;
    }

    public function getDropdownPosition(): string
    {
        return $this->dropdownPosition->value;
    }

    public function setDropdownPosition(SelectDropdownPosition $dropdownPosition): self
    {
        $this->dropdownPosition = $dropdownPosition;

        return $this;
    }

    public function getShowMoreButtonDisplay(): bool
    {
        return $this->showMoreButtonDisplay;
    }

    public function setShowMoreButtonDisplay(bool $showMoreButtonDisplay): self
    {
        $this->showMoreButtonDisplay = $showMoreButtonDisplay;

        return $this;
    }

    public function getCloseDropdownAfterSelection(): bool
    {
        return $this->closeDropdownAfterSelection;
    }

    public function setCloseDropdownAfterSelection(bool $closeDropdownAfterSelection): self
    {
        $this->closeDropdownAfterSelection = $closeDropdownAfterSelection;

        return $this;
    }

    public function getNonLocalOptionsTotalCount(): int
    {
        return $this->nonLocalOptionsTotalCount;
    }

    public function setNonLocalOptionsTotalCount(int $nonLocalOptionsTotalCount): self
    {
        $this->nonLocalOptionsTotalCount = $nonLocalOptionsTotalCount;

        return $this;
    }

    public function getShowMoreButtonText(): string
    {
        return $this->showMoreButtonText;
    }

    public function setShowMoreButtonText(string $showMoreButtonText): self
    {
        $this->showMoreButtonText = $showMoreButtonText;

        return $this;
    }

    public function getDropdownStyleConfig(): string
    {
        return json_encode($this->dropdownStyleConfig);
    }

    public function setDropdownStyleConfig(array $dropdownStyleConfig): self
    {
        $this->dropdownStyleConfig = $dropdownStyleConfig;

        return $this;
    }

    public function getInputSearchStyleConfig(): string
    {
        return json_encode($this->inputSearchStyleConfig);
    }

    public function setInputSearchStyleConfig(array $inputSearchStyleConfig): self
    {
        $this->inputSearchStyleConfig = $inputSearchStyleConfig;

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

    public function getShownBadgeKey(): string
    {
        return $this->shownBadgeKey;
    }

    public function setShownBadgeKey(string $shownBadgeKey): self
    {
        $this->shownBadgeKey = $shownBadgeKey;

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

    public function getInputConfig(): string
    {
        return json_encode($this->inputConfig);
    }

    public function setInputConfig(array $inputConfig): self
    {
        $this->inputConfig = $inputConfig;

        return $this;
    }

}
