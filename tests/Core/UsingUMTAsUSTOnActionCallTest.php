<?php

namespace Egal\Tests\Core;

use Egal\Auth\Exceptions\WrongTokenTypeException;
use Egal\Auth\Tokens\UserMasterToken;
use Egal\Core\Messages\ActionMessage;
use Egal\Core\Session\Session;
use Egal\Tests\TestCase;
use Illuminate\Support\Facades\Config;

class UsingUMTAsUSTOnActionCallTest extends TestCase
{

    function test(): void
    {
        Config::set('app.service_key', $serviceKey = 'self_service_key');

        Config::set('app.debug', true);

        $umt = new UserMasterToken();
        $umt->setSigningKey($serviceKey);
        $umt->setSub([]);

        $actionMessage = new ActionMessage('self', 'Model', 'ping', []);
        $actionMessage->setToken($umt->generateJWT());

        $this->expectException(WrongTokenTypeException::class);

        Session::setActionMessage($actionMessage);

        $this->assertNotEquals(0, $this->getExpectedExceptionCode());
    }

}
