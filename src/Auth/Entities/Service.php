<?php

declare(strict_types=1);

namespace Egal\Auth\Entities;

use Egal\Auth\Tokens\ServiceServiceToken;

class Service extends Client
{

    public readonly string $name;

    public function __construct(ServiceServiceToken $sst)
    {
        $this->name = $sst->getSub()['name'];
    }

    public function isService(string|null $name = null): bool
    {
        return parent::isService($name) && ($name === null || $this->name === $name);
    }

}
