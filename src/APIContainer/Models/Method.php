<?php

namespace EgalFramework\APIContainer\Models;

use EgalFramework\Common\Interfaces\APIContainer\MethodInterface;

/**
 * Class Method
 * @package EgalFramework\APIContainer\Models
 */
class Method extends AbstractModel implements MethodInterface
{

    /** @var string */
    public string $name;

    /** @var string */
    public string  $summary;

    /** @var string */
    public string  $description;

    /** @var string */
    public string  $fromClass;

    /** @var Argument[] */
    public array $arguments;

    /** @var string */
    public string $return;

    /** @var string[] */
    public array $roles;

    /**
     * Method constructor.
     */
    public function __construct()
    {
        $this->name = $this->summary = $this->description = $this->fromClass = $this->return = '';
        $this->arguments = $this->roles = [];
        parent::__construct();
    }

    public function getRoles(): array
    {
        return $this->roles;
    }

}
