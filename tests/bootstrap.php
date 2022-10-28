<?php

require_once __DIR__ . '/../vendor/autoload.php';

(new Laravel\Lumen\Bootstrap\LoadEnvironmentVariables(
    dirname(__DIR__)
))->bootstrap();

Illuminate\Support\Carbon::setTestNow(Illuminate\Support\Carbon::now());
setlocale(LC_ALL, 'C.UTF-8');
date_default_timezone_set(env('APP_TIMEZONE', 'UTC'));

$app = new Egal\Core\Application(
    dirname(__DIR__)
);

$app->withFacades();
$app->withEloquent();

$app->singleton(Illuminate\Contracts\Debug\ExceptionHandler::class, Egal\Core\Exceptions\ExceptionHandler::class);
$app->singleton(Illuminate\Contracts\Console\Kernel::class, Laravel\Lumen\Console\Kernel::class);

$app->configure('app');

$app->register(Egal\Core\ServiceProvider::class);
$app->register(Egal\AuthServiceDependencies\ServiceProvider::class);
$app->register(Egal\Tests\ServiceProvider::class);

return $app;
