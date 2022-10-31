<?php

declare(strict_types=1);

namespace Egal\Auth\Tokens;

class UserServiceToken extends ServiceToken
{

    protected string $typ = TokenType::USER_SERVICE;

}
