<?php

namespace EgalFramework\Model\Tests\Samples\Stubs;

use EgalFramework\Common\Interfaces\FieldInterface;

/**
 * @method setInCreateForm(bool $false)
 * @method setRequired(bool $true)
 * @method setRegex(string $string, string $string1)
 * @method setInList(bool $false)
 * @method setModel(string $string)
 * @method setInFilter(bool $true)
 * @method setDefaultValue(string $string)
 * @method setList(string[] $array)
 * @method setTechnicalDescription(string $string)
 * @method setLabel(string $label)
 */
class Field implements FieldInterface
{

    private string $type;
    private array $vars;
    private ?bool $readonly;


    public function __construct(string $type, string $label)
    {
        $this->vars = [];
        $this->setType($type);
        $this->setLabel($label);
    }

    public function __call($name, $arguments)
    {
        if (preg_match('/set(.+)$/', $name, $match)) {
            $this->vars[lcfirst($match[1])] = $arguments[0];
        } elseif (preg_match('/get(.+)$/', $name, $match)) {
            return isset($this->vars[lcfirst($match[1])])
                ? $this->vars[lcfirst($match[1])]
                : null;
        }
        return $this;
    }

    public function setType(string $type)
    {
        $this->type = $type;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setReadonly(bool $readonly): FieldInterface
    {
        $this->readonly = $readonly;
        return $this;
    }

    public function getReadonly(): bool
    {
        return isset($this->readonly) && $this->readonly;
    }

    public function getReadonlyOnCreate(): bool
    {
        return isset($this->readonlyOnCreate) && $this->readonlyOnCreate;
    }

    public function getReadonlyOnChange(): bool
    {
        // TODO: Implement getReadonlyOnChange() method.
    }

    public function getRelation(): ?string
    {
        // TODO: Implement getRelation() method.
    }

    public function getDefaultSortOrder(): string
    {
        // TODO: Implement getDefaultSortOrder() method.
    }
}
