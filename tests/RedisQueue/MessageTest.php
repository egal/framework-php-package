<?php

namespace EgalFramework\RedisQueue\Tests;

use EgalFramework\Common\Exceptions\MessageException;
use EgalFramework\Common\Queue\Message;
use PHPUnit\Framework\TestCase;

class MessageTest extends TestCase
{

    public function testMessageError()
    {
        $message = new Message();
        $this->expectException(MessageException::class);
        $message->fromJSON('{error!!!11');
    }

    public function testMessageSetters()
    {
        $message = new Message;

        $message->setId(123);
        $this->assertEquals(123, $message->getId());

        $message->setQuery([123, "qwe" => "sss"]);
        $this->assertEquals([123, "qwe" => "sss"], $message->getQuery());

        $message->setModel('Model');
        $this->assertEquals('Model', $message->getModel());

        $message->setUid('UID');
        $this->assertEquals('UID', $message->getUid());

        $message->setMethod(123);
        $this->assertEquals(123, $message->getMethod());

        $message->setAction('Action');
        $this->assertEquals('Action', $message->getAction());

        $message->setData(['SomeData']);
        $this->assertEquals(['SomeData'], $message->getData());

        $message->setProcessTime(123.321);
        $this->assertEquals(123.321, $message->getProcessTime());

        $message->setMandate('mandate');
        $this->assertEquals('mandate', $message->getMandate());
    }

}
