<?php

namespace Egal\Centrifugo;

use phpcent\Client as PHPCentClient;

/**
 * @mixin \phpcent\Client
 */
class Centrifugo
{

    private PHPCentClient $client;

    public function __construct(array $config)
    {
        $this->client = new PHPCentClient($config['api_url'], $config['api_key'], $config['secret']);
        # TODO: Валидация содержания конфига
        # TODO: Доконфигурирование из файла конфига полностью клиента
    }

    public static function __callStatic(string $name, array $arguments)
    {
        return static::getInstance()->client->{$name}(...$arguments);
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