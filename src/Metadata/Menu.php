<?php

namespace EgalFramework\Metadata;

/**
 * Class Menu
 * @package EgalFramework\Metadata
 */
class Menu
{

    /** @var Menu[] */
    private array $menu;

    private string $label;

    private string $route;

    private bool $isExternal;

    private string $internalName;

    private string $icon;

    public function __construct(string $label = '', string $route = '', $isExternal = false)
    {
        $this->menu = [];
        $this->label = $label;
        $this->route = $route;
        $this->isExternal = $isExternal;
    }

    public function add(string $label, string $route = '', bool $isExternal = false): Menu
    {
        return $this->menu[] = new Menu($label, $route, $isExternal);
    }

    public function setInternalName(string $internalName): self
    {
        $this->internalName = $internalName;
        return $this;
    }

    public function setIcon(string $icon): self
    {
        $this->icon = $icon;
        return $this;
    }

    public function build(): array
    {
        $result = [];
        if (!empty($this->label)) {
            $result['label'] = $this->label;
        }
        if (!empty($this->route)) {
            $result['route'] = $this->route;
        }
        if (!empty($this->isExternal)) {
            $result['isExternal'] = $this->isExternal;
        }
        if (isset($this->internalName) && !empty($this->internalName)) {
            $result['internalName'] = $this->internalName;
        }
        if (isset($this->icon) && !empty($this->icon)) {
            $result['icon'] = $this->icon;
        }
        $isTopMenu = empty($result);
        if (!empty($this->menu)) {
            $result['deep'] = [];
            foreach ($this->menu as $menu) {
                $result['deep'][] = $menu->build();
            }
        }
        return $isTopMenu
            ? (
            empty($result['deep'])
                ? []
                : $result['deep']
            )
            : $result;
    }

}
