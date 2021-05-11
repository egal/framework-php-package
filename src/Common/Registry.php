<?php

namespace EgalFramework\Common;

class Registry
{

    /** @var mixed[] */
    private array $vars;

    public function __construct()
    {
        $this->vars = [];
    }

    /**
     * @param string $name
     * @param mixed $value
     */
    public function set(string $name, $value)
    {
        $this->vars[$name] = $value;
    }

    /**
     * @param string $name
     * @return mixed|null
     */
    public function get(string $name)
    {
        return isset($this->vars[$name])
            ? $this->vars[$name]
            : null;
    }

}
