<?php

namespace EgalFramework\Kerberos\Tests;

use EgalFramework\Kerberos\Common;
use EgalFramework\Kerberos\Exceptions\IncorrectDataException;
use EgalFramework\Kerberos\Mandate;
use EgalFramework\Kerberos\MandateData;
use PHPUnit\Framework\TestCase;

class MandateTest extends TestCase
{

    public function testToArray()
    {
        $mandate = new Mandate('sessionKey!1', new MandateData([], ['data1']), 10);
        $this->assertEquals([
            'sessionKey' => 'sessionKey!1',
            'data' => [Common::FIELD_USER => [], Common::FIELD_ROLES => ['data1']],
            'sessionTTL' => 10,
        ], $mandate->toArray());
    }

    public function testSettersGetters()
    {
        $mandate = new Mandate('sessionKey', new MandateData([], ['roles']), 100);

        $mandate->setSessionKey('sss');
        $this->assertEquals('sss', $mandate->getSessionKey());

        $mandate->setData(new MandateData([], ['roles']));
        $this->assertEquals(
            [Common::FIELD_USER => [], Common::FIELD_ROLES => ['roles']],
            $mandate->getData()->toArray());

        $mandate->setSessionTTL(10000);
        $this->assertEquals(10000, $mandate->getSessionTTL());
    }

}
