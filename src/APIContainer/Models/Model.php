<?php

namespace EgalFramework\APIContainer\Models;

use EgalFramework\Common\Interfaces\APIContainer\MethodInterface;
use EgalFramework\Common\Interfaces\APIContainer\ModelInterface;

/**
 * Class Model
 * @package EgalFramework\APIContainer\Models
 */
class Model extends AbstractModel implements ModelInterface
{

    /** @var string */
    public string  $name;

    /** @var string */
    public string $summary;

    /** @var string */
    public string $description;

    /** @var MethodInterface[] */
    private array $methods;

    /** @var array */
    protected array $skipFields = ['methods'];

    /**
     * Model constructor.
     */
    public function __construct()
    {
        $this->name = $this->summary = $this->description = '';
        $this->methods = [];
        parent::__construct();
    }

    /**
     * @return MethodInterface[]
     */
    public function getMethods(): array
    {
        return $this->methods;
    }

    public function keySortMethods()
    {
        ksort($this->methods);
    }

    /**
     * @param string $name
     * @return MethodInterface|null
     */
    public function getMethod(string $name): ?MethodInterface
    {
        return isset($this->methods[$name])
            ? $this->methods[$name]
            : null;
    }

    public function setMethod(string $name, MethodInterface $method)
    {
        $this->methods[$name] = $method;
    }

    public function removeMethod(string $name)
    {
        unset($this->methods[$name]);
    }

}
