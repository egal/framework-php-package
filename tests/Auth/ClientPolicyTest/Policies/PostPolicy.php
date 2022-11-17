<?php

namespace Egal\Tests\Auth\ClientPolicyTest\Policies;

use Egal\Auth\Entities\Client;
use Egal\Tests\Auth\ClientPolicyTest\Models\Post;

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
