<?php

namespace EgalFramework\Metadata;

class FilterField
{

    const TYPE_FIELD = 'field';
    const TYPE_RELATION = 'relation';
    const TYPE_RANGE = 'range';
    const TYPE_MONTH_RANGE = 'month_range';

    private string $type;

    private string $relation;

    /** @var mixed */
    private $defaultValue;

    private ?bool $multiple;

    private string $label;

    public function __construct(string $type, string $relation = '')
    {
        $this->type = $type;
        $this->relation = $relation;
    }

    public function getRelation(): string
    {
        return $this->relation;
    }

    public function setDefaultValue($defaultValue): FilterField
    {
        $this->defaultValue = $defaultValue;
        return $this;
    }

    public function getDefaultValue()
    {
        return $this->defaultValue;
    }

    public function setMultiple(?bool $multiple): FilterField
    {
        $this->multiple = $multiple;
        return $this;
    }

    public function getMultiple(): ?bool
    {
        return isset($this->multiple)
            ? $this->multiple
            : null;
    }

    public function setLabel(string $label): FilterField
    {
        $this->label = $label;
        return $this;
    }

    public function getLabel(): ?string
    {
        return isset($this->label)
            ? $this->label
            : null;
    }

    public function toArray(): array
    {
        $result = ['type' => $this->type];
        if (!empty($this->relation)) {
            $result['relation'] = $this->getRelation();
        }
        if (!empty($this->defaultValue)) {
            $result['defaultValue'] = $this->getDefaultValue();
        }
        if (isset($this->multiple)) {
            $result['multiple'] = $this->getMultiple();
        }
        if (isset($this->label)) {
            $result['label'] = $this->label;
        }
        return $result;
    }

}
