<?php

namespace Egal\Tests;

use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Database\Eloquent\Model as Eloquent;

trait DatabaseSchema
{

    abstract protected function createSchema(): void;
    abstract protected function dropSchema(): void;

    protected function setUp(): void
    {
        $db = new DB;

        $db->addConnection([
            'driver'   => getenv('DB_CONNECTION'),
            'database' => getenv('DB_NAME'),
            'host' => getenv('DB_HOST'),
            'port' => getenv('DB_PORT'),
            'username' => getenv('DB_USERNAME'),
            'password' => getenv('DB_PASSWORD'),
            'schema' => 'public',
            'charset' => 'utf8',
            'prefix' => '',
            'sslmode' => 'prefer',
        ]);

        $db->bootEloquent();
        $db->setAsGlobal();

        $this->createSchema();
    }

    protected function tearDown(): void
    {
        $this->dropSchema();
    }

    /**
     * Get a database connection instance.
     *
     * @return \Illuminate\Database\Connection
     */
    protected function connection()
    {
        return Eloquent::getConnectionResolver()->connection();
    }

    /**
     * Get a schema builder instance.
     *
     * @return \Illuminate\Database\Schema\Builder
     */
    protected function schema()
    {
        return $this->connection()->getSchemaBuilder();
    }

}
