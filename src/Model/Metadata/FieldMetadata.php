<?php

declare(strict_types=1);

namespace Egal\Model\Metadata;

use Egal\Model\Traits\VariableMetadata;

class FieldMetadata
{

    use VariableMetadata {
        required as requiredVariableMetadata;
    }

    protected bool $hidden = false;

    protected bool $guarded = false;

    protected bool $required = false;

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'type' => $this->type->value,
            'required' => $this->required,
            'hidden' => $this->hidden,
            'guarded' => $this->guarded,
            'default' => $this->default,
            'nullable' => $this->nullable,
            'validationRules' => $this->validationRules,
        ];
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

    public function required(): self
    {
        $this->required = true;
        $this->requiredVariableMetadata();

        return $this;
    }

    public function isRequired(): bool
    {
        return $this->required;
    }

    public function isHidden(): bool
    {
        return $this->hidden;
    }

    public function isGuarded(): bool
    {
        return $this->guarded;
    }

}
