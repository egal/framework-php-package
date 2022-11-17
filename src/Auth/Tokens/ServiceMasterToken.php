<?php

declare(strict_types=1);

namespace Egal\Auth\Tokens;

class ServiceMasterToken extends Token
{

    protected string $typ = TokenType::SERVICE_MASTER;

}
