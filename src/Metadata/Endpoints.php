<?php

namespace EgalFramework\Metadata;


use EgalFramework\Common\Interfaces\APIContainer\StorageInterface;

/**
 * Class Endpoints
 * @package EgalFramework\Metadata
 */
class Endpoints
{

    /** @var StorageInterface */
    public StorageInterface $storage;

    /** @var array */
    public array $endpoints;

    /**
     * Endpoints constructor.
     * @param StorageInterface $storage
     */
    public function __construct(StorageInterface $storage)
    {
        $this->storage = $storage;
        $this->endpoints = [];
    }

    /**
     * @param string $className
     * @param array $roles
     */
    public function addClass(string $className, array $roles)
    {
        $classDescriptionObj = $this->storage->getClass($className);
        if (!$classDescriptionObj) {
            return;
        }

        $this->endpoints[$className] = [];

        foreach ($classDescriptionObj->getMethods() as $method) {
            if (env('DISABLE_AUTH', false) || !empty(array_intersect($method->roles, $roles))) {
                $this->endpoints[$className][] = $method->name;
            }
        }
        if (empty($this->endpoints[$className])) {
            unset($this->endpoints[$className]);
        }
    }

}
