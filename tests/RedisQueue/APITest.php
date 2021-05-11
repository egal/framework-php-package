<?php

namespace EgalFramework\RedisQueue\Tests;

use EgalFramework\Common\Exceptions\MessageException;
use EgalFramework\Common\Queue\Message;
use EgalFramework\RedisQueue\API;
use EgalFramework\RedisQueue\DataCorruptionException;
use EgalFramework\RedisQueue\Tests\Samples\RedisManager;
use PHPUnit\Framework\TestCase;

class APITest extends TestCase
{

    private string $data;

    private Message $message;

    private API $api;

    protected function setUp(): void
    {
        parent::setUp();

        $this->message = new Message();
        $this->message->setId(123);
        $this->message->setAction('Action');
        $this->message->setData(['dataKey' => 'dataValue']);
        $this->message->setUid('UID');
        $data = $this->message->toArray();
        $data['hash'] = 'salt';
        ksort($data);
        $this->message->setHash(hash('SHA256', json_encode($data)));

        $redisManager = new RedisManager;
        $redisManager->setMessage($this->message);
        $this->api = $api = new API($redisManager, 'salt', 'q');
    }

    /**
     * @throws DataCorruptionException
     * @throws MessageException
     */
    public function testRead()
    {
        $this->api->send('test', 'queue', $this->message, 1000);
        $callback = function (string $data) {
            $this->data = $data;
        };
        $callback->bindTo($this);
        $this->api->read('test', 'queue', $callback);
        $result = $this->api->getMessage($this->data);
        $this->assertEquals($this->message->getId(), $result->getId());
        $this->assertEquals($this->message->getAction(), $result->getAction());
        $this->assertEquals($this->message->getData(), $result->getData());
        $this->assertEquals($this->message->getUid(), $result->getUid());
    }

    /**
     * @throws DataCorruptionException
     * @throws MessageException
     */
    public function testFault()
    {
        $this->message->setHash('wrong');
        $this->expectException(DataCorruptionException::class);
        $this->api->getMessage('{}');
    }

    public function testPool()
    {
        $this->api->createPool('poolz');
        $this->assertEquals(['poolz'], $this->api->getPools());
    }

    public function testReadFalse()
    {
        $this->assertFalse($this->api->read('testFalse', 'q', function () {
        }));
    }

    public function testDelete()
    {
        $this->api->deletePool('pool');
        $this->assertTrue(true);
    }

    public function testListen()
    {
        $this->api->quit();
        $this->api->listen('service', 'queue', function () {
        }, 1);
        $this->assertTrue(true);
    }

    public function testNewMessageInstance()
    {
        $message = $this->api->getNewMessageInstance();
        $this->assertInstanceOf(Message::class, $message);
    }

}
