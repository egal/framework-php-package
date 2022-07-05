<?php

namespace Egal\Core\Http;

use Illuminate\Contracts\Http\Kernel;
use Egal\Core\Http\ForceJsonMiddleware;
use Illuminate\Support\ServiceProvider as IlluminateServiceProvider;

class ServiceProvider extends IlluminateServiceProvider
{
    public function boot(Kernel $kernel)
    {
        $kernel->pushMiddleware(ForceJsonMiddleware::class);

        $this->loadRoutesFrom(__DIR__.'/../routes/non-existing-route.php');
    }
}
