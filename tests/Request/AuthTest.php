<?php

namespace EgalFramework\Request\Tests;

use EgalFramework\Common\Registry;
use EgalFramework\Common\Session;
use EgalFramework\Kerberos\Common;
use EgalFramework\Kerberos\Crypt;
use EgalFramework\Request\Auth;
use EgalFramework\Request\Exceptions\RequestFailedException;
use EgalFramework\Request\Tests\Stubs\Message;
use EgalFramework\Request\Tests\Stubs\Queue;
use PHPUnit\Framework\TestCase;

class AuthTest extends TestCase
{

    protected function setUp(): void
    {
        parent::setUp();
        Session::setRegistry(new Registry);
    }

    /**
     * @throws RequestFailedException
     */
    public function testAuth()
    {
        $queue = new Queue;
        $message = new Message;
        $crypt = new Crypt;
        $message->setPrivateData([
            Common::FIELD_DATA => $crypt->encrypt(time(), 'pwd'),
        ]);
        $queue->setNewMessage($message);
        Session::setQueue($queue);

        $auth = new Auth('smName', 'pwd', 'qqq');
        $auth->run();
        $result = Session::getRegistry()->get('testSentResult');
        $this->assertEquals('qqq', $result['service']);
        $this->assertEquals('_model_Service', $result['queue']);
    }

    /**
     * @throws RequestFailedException
     */
    public function testTimeShiftMore()
    {
        $queue = new Queue;
        $message = new Message;
        $crypt = new Crypt;
        $message->setPrivateData([
            Common::FIELD_DATA => $crypt->encrypt(time() + 4 * 60, 'pwd'),
        ]);
        $queue->setNewMessage($message);
        Session::setQueue($queue);

        $auth = new Auth('smName', 'pwd', 'qqq');
        $auth->run();

        $message->setPrivateData([
            Common::FIELD_DATA => $crypt->encrypt(time() + 5 * 60 + 1, 'pwd'),
        ]);
        $queue->setNewMessage($message);
        Session::setQueue($queue);

        $this->expectExceptionMessage('Time shift, auth server is invalid');
        $this->expectExceptionCode(401);
        $auth->run();
    }

    /**
     * @throws RequestFailedException
     */
    public function testTimeShiftLess()
    {
        $queue = new Queue;
        $message = new Message;
        $crypt = new Crypt;
        $message->setPrivateData([
            Common::FIELD_DATA => $crypt->encrypt(time() - 4 * 60, 'pwd'),
        ]);
        $queue->setNewMessage($message);
        Session::setQueue($queue);

        $auth = new Auth('smName', 'pwd', 'qqq');
        $auth->run();

        $message->setPrivateData([
            Common::FIELD_DATA => $crypt->encrypt(time() - 5 * 60 - 1, 'pwd'),
        ]);
        $queue->setNewMessage($message);
        Session::setQueue($queue);

        $this->expectExceptionMessage('Time shift, auth server is invalid');
        $this->expectExceptionCode(401);
        $auth->run();
    }

    /**
     * @throws RequestFailedException
     */
    public function testIncorrectDataException()
    {
        $queue = new Queue;
        $message = new Message;
        $message->setPrivateData('[]');
        $queue->setNewMessage($message);
        Session::setQueue($queue);

        $auth = new Auth('smName', 'pwd', 'qqq');
        $this->expectExceptionMessage('Incorrect data received from auth service: \'[]\'');
        $this->expectExceptionCode(500);
        $auth->run();
    }

    /**
     * @throws RequestFailedException
     */
    public function testRemoteException()
    {
        $queue = new Queue;
        $message = new Message;
        $message->setPrivateData([
            'error' => [
                'message' => 'custom error',
                'code' => '666',
            ],
        ]);
        $queue->setNewMessage($message);
        Session::setQueue($queue);

        $auth = new Auth('smName', 'pwd', 'qqq');
        $this->expectExceptionMessage('custom error');
        $this->expectExceptionCode(666);
        $auth->run();
    }

    public function testGetToken()
    {
        $queue = new Queue;
        $message = new Message;
        $crypt = new Crypt;
        $message->setPrivateData([
            Common::FIELD_DATA => $crypt->encrypt(time(), 'pwd'),
        ]);
        $queue->setNewMessage($message);
        Session::setQueue($queue);

        $auth = new Auth('smName', 'pwd', 'qqq');
        $auth->run();
        $token = json_decode($auth->getSessionKey(), true);
        $this->assertNotEmpty($token[Common::FIELD_DATA]);
        $this->assertEquals('smName', $token[Common::FIELD_EMAIL]);
    }

}
