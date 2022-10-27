<?php

declare(strict_types=1);

namespace Egal\Model\Traits;

use BackedEnum;
use Egal\Model\Enums\VariableType;
use Egal\Model\Enums\ValidationRules;
use Egal\Model\Exceptions\ValidateException;
use Egal\Validation\Rules\Rule as EgalRule;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Validator;

trait VariableMetadata
{

    use VariableValidationRules;

    protected readonly string $name;

    protected readonly VariableType $type;

    protected mixed $default = null;

    protected bool $nullable = false;

    /**
     * @var array<string, Rule, EgalRule>
     */
    protected array $validationRules = [];

    protected function __construct(string $name, VariableType $type)
    {
        $this->name = $name;
        $this->type = $type;

        switch ($type) {
            case VariableType::DATETIME:
                break;
            default:
                $this->addValidationRule($type->value);
                break;
        }
    }

    public static function make(string $name, VariableType $type): static
    {
        return new static($name, $type);
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'type' => $this->type->value,
            'default' => $this->default,
            'nullable' => $this->nullable,
            'validationRules' => $this->validationRules,
        ];
    }

    /**
     * @throws ValidateException
     */
    public function default(mixed $value): static
    {
        if ($value instanceof BackedEnum) $value = $value->value;

        $validator = Validator::make(
            [$this->getName() => $value],
            [$this->getName() => $this->getValidationRules()]
        );

        if ($validator->fails()) {
            if ($validator->fails()) {
                $exception = new ValidateException();
                $exception->setMessageBag($validator->errors());

                throw $exception;
            }
        }

        $this->default = $value;

        return $this;
    }

    public function nullable(): static
    {
        $this->nullable = true;
        $this->validationRules[] = ValidationRules::NULLABLE->value;

        return $this;
    }

    public function addValidationRule(string|object $validationRule): static
    {
        $this->validationRules[] = $validationRule;

        return $this;
    }

    public function isNullable(): bool
    {
        return $this->nullable;
    }

    public function getDefault(): mixed
    {
        return $this->default;
    }

    public function getValidationRules(): array
    {
        return $this->validationRules;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getType(): VariableType
    {
        return $this->type;
    }

}
