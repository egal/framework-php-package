<?php

namespace Egal\Core\Http;

use Illuminate\Contracts\Http\Kernel;
use Egal\Core\Http\ForceJsonMiddleware;


class ServiceProvider
{
    public function boot(Kernel $kernel)
    {
        $kernel->pushMiddleware(ForceJsonMiddleware::class);

        $this->loadRoutesFrom(__DIR__.'/../routes/non-existing-route.php');
    }
}
