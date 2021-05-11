<?php

namespace EgalFramework\Common\Tests;

use EgalFramework\Common\Settings;
use PHPUnit\Framework\TestCase;

class SettingsTest extends TestCase
{

    public function testSettersGetters()
    {
        Settings::setAppKey('appKey');
        $this->assertEquals('appKey', Settings::getAppKey());
        Settings::setAppName('appName');
        $this->assertEquals('appName', Settings::getAppName());
        Settings::setDebugMode(false);
        $this->assertEquals(false, Settings::getDebugMode());
        Settings::setDisableAuth(true);
        $this->assertEquals(true, Settings::getDisableAuth());
        Settings::setDisableCache(false);
        $this->assertEquals(false, Settings::getDisableCache());
    }

}
