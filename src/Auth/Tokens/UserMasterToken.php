<?php

declare(strict_types=1);

namespace Egal\Auth\Tokens;

class UserMasterToken extends Token
{

    protected string $typ = TokenType::USER_MASTER;

}
