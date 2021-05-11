<?php

namespace EgalFramework\CommandLine\Tests\Commands;

use EgalFramework\CommandLine\Commands\InitCommands;
use EgalFramework\CommandLine\Tests\CommandTestCase;
use EgalFramework\Common\Session;

class InitCommandsTest extends CommandTestCase
{

    public function testInitCommands()
    {
        /** @var InitCommands $command */
        $command = $this->getCommand(InitCommands::class);
        $command->handle();
        $data = json_decode(file_get_contents(Session::getRegistry()->get('AppPath') . '/commands.json'), true);
        sort($data);
        $this->assertEquals($this->getCommands(), $data);
    }

    private function getCommands(): array
    {
        $result = [];
        foreach (scandir(__DIR__ . '/../../src/Commands') as $file) {
            if (in_array($file, ['.', '..', 'InitCommands.php'])) {
                continue;
            }
            $result[] = '\\EgalFramework\\CommandLine\\Commands\\' . preg_replace('/\.php$/', '', $file);
        }
        sort($result);
        return $result;
    }

}
