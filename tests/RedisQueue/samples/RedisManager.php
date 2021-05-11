<?php

namespace EgalFramework\RedisQueue\Tests\Samples;

use EgalFramework\Common\Queue\Message;
use Illuminate\Contracts\Redis\Factory;

class RedisManager implements Factory
{

    private Message $message;

    private array $data;

    public function connection($name = null)
    {
    }

    public function setMessage(Message $message)
    {
        $this->message = $message;
    }

    public function blpop(array $path)
    {
        if (strstr($path[0], 'testFalse')) {
            return null;
        }
        return [
            0 => '', 1 => json_encode($this->message->toArray())
        ];
    }

    public function rpush()
    {
        return null;
    }

    public function lpush($key, $value)
    {
        $this->data[$key] = $value;
    }

    public function lrange(string $key)
    {
        return $this->data[$key];
    }

    public function expire()
    {
    }

    public function lrem()
    {
    }

    public function del()
    {
    }

}
