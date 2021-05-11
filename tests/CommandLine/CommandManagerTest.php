<?php

namespace EgalFramework\CommandLine\Tests;

use EgalFramework\CommandLine\CommandManager;
use EgalFramework\Common\Session;

class CommandManagerTest extends CommandTestCase
{

    public function testRegister(): void
    {
        Session::getCommandManager()->register('\\App\\Commands\\TestCommand');
        $data = json_decode(file_get_contents(Session::getRegistry()->get('AppPath') . '/commands.json'), true);
        $this->assertEquals(['\\App\\Commands\\TestCommand'], $data);
    }

    public function testLoadAndGetItems(): void
    {
        Session::getCommandManager()->register('\\App\\Commands\\TestCommand');
        $commandManager = new CommandManager();
        $this->assertEquals(['\\App\\Commands\\TestCommand'], $commandManager->getScripts());
    }

    public function testClean(): void
    {
        Session::getCommandManager()->clean();
        Session::getCommandManager()->register('\\App\\Commands\\TestCommand');
        Session::getCommandManager()->clean();
        $this->assertEquals([], Session::getCommandManager()->getScripts());
        $data = json_decode(file_get_contents(Session::getRegistry()->get('AppPath') . '/commands.json'), true);
        $this->assertEmpty($data);
    }

}
