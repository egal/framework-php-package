<?php

declare(strict_types=1);

namespace Egal\Core\Messages;

use Egal\Core\Bus\Bus;
use Illuminate\Support\Str;

abstract class Message
{

    protected string $uuid;

    protected string $type;

    abstract public static function fromArray(array $array): Message;

    public function __construct()
    {
        $this->uuid = Str::uuid()->toString();
    }

    public function toArray(): array
    {
        $result = [];

        foreach (get_object_vars($this) as $key => $value) {
            $result[Str::snake($key)] = $value;
        }

        return $result;
    }

    public function toJson(): string
    {
        return json_encode($this->toArray());
    }

    public function getUuid(): string
    {
        return $this->uuid;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function publish(): void
    {
        Bus::instance()->publishMessage($this);
    }

}
