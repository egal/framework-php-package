<?php

namespace EgalFramework\CommandLine\Tests\Commands;

use EgalFramework\CommandLine\Commands\RegisterCommand;
use EgalFramework\CommandLine\Tests\CommandTestCase;
use EgalFramework\Common\Session;

class RegisterCommandTest extends CommandTestCase
{

    protected function setUp(): void
    {
        parent::setUp();
        mkdir(Session::getRegistry()->get('AppPath') . '/Console/Commands', 0750, true);
    }

    public function testRegisterCommand(): void
    {
        $commandName = 'TestCommand';

        /** @var RegisterCommand $command */
        $command = $this->getCommand(RegisterCommand::class);
        $this->input->setArgument('commandName', $commandName);
        $command->handle();
        $this->assertEquals(
            ['\\App\\Console\\Commands\\TestCommand'],
            json_decode(file_get_contents(Session::getRegistry()->get('AppPath') . 'commands.json'), true)
        );
    }

    public function testGetAllCommands(): void
    {
        copy(
            __DIR__ . '/../assets/Command.php',
            Session::getRegistry()->get('AppPath') . '/Console/Commands/TestCommand.php'
        );
        /** @var RegisterCommand $command */
        $command = $this->getCommand(RegisterCommand::class);
        $command->handle();
        $this->assertFileNotExists(Session::getRegistry()->get('AppPath') . '/commands.json');

        require_once Session::getRegistry()->get('AppPath') . '/Console/Commands/TestCommand.php';
        $command->handle();
        $this->assertEquals(
            ['\\App\\Console\\Commands\\TestCommand'],
            json_decode(file_get_contents(Session::getRegistry()->get('AppPath') . '/commands.json'), true)
        );
    }

}
