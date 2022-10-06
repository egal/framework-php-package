<?php

namespace Egal\Model\Metadata;

use Egal\Model\Enums\ValidationRules;
use Egal\Model\Traits\FieldValidationRules;
use Egal\Model\Enums\FieldType;
use Egal\Validation\Rules\Rule as EgalRule;
use Illuminate\Contracts\Validation\Rule;

class FieldMetadata
{

    use FieldValidationRules;

    protected readonly string $name;

    protected readonly FieldType $type;

    protected bool $hidden = false;

    protected bool $guarded = false;

    protected mixed $default = null;

    protected bool $nullable = false;

    /**
     * @var array<string, Rule, EgalRule>
     */
    protected array $validationRules = [];

    protected function __construct(string $name, FieldType $type)
    {
        $this->name = $name;
        $this->type = $type;
    }

    public static function make(string $name, FieldType $type): self
    {
        return new static($name, $type);
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'type' => $this->type->value,
            'hidden' => $this->hidden,
            'guarded' => $this->guarded,
            'default' => $this->default,
            'nullable' => $this->nullable,
            'validationRules' => $this->validationRules,
        ];
    }

    public function addValidationRule(string $validationRule): self
    {
        $this->validationRules[] = $validationRule;

        return $this;
    }

    public function hidden(): self
    {
        $this->hidden = true;

        return $this;
    }

    public function guarded(): self
    {
        $this->guarded = true;

        return $this;
    }

    public function default(mixed $defaultValue): self
    {
        $this->default = $defaultValue;

        return $this;
    }

    public function nullable(): self
    {
        $this->nullable = true;
        $this->validationRules[] = ValidationRules::NULLABLE->value;

        return $this;
    }

    public function isHidden(): bool
    {
        return $this->hidden;
    }

    public function isGuarded(): bool
    {
        return $this->guarded;
    }

    public function isNullable(): bool
    {
        return $this->nullable;
    }

    public function getDefault(): mixed
    {
        return $this->default;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getType(): FieldType
    {
        return $this->type;
    }

    public function getValidationRules(): array
    {
        if (in_array($this->type->value, $this->validationRules)) {
            return $this->validationRules;
        }

        switch ($this->type) {
            case FieldType::DATETIME:
                break;
            default:
                array_unshift($this->validationRules, $this->type->value);
        }

        return $this->validationRules;
    }

}
