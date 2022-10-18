<?php

declare(strict_types=1);

namespace Egal\Model\Metadata;

use Egal\Model\Traits\VariableMetadata;

class FieldMetadata
{

    use VariableMetadata;

    protected bool $hidden = false;

    protected bool $guarded = false;

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

    public function isHidden(): bool
    {
        return $this->hidden;
    }

    public function isGuarded(): bool
    {
        return $this->guarded;
    }

}
