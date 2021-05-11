<?php

require 'vendor/autoload.php';

use Illuminate\Database\Capsule\Manager as Capsule;

$capsule = new Capsule;

$capsule->addConnection([
    'driver' => 'pgsql',
    'host' => 'db',
    'port' => 5432,
    'database' => 'postgres',
    'username' => 'postgres',
    'password' => 'dbpass',
    'charset' => 'utf8',
    'prefix' => '',
    'schema' => 'public',
]);

$capsule->setAsGlobal();
$capsule->bootEloquent();
