<?php

namespace EgalFramework\RedisQueue;

use Closure;
use EgalFramework\Common\Exceptions\MessageException;
use EgalFramework\Common\Interfaces\MessageInterface;
use EgalFramework\Common\Interfaces\QueueInterface;
use EgalFramework\Common\Queue\Message;
use Illuminate\Contracts\Redis\Factory;
use Redis;

class API implements QueueInterface
{

    /** @var Factory|Redis */
    private $redis;

    /** @var string */
    private string $hashSalt;

    /** @var string The part of the path depending on an app */
    private string $path;

    /** @var bool */
    private bool $quitFlag;

    /**
     * API constructor.
     * @param Factory $redis
     * @param string $hashSalt
     * @param string $path
     */
    public function __construct(Factory $redis, string $hashSalt, string $path = '')
    {
        $this->redis = $redis;
        $this->hashSalt = $hashSalt;
        $this->path = $path;
        $this->quitFlag = FALSE;
    }

    public function quit(): void
    {
        $this->quitFlag = TRUE;
    }

    /**
     * @param string $service
     * @param string $queue
     * @param Closure $callback
     * @param int $ttl
     */
    public function listen(string $service, string $queue, Closure $callback, $ttl = 1): void
    {
        while (TRUE) {
            $this->read($service, $queue, $callback, $ttl);
            if ($this->quitFlag) {
                break;
            }
        }
    }

    /**
     * @param string $service
     * @param string $queue
     * @param Closure $callback
     * @param int $ttl
     * @return bool
     */
    public function read(string $service, string $queue, Closure $callback, int $ttl = 10): bool
    {
        $data = $this->redis->blpop($this->getPath($service, $queue), $ttl);
        if (is_null($data)) {
            return false;
        }
        call_user_func($callback, $data[1]);
        return true;
    }

    /**
     * @param string $data
     * @return Message
     * @throws DataCorruptionException
     * @throws MessageException
     */
    public function getMessage(string $data): MessageInterface
    {
        $message = new Message();
        $message->fromJSON($data);
        if (hash('SHA256', $this->getLineToHash($message)) !== $message->getHash()) {
            throw new DataCorruptionException('Data corrupted');
        }
        return $message;
    }

    public function getNewMessageInstance(): MessageInterface
    {
        return new Message;
    }

    private function getLineToHash(MessageInterface $message): string
    {
        $data = $message->toArray();
        $data['hash'] = $this->hashSalt;
        ksort($data);
        return json_encode($data);
    }

    public function send(string $service, string $queue, MessageInterface $message, int $ttl = -1): void
    {
        $message->setHash(hash('SHA256', $this->getLineToHash($message)));
        $key = $this->getPath($service, $queue);
        $this->redis->rpush($key, json_encode($message->toArray(), JSON_UNESCAPED_UNICODE));
        if ($ttl != -1) {
            $this->redis->expire($key, $ttl);
        }
    }

    /**
     * Sets path to a queue
     *
     * This will set path to a queue. It can be used to put a request into a queue to another another microservice
     * Before using this method from Session's object, clone it or re-set it back, because it can cause unhandled bugs
     *
     * @param string $name
     */
    public function setPath(string $name): void
    {
        $this->path = $name;
    }

    private function getPath($service, $queue)
    {
        return 'queue:' . $service . ':' . $queue;
    }

    public function getPools(): array
    {
        return $this->redis->lrange('queue:' . $this->path . ':pools', 0, -1);
    }

    public function deletePool(string $poolName): void
    {
        $this->redis->lrem('queue:' . $this->path . ':pools', 0, $poolName);
        $this->redis->del('queue:' . $this->path . ':' . $poolName);
    }

    public function createPool(string $poolName): void
    {
        $this->redis->lpush('queue:' . $this->path . ':pools', $poolName);
    }

    /** @noinspection PhpParamsInspection */
    public function restartQueue(): void
    {
        $this->redis->client()->close();
        $this->redis->client()->connect(
            $this->redis->client()->getHost(),
            $this->redis->client()->getPort()
        );
    }

}
