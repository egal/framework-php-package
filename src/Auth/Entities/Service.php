<?php

declare(strict_types=1);

namespace Egal\Auth\Entities;

use Egal\Auth\Tokens\ServiceServiceToken;

class Service extends Client
{

    public readonly string $service;

    public function __construct(ServiceServiceToken $sst)
    {
        $this->service = $sst->getServiceName();
    }

}
