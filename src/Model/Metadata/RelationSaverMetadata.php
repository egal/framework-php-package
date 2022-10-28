<?php

namespace Egal\Model\Metadata;

use Closure;

class RelationSaverMetadata
{

    protected Closure $callback;

    protected array $valueValidationRules = ['value' => []];

    public function __construct(Closure $callback)
    {
        $this->callback = $callback;
    }

    public static function make(Closure $callback): self
    {
        return new static($callback);
    }

    public function getCallback(): Closure
    {
        return $this->callback;
    }

    public function addValueValidationRule(string|object $rule): self
    {
        $this->valueValidationRules['value'][] = $rule;

        return $this;
    }

    public function addValueContentValidationRule(string|object $rule): self
    {
        $this->valueValidationRules['value.*'][] = $rule;

        return $this;
    }

    public function getValueValidationRules(): array
    {
        return $this->valueValidationRules;
    }

}
