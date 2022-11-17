<?php

declare(strict_types=1);

namespace Egal\Auth\Tokens;

class ServiceServiceToken extends ServiceToken
{

    protected string $typ = TokenType::SERVICE_SERVICE;

}
