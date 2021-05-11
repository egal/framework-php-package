<?php

namespace EgalFramework\APIContainer\Models;

/**
 * Class Argument
 * @package EgalFramework\APIContainer\Models
 */
class Argument extends AbstractModel
{

    /** @var string|null */
    public ?string $name;

    /** @var string */
    public string $type;

    /** @var string */
    public string $description;

    /** @var bool */
    public bool $isRequired;

    /**
     * Argument constructor.
     */
    public function __construct()
    {
        $this->name = $this->type = $this->description = '';
        $this->isRequired = false;
        parent::__construct();
    }

}
