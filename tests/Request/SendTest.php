<?php

namespace EgalFramework\Request\Tests;

use EgalFramework\Common\Registry;
use EgalFramework\Common\Session;
use EgalFramework\Kerberos\Common;
use EgalFramework\Kerberos\Crypt;
use EgalFramework\Request\Auth;
use EgalFramework\Request\Exceptions\RequestFailedException;
use EgalFramework\Request\Send;
use EgalFramework\Request\Tests\Stubs\Message;
use EgalFramework\Request\Tests\Stubs\Queue;
use Exception;
use PHPUnit\Framework\TestCase;

class SendTest extends TestCase
{

    protected function setUp(): void
    {
        parent::setUp();
        Session::setRegistry(new Registry);
    }

    /**
     * @throws RequestFailedException
     */
    public function testFailedToRead()
    {
        $queue = new Queue;
        $message = new Message;
        $crypt = new Crypt;
        $message->setPrivateData([
            Common::FIELD_DATA => $crypt->encrypt(time(), 'pwd'),
        ]);
        $queue->setNewMessage($message);
        $queue->setReadReturn(false);
        Session::setQueue($queue);

        $auth = new Auth('smName', 'pwd', 'qqq');
        $this->expectErrorMessage('Failed to get data from qqq request');
        $this->expectExceptionCode(401);
        $auth->run();
    }

    /**
     * @throws RequestFailedException
     * @throws Exception
     */
    public function testSend()
    {
        $queue = new Queue;
        $message = new Message;
        $crypt = new Crypt;
        $message->setPrivateData([
            Common::FIELD_DATA => $crypt->encrypt(time(), 'pwd'),
        ]);
        $queue->setNewMessage($message);
        Session::setQueue($queue);

        $send = new Send('app', 'pwd', 'tst');
        $send->send('service', $send->createMessage('Model', 'action'));
        $this->assertTrue(true);
    }

}
