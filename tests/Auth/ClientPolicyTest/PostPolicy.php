<?php

namespace Egal\Tests\Auth\ClientPolicyTest;

use Egal\Auth\Entities\Client;

class PostPolicy
{

    public static function foo(Client $client, Post $post): bool
    {
        return false;
    }

    public static function bar(Client $client, Post $post): bool
    {
        return true;
    }

}
