<?php

declare(strict_types=1);

namespace Egal\Centrifugo;

use phpcent\Client as PHPCentClient;

class Centrifugo
{

    private PHPCentClient $client;

    public function __construct(array $config)
    {
        $requiredKeys = ['api_url', 'api_key', 'secret'];

        $configKeys = array_intersect(array_keys($config), $requiredKeys);

        if (sort($configKeys) !== sort($requiredKeys)) {
            throw CentrifugoInitException::make(...$requiredKeys);
        }

        $this->client = new PHPCentClient($config['api_url'], $config['api_key'], $config['secret']);
    }

    public static function getInstance(): self
    {
        return app(static::class);
    }

    public static function getClient(): PHPCentClient
    {
        return static::getInstance()->client;
    }

}
