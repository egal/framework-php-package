<?php

namespace Egal\Interface;

use Egal\Interface\Metadata\Metadata as ComponentMetadata;

class Manager
{
    private array $components = [];

    public function component(ComponentMetadata $component): self
    {
        $this->components[$component->getLabel()] = $component;

        return $this;
    }

    public function getComponents(): array
    {
        return $this->components;
    }

    public function getComponentByLabel(string $label): ComponentMetadata
    {
        return array_key_exists($label, $this->components)
            ? $this->components[$label]
            : throw new \Exception('Not found component with this label!', 404);
    }
}
