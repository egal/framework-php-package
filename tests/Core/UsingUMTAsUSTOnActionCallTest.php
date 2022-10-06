<?php

namespace Egal\Tests\Core;

use Egal\Auth\Exceptions\WrongTokenTypeException;
use Egal\Auth\Tokens\UserMasterToken;
use Egal\Core\Messages\ActionMessage;
use Egal\Core\Session\Session;
use Illuminate\Support\Facades\Config;
use Laravel\Lumen\Application;
use PHPUnit\Framework\TestCase;

class UsingUMTAsUSTOnActionCallTest extends TestCase
{

    function test(): void
    {
        $app = new Application(dirname(__DIR__));
        $app->withFacades();

        Config::set('app.service_key', $serviceKey = 'self_service_key');

        Config::set('app.debug', true);

        $umt = new UserMasterToken();
        $umt->setSigningKey($serviceKey);

        $actionMessage = new ActionMessage('self', 'Model', 'ping', []);
        $actionMessage->setToken($umt->generateJWT());

        $this->expectException(WrongTokenTypeException::class);

        Session::setActionMessage($actionMessage);

        $this->assertNotEquals(0, $this->getExpectedExceptionCode());
    }

}
