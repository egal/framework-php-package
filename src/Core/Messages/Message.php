<?php

namespace Egal\Core\Messages;

use Egal\Core\Bus\Bus;
use Illuminate\Support\Str;

abstract class Message
{

    protected string $uuid;
    protected string $type;

    abstract static function fromArray(array $array): Message;

    public function toArray(): array
    {
        $result = [];
        foreach (get_object_vars($this) as $key => $value) {
            $result[Str::snake($key)] = $value;
        }
        return $result;
    }

    public function __construct()
    {
        $this->uuid = Str::uuid()->toString();
    }

    protected function makeHash(): string
    {
        return hash('md5', json_encode($this->toArray()));
    }

    public function toJson(): string
    {
        return json_encode(array_merge($this->toArray(), ['hash' => $this->makeHash()]));
    }

    /**
     * @return string
     */
    public function getUuid(): string
    {
        return $this->uuid;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    public function publish(): void
    {
        Bus::getInstance()->publishMessage($this);
    }

}
